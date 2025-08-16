try:
    from openai import OpenAI  # type: ignore
    _OPENAI_IMPORTED = True
except Exception:  # ModuleNotFoundError or other import-time errors
    OpenAI = None  # type: ignore
    _OPENAI_IMPORTED = False
from pydantic import Field, BaseModel
from typing import List, Optional, Dict
from datetime import datetime, timedelta
import json
import os
import sys
import argparse
import unicodedata

# Optional project-specific helpers
try:
    from answer import get_ai_task_answer  # type: ignore
    _ANSWER_HELPERS = True
except Exception:
    _ANSWER_HELPERS = False
    def get_ai_task_answer(*args, **kwargs):  # type: ignore
        return None

try:
    from answer_format import AnswerFormat  # type: ignore
    _ANSWER_FORMAT = True
except Exception:
    _ANSWER_FORMAT = False
    class AnswerFormat(BaseModel):  # Minimal fallback on Pydantic BaseModel
        @classmethod
        def generate_prompt(cls) -> str:
            return f"Schema for {cls.__name__}: {cls.model_json_schema()}"

# --- Configuration API ---
# La clé est chargée depuis l'environnement, ne JAMAIS hardcoder ici.
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")  # pas de valeur par défaut

# --- Modèles de données pour les formats de réponse ---

class Exercise(AnswerFormat):
    name: str = Field(..., description="Nom de l'exercice")
    description: str = Field(..., description="Description de l'exercice")
    primary_muscles: List[str] = Field(..., description="Muscles principaux sollicités")
    secondary_muscles: List[str] = Field(..., description="Muscles secondaires sollicités")
    sets: int = Field(..., description="Nombre de séries")
    reps_per_set: List[int] = Field(..., description="Nombre de répétitions par série")


class WorkoutSession(AnswerFormat):
    title: str = Field(..., description="Titre de la séance")
    date: str = Field(..., description="Date de la séance (YYYY-MM-DD)")
    focus_area: str = Field(..., description="Zone musculaire principale ciblée")
    exercises: List[Exercise] = Field(..., description="Liste des exercices de la séance")
    notes: Optional[str] = Field(None, description="Notes ou conseils pour la séance")


class UserProfile(AnswerFormat):
    age: int = Field(..., description="Âge de l'utilisateur")
    weight_kg: float = Field(..., description="Poids en kg")
    height_cm: int = Field(..., description="Taille en cm")
    fitness_level: str = Field(..., description="Niveau fitness (débutant, intermédiaire, avancé)")
    goal: str = Field(..., description="Objectif (prise de masse, perte de poids, endurance, force)")
    available_time: int = Field(..., description="Temps disponible en minutes")
    equipment: List[str] = Field(..., description="Équipement disponible")


class PreviousSession(AnswerFormat):
    date: str = Field(..., description="Date de la séance")
    exercises: List[Exercise] = Field(..., description="Exercices réalisés")


# --- Base de données d'exercices ---

EXERCISE_DATABASE = {
    
}


# --- Nouveau format attendu par le frontend ---

class PlanItem(AnswerFormat):
    training_id: int = Field(..., description="Identifiant du training existant")
    name: str = Field(..., description="Nom du training")
    series: int = Field(..., description="Nombre de séries")
    repetitions: int = Field(..., description="Nombre de répétitions (0 si basé sur durée)")
    duree: int = Field(..., description="Durée en secondes (0 si basé sur répétitions)")
    categories: List[str] = Field(default_factory=list, description="Noms des muscles/catégories visés")


class PlanResponse(AnswerFormat):
    plan: List[PlanItem] = Field(..., description="Liste d'éléments pour pré-remplir le formulaire frontend")


# --- Helpers de comptage et d'augmentation du plan ---

# PPL grouping helpers
_PPL_GROUP_STEMS: Dict[str, List[str]] = {
    "push": [
        "pector", "epaul", "deltoid", "anterieur", "lateral", "triceps", "pompe", "dips", "militaire",
        "developpe", "pompe", "push"
    ],
    "pull": [
        "dos", "trap", "trapez", "rhombo", "rhomboid", "dorsal", "grand dorsal", "lat", "latissimus",
        "bicep", "poster", "arriere", "deltoid post", "deltoide post", "traction", "row", "tirage",
        "horizontal", "vertical", "pull"
    ],
    "legs": [
        "quadri", "ischio", "fess", "glute", "mollet", "jambe", "cuisse", "hamstring", "presse", "squat",
        "souleve", "souleve de terre", "fente", "leg"
    ],
    "abs": [
        "abdo", "oblique", "core", "gainage", "plank", "planche", "crunch", "sit", "sit-up", "mountain",
        "burpee", "v-up", "hollow", "russian", "twist", "cardio"
    ],
}


def _normalize_text(value: Optional[str]) -> str:
    if not value:
        return ""
    text = unicodedata.normalize("NFKD", str(value))
    text = "".join([c for c in text if not unicodedata.combining(c)])
    return text.lower().strip()


def _category_group_for_name(category_name: Optional[str]) -> Optional[str]:
    name = _normalize_text(category_name)
    if not name:
        return None
    for group, stems in _PPL_GROUP_STEMS.items():
        for stem in stems:
            if stem in name:
                return group
    return None


def _infer_last_session_type(recent_seances: List[Dict]) -> Optional[str]:
    if not recent_seances:
        return None
    # Try to pick the most recent by date if provided
    def _parse_date(d: Optional[str]) -> Optional[datetime]:
        try:
            return datetime.fromisoformat(str(d)) if d else None
        except Exception:
            return None
    sorted_sessions = sorted(
        list(recent_seances),
        key=lambda s: (_parse_date(s.get("date")) or datetime.min),
        reverse=True,
    )
    latest = sorted_sessions[0]
    counts = {"push": 0, "pull": 0, "legs": 0, "abs": 0}
    for tr in (latest.get("trainings") or []):
        for c in (tr.get("categories") or []):
            name = c.get("name") if isinstance(c, dict) else c
            grp = _category_group_for_name(name)
            if grp in counts:
                counts[grp] += 1
    # Decide by max; stable priority order
    best_group = None
    best_score = -1
    for grp in ["push", "pull", "legs", "abs"]:
        if counts[grp] > best_score:
            best_group = grp
            best_score = counts[grp]
    return best_group if best_score > 0 else None


def _rotation_sequence_for_level(fitness_level: Optional[str]) -> List[str]:
    lvl = _normalize_text(fitness_level)
    if "debut" in lvl or "début" in lvl:
        return ["push", "pull", "legs"]
    if "interm" in lvl:
        return ["push", "pull", "legs", "abs"]
    if "avanc" in lvl or "expert" in lvl:
        return ["push", "pull", "legs", "abs", "full_body"]
    return ["push", "pull", "legs"]


def _infer_next_session_type(recent_seances: List[Dict], user_profile: Dict) -> str:
    # No history → Full Body
    if not recent_seances:
        return "full_body"
    last_type = _infer_last_session_type(recent_seances)
    rotation = _rotation_sequence_for_level((user_profile or {}).get("fitness_level"))
    try:
        idx = rotation.index(last_type) if last_type in rotation else -1
    except Exception:
        idx = -1
    if idx >= 0:
        return rotation[(idx + 1) % len(rotation)]
    # Fallback
    return rotation[0] if rotation else "push"


def _target_count_from_level(level: Optional[str]) -> int:
    """Retourne un nombre cible d'exercices en fonction du niveau utilisateur."""
    if not level:
        return 8
    level_lc = (level or "").strip().lower()
    if "début" in level_lc or "debut" in level_lc:
        return 6
    if "interm" in level_lc:
        return 8
    if "avanc" in level_lc:
        return 10
    return 8


def _deduplicate_items_by_training_id(items: List[PlanItem]) -> List[PlanItem]:
    seen: set = set()
    unique: List[PlanItem] = []
    for it in items:
        if it.training_id in seen:
            continue
        seen.add(it.training_id)
        unique.append(it)
    return unique


def _augment_plan_to_minimum(
    items: List[PlanItem],
    minimal_trainings: List[Dict],
    recent_category_names: List[str],
    min_count: int,
    preferred_group: Optional[str] = None,
) -> List[PlanItem]:
    """Complète la liste jusqu'à min_count en privilégiant la variété des catégories et le groupe PPL cible si fourni."""
    if len(items) >= min_count:
        return items

    used_ids = {it.training_id for it in items}
    recent_set = set([_normalize_text(x) for x in (recent_category_names or [])])

    # Scoring simple: pénalise l'overlap avec catégories récentes, favorise groupe cible
    candidates: List[Dict] = [t for t in minimal_trainings if int(t.get("id")) not in used_ids]
    scored: List[tuple] = []
    for t in candidates:
        cats = list(t.get("categories") or [])
        cats_norm = set([_normalize_text(x) for x in cats])
        overlap_recent = len(cats_norm & recent_set)
        score = 100 - (overlap_recent * 20) - len(cats_norm)
        if preferred_group:
            if any(_category_group_for_name(c) == preferred_group for c in cats):
                score += 30
        scored.append((score, t))
    scored.sort(key=lambda x: x[0], reverse=True)

    augmented = list(items)
    for _, t in scored:
        if len(augmented) >= min_count:
            break
        augmented.append(PlanItem(
            training_id=int(t["id"]),
            name=t.get("name") or f"Training {t['id']}",
            series=3,
            repetitions=12,
            duree=0,
            categories=list(t.get("categories") or []),
        ))

    return augmented


def _ensure_group_quota(
    items: List[PlanItem],
    minimal_trainings: List[Dict],
    target_count: int,
    target_group: Optional[str],
) -> List[PlanItem]:
    """Assure qu'au moins 60% des items appartiennent au groupe cible en remplaçant si nécessaire."""
    if not target_group:
        return items
    required = max(1, int((target_count * 3 + 4) // 5))  # ceil(60%) without floats
    used_ids = {it.training_id for it in items}

    def item_group(it: PlanItem) -> Optional[str]:
        for c in it.categories or []:
            g = _category_group_for_name(c)
            if g:
                return g
        return None

    current_target_items = [it for it in items if item_group(it) == target_group]
    if len(current_target_items) >= required:
        return items

    # Candidates from trainings for target group
    candidates = []
    for t in minimal_trainings:
        tid = int(t.get("id"))
        if tid in used_ids:
            continue
        cats = t.get("categories") or []
        if any(_category_group_for_name(c) == target_group for c in cats):
            candidates.append(t)

    if not candidates:
        return items

    # Replace non-target items with target candidates
    new_items = list(items)
    i = 0
    for t in candidates:
        if len([it for it in new_items if item_group(it) == target_group]) >= required:
            break
        # find a non-target to replace
        while i < len(new_items) and item_group(new_items[i]) == target_group:
            i += 1
        if i >= len(new_items):
            break
        new_items[i] = PlanItem(
            training_id=int(t["id"]),
            name=t.get("name") or f"Training {t['id']}",
            series=3,
            repetitions=12,
            duree=0,
            categories=list(t.get("categories") or []),
        )
        i += 1
    return new_items


def _ensure_full_body_balance(
    items: List[PlanItem],
    minimal_trainings: List[Dict],
    target_count: int,
) -> List[PlanItem]:
    """Assure au moins une couverture Push, Pull, Legs si possible."""
    groups_needed = ["push", "pull", "legs"]
    present = {g: False for g in groups_needed}

    def item_group(it: PlanItem) -> Optional[str]:
        for c in it.categories or []:
            g = _category_group_for_name(c)
            if g:
                return g
        return None

    for it in items:
        g = item_group(it)
        if g in present:
            present[g] = True

    missing = [g for g, ok in present.items() if not ok]
    if not missing:
        return items

    used_ids = {it.training_id for it in items}
    # Try to fill by replacing from candidates for missing groups
    new_items = list(items)
    replace_idx = 0
    for mg in missing:
        # find candidate
        cand = None
        for t in minimal_trainings:
            tid = int(t.get("id"))
            if tid in used_ids:
                continue
            cats = t.get("categories") or []
            if any(_category_group_for_name(c) == mg for c in cats):
                cand = t
                used_ids.add(tid)
                break
        if not cand:
            continue
        # replace the earliest item that is not of a needed group
        while replace_idx < len(new_items) and item_group(new_items[replace_idx]) in groups_needed:
            replace_idx += 1
        if replace_idx >= len(new_items):
            break
        new_items[replace_idx] = PlanItem(
            training_id=int(cand["id"]),
            name=cand.get("name") or f"Training {cand['id']}",
            series=3,
            repetitions=12,
            duree=0,
            categories=list(cand.get("categories") or []),
        )
        replace_idx += 1
    return new_items


# --- Fonction principale de génération (ancienne) ---

def generate_workout_session(
    user_profile: UserProfile,
    previous_sessions: List[PreviousSession],
    target_date: str = None
) -> WorkoutSession:
    """
    Génère une séance de musculation personnalisée avec l'IA OpenAI.
    """
    # Préconditions explicites pour éviter des erreurs silencieuses
    if not _OPENAI_IMPORTED:
        raise RuntimeError("Le package Python 'openai' n'est pas installé (from openai import OpenAI a échoué).")
    if not OPENAI_API_KEY:
        raise RuntimeError("OPENAI_API_KEY manquante dans l'environnement du process Python.")

    # Initialisation du client OpenAI si disponible
    openai_client = OpenAI(api_key=OPENAI_API_KEY) if (_OPENAI_IMPORTED and OPENAI_API_KEY) else None

    # Préparation des données pour l'IA
    if target_date is None:
        target_date = datetime.now().strftime("%Y-%m-%d")

    # Analyse des séances précédentes
    muscle_frequency = {}
    for session in previous_sessions:
        for exercise in session.exercises:
            for muscle in exercise.primary_muscles:
                muscle_frequency[muscle.lower()] = muscle_frequency.get(muscle.lower(), 0) + 1

    # Création du prompt contextuel
    context = f"""
    Profil utilisateur:
    - Âge: {user_profile.age} ans
    - Poids: {user_profile.weight_kg} kg
    - Taille: {user_profile.height_cm} cm
    - Niveau: {user_profile.fitness_level}
    - Objectif: {user_profile.goal}
    - Équipement: {', '.join(user_profile.equipment)}

    Muscles les plus travaillés récemment:
    {json.dumps(muscle_frequency, indent=2, ensure_ascii=False)}

    Base d'exercices disponibles:
    {json.dumps(EXERCISE_DATABASE, indent=2, ensure_ascii=False)}

    Règles importantes:
    1. Éviter de retravailler les mêmes muscles 2 jours de suite
    2. Adapter la difficulté au niveau de l'utilisateur
    3. Respecter le temps disponible
    4. Varier les exercices pour éviter la routine
    5. Inclure des exercices de base et d'isolation
    6. Chaque exercice doit avoir TOUS les champs requis (name, description, primary_muscles, secondary_muscles, sets, reps_per_set, rest_time_seconds, difficulty)
    7. Les reps_per_set doivent être une liste de nombres (ex: [12, 10, 8])
    8. Les muscles doivent être des listes de strings (ex: ["pectoraux", "triceps"])
    9. Une séance doit contenir entre 6 et 10 exercices en fonction du niveau de l'utilisateur
    """

    # Génération de la séance avec l'IA
    if not openai_client:
        raise RuntimeError("OpenAI client indisponible ou clé API absente.")

    if _ANSWER_HELPERS:
        response = get_ai_task_answer(
            _client=openai_client,
            task=f"Génère une séance de musculation pour le {target_date} en tenant compte du profil utilisateur et de l'historique des séances. IMPORTANT: Chaque exercice doit être un objet complet avec tous les champs requis.",
            model="gpt-4o-mini",
            system_prompt=f"Tu es un coach sportif expert en musculation. {context}",
            answer_format=WorkoutSession,
            provider='openai'
        )
    else:
        # Appel direct OpenAI avec JSON strict
        from json import loads as json_loads
        chat = openai_client.chat.completions.create(
            model="gpt-4o-mini",
            response_format={"type": "json_object"},
            messages=[
                {"role": "system", "content": f"Tu es un coach sportif expert en musculation. {context}. Réponds uniquement en JSON valide conforme au schéma attendu."},
                {"role": "user", "content": (
                    "Produit un objet JSON unique avec les clés: title, date (YYYY-MM-DD), total_duration_minutes, difficulty, focus_area, notes (optionnel), exercises (liste). "
                    "Chaque exercise doit avoir: name, description, primary_muscles (string[]), secondary_muscles (string[]), sets (int), reps_per_set (int[]), rest_time_seconds (int), difficulty (string)."
                )},
            ],
        )
        content = chat.choices[0].message.content or "{}"
        try:
            data = json_loads(content)
        except Exception as e:
            raise RuntimeError(f"JSON invalide renvoyé par l'IA: {e}")
        try:
            response = WorkoutSession(**data)
        except Exception as e:
            raise RuntimeError(f"La réponse IA ne correspond pas au schéma WorkoutSession: {e}")

    # Vérification de la réponse
    if response is None or not hasattr(response, 'exercises'):
        raise RuntimeError("L'IA n'a pas renvoyé une séance valide.")

    return response


# --- Nouvelle API: génération d'un plan pour le frontend ---

def generate_ai_plan_for_frontend(
    user_profile: Dict,
    recent_seances: List[Dict],
    trainings: List[Dict],
    available_time: int = 60,
) -> PlanResponse:
    """Construit un plan conforme au frontend à partir des données fournies."""
    # Préconditions explicites
    if not _OPENAI_IMPORTED:
        raise RuntimeError("Le package Python 'openai' n'est pas installé (from openai import OpenAI a échoué).")
    if not OPENAI_API_KEY:
        raise RuntimeError("OPENAI_API_KEY manquante dans l'environnement du process Python.")

    # Initialisation client si possible
    openai_client = OpenAI(api_key=OPENAI_API_KEY) if (_OPENAI_IMPORTED and OPENAI_API_KEY) else None

    # Limiter les trainings à l'essentiel dans le prompt
    minimal_trainings = [
        {
            "id": int(t.get("id")),
            "name": t.get("name"),
            "categories": [c.get("name") for c in (t.get("categories") or []) if c.get("name")],
        }
        for t in trainings
        if t and t.get("id") is not None
    ]

    # Résumer les catégories récentes
    recent_categories = []
    for s in recent_seances or []:
        for tr in (s.get("trainings") or []):
            for c in (tr.get("categories") or []):
                name = c.get("name") if isinstance(c, dict) else c
                if name:
                    recent_categories.append(name)

    # Déterminer le type de séance PPL à viser (ou full body si pas d'historique)
    next_session_type = _infer_next_session_type(recent_seances or [], user_profile)

    # Identifier les trainings préférés pour le groupe cible
    preferred_training_ids = []
    preferred_group = next_session_type if next_session_type in ("push", "pull", "legs", "abs") else None
    if preferred_group:
        for t in minimal_trainings:
            cats = t.get("categories") or []
            if any(_category_group_for_name(c) == preferred_group for c in cats):
                preferred_training_ids.append(int(t["id"]))

    # Cible basée sur le niveau, bornée entre 6 et 10
    fitness_level = (user_profile or {}).get("fitness_level")
    target_count = _target_count_from_level(fitness_level)
    MIN_EX = 6
    MAX_EX = 10
    target_count = max(MIN_EX, min(MAX_EX, target_count))

    # Conserver aussi la contrainte de temps, mais ne pas réduire le nombre d'exercices sous 6
    # On gardera le nombre d'exercices demandé et on ajustera séries/répétitions/durée si besoin côté IA

    rules = (
        "\n    OBJECTIF: Sélectionner une liste d'exercices parmi les trainings fournis.\n"
        "    CONTRAINTES:\n"
        "    - Nombre d'exercices: entre 6 et 10; vise exactement " + str(target_count) + 
        " selon le niveau de l'utilisateur (" + str(fitness_level or "inconnu") + ").\n"
        "    - Respecter le temps disponible global: ajuster les répétitions/séries/durée plutôt que de réduire le nombre d'exercices sous 6.\n"
        "    - Éviter de réentraîner exactement les mêmes catégories que dans les 3 dernières séances consécutivement, sauf si nécessaire.\n"
        "    - Favoriser la variété des catégories au sein de la même séance.\n"
        "    - Retourner uniquement des trainings existants en se basant sur leur id.\n"
        "    - Pour chaque élément: renseigner series, repetitions OU duree (l'un des deux à 0).\n"
        "    - Format de sortie JSON strict avec la forme: { \"plan\": [ { \"training_id\": number, \"name\": string, \"series\": number, \"repetitions\": number, \"duree\": number, \"categories\": string[] } ] }\n"
        "    - Le champ categories est la liste des noms de catégories du training choisi.\n"
    )

    # Ajout des règles PPL
    if next_session_type in ("push", "pull", "legs", "abs"):
        rules += (
            "    - Séance du jour de type PPL cible: " + next_session_type.upper() + ". Prioriser les trainings dont les catégories appartiennent à ce groupe.\n"
            "    - Si possible, au moins 60% des items doivent provenir des trainings dont l'id est dans 'preferred_training_ids'.\n"
            "    - En cas d'insuffisance de trainings de ce groupe, compléter avec d'autres groupes en maximisant la variété.\n"
        )
    else:
        rules += (
            "    - Pas d'historique: produire une séance FULL BODY équilibrée couvrant les groupes push, pull et jambes.\n"
            "    - Répartir les catégories sur tout le corps (au moins 1-2 exercices par grand groupe si possible).\n"
        )

    context = {
        "user": user_profile or {},
        "available_time": available_time,
        "recent_categories": recent_categories,
        "recent_seances": recent_seances,
        "trainings": minimal_trainings,
        "rules": rules,
        "ppl": {
            "next_session_type": next_session_type,
            "preferred_training_ids": preferred_training_ids,
            "group_stems": _PPL_GROUP_STEMS,
        },
    }

    if not openai_client:
        raise RuntimeError("OpenAI client indisponible ou clé API absente.")

    if _ANSWER_HELPERS:
        ai_response = get_ai_task_answer(
            _client=openai_client,
            task=(
                "En te basant UNIQUEMENT sur la liste 'trainings' avec leurs 'id', "
                "génère un plan respectant les contraintes. Très important: chaque item du plan doit référencer 'training_id' présent dans trainings, "
                "remettre 'name' exactement tel que dans trainings, 'categories' comme liste de noms, et fixer repetitions XOR duree. "
                "Vise EXACTEMENT " + str(target_count) + " exercices si possible (mais toujours entre 6 et 10)."
            ),
            model="gpt-4o-mini",
            system_prompt="Tu es un coach sportif expert et un planificateur strictement structuré. Voici le contexte: " + json.dumps(context, ensure_ascii=False),
            answer_format=PlanResponse,
            provider='openai',
        )
        if ai_response and getattr(ai_response, 'plan', None):
            # Post-traitement pour respecter les bornes 6..10
            items = _deduplicate_items_by_training_id(list(ai_response.plan))
            if len(items) < MIN_EX:
                items = _augment_plan_to_minimum(items, minimal_trainings, recent_categories, MIN_EX, preferred_group=preferred_group)
            if len(items) > MAX_EX:
                items = items[:MAX_EX]
            items = _ensure_group_quota(items, minimal_trainings, target_count, preferred_group)
            items = _ensure_full_body_balance(items, minimal_trainings, target_count)
            return PlanResponse(plan=items)
    else:
        # Appel direct OpenAI avec sortie JSON stricte
        system_prompt = (
            "Tu es un coach sportif expert et un planificateur strictement structuré. "
            "Tu dois sélectionner des trainings UNIQUEMENT parmi 'trainings' (avec leurs 'id'). "
            "Voici le contexte JSON: " + json.dumps(context, ensure_ascii=False)
        )
        user_prompt = (
            "Retourne un objet JSON unique avec la forme exacte: {\n"
            "  \"plan\": [ { \"training_id\": number, \"name\": string, \"series\": number, \"repetitions\": number, \"duree\": number, \"categories\": string[] } ]\n"
            "}. Pour chaque item: 'training_id' DOIT correspondre à un id de 'trainings'; 'name' DOIT être exactement le name du training; 'categories' la liste des noms depuis le training; et 'repetitions' XOR 'duree' doit être 0. "
            "Le plan doit contenir ENTRE 6 ET 10 exercices et viser EXACTEMENT " + str(target_count) + "."
        )
        chat = openai_client.chat.completions.create(
            model="gpt-4o-mini",
            response_format={"type": "json_object"},
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_prompt},
            ],
        )
        content = chat.choices[0].message.content or "{}"
        try:
            data = json.loads(content)
        except Exception as e:
            raise RuntimeError(f"JSON invalide renvoyé par l'IA: {e}")
        try:
            parsed = PlanResponse(**data)
            items = _deduplicate_items_by_training_id(list(parsed.plan))
            if len(items) < MIN_EX:
                items = _augment_plan_to_minimum(items, minimal_trainings, recent_categories, MIN_EX, preferred_group=preferred_group)
            if len(items) > MAX_EX:
                items = items[:MAX_EX]
            items = _ensure_group_quota(items, minimal_trainings, target_count, preferred_group)
            items = _ensure_full_body_balance(items, minimal_trainings, target_count)
            return PlanResponse(plan=items)
        except Exception as e:
            raise RuntimeError(f"La réponse IA ne correspond pas au schéma PlanResponse: {e}")

    raise RuntimeError("L'IA n'a pas renvoyé un PlanResponse valide.")


def create_fallback_plan_for_frontend(
    trainings: List[Dict],
    recent_category_names: List[str],
    available_time: int,
) -> PlanResponse:
    max_exercises = max(1, min(6, available_time // 15))

    # Déduire le groupe cible probable à partir des catégories récentes (approximatif)
    counts = {"push": 0, "pull": 0, "legs": 0}
    for n in recent_category_names or []:
        grp = _category_group_for_name(n)
        if grp:
            counts[grp] += 1
    # Aperçu du prochain type attendu avec la même logique que ci-dessus
    last_type = None
    if any(counts.values()):
        last_type = max(counts.items(), key=lambda kv: kv[1])[0]
    preferred_group = None
    if last_type == "push":
        preferred_group = "pull"
    elif last_type == "pull":
        preferred_group = "legs"
    elif last_type == "legs":
        preferred_group = "push"

    # Score simple: pénalise l'overlap avec catégories récentes
    scored = []
    recent_set = set([_normalize_text(x) for x in (recent_category_names or [])])
    for t in trainings:
        cats = t.get("categories") or []
        cats_norm = set([_normalize_text(x) for x in cats])
        overlap = len(cats_norm & recent_set)
        score = 100 - (overlap * 20)
        if preferred_group and any(_category_group_for_name(c) == preferred_group for c in cats):
            score += 30
        scored.append((score, t))
    scored.sort(key=lambda x: x[0], reverse=True)

    # Si pas d'historique: construire une FULL BODY en round-robin PPL
    selected: List[Dict] = []
    if not recent_category_names:
        group_buckets: Dict[str, List[Dict]] = {"push": [], "pull": [], "legs": []}
        for _, t in scored:
            cats = t.get("categories") or []
            # Bucket a training if any of its categories maps to the group
            bucketed = False
            for g in ["push", "pull", "legs"]:
                if any(_category_group_for_name(c) == g for c in cats):
                    group_buckets[g].append(t)
                    bucketed = True
                    break
            if not bucketed:
                # ignore unclassified for the first pass
                pass
        # Round-robin pick
        order = ["push", "pull", "legs"]
        idx = 0
        used_ids = set()
        while len(selected) < max_exercises and any(group_buckets[g] for g in order):
            g = order[idx % len(order)]
            idx += 1
            if not group_buckets[g]:
                continue
            cand = group_buckets[g].pop(0)
            tid = int(cand.get("id"))
            if tid in used_ids:
                continue
            selected.append(cand)
            used_ids.add(tid)
        # Si pas assez rempli, compléter avec le reste des scorés
        if len(selected) < max_exercises:
            for _, t in scored:
                tid = int(t.get("id"))
                if tid in used_ids:
                    continue
                selected.append(t)
                used_ids.add(tid)
                if len(selected) >= max_exercises:
                    break
    else:
        # Historique présent: sélection basée sur scoring et variété simple
        used_categories: set = set()
        for _, t in scored:
            if len(selected) >= max_exercises:
                break
            cats = set(t.get("categories") or [])
            # Éviter de répéter trop tôt les mêmes catégories si on a du choix
            if used_categories & cats and len(selected) < 2:
                continue
            selected.append(t)
            used_categories |= cats

    if not selected:
        selected = [t for _, t in scored[: min(3, len(scored))]]

    items = []
    for t in selected:
        items.append(PlanItem(
            training_id=int(t["id"]),
            name=t.get("name") or f"Training {t['id']}",
            series=3,
            repetitions=12,
            duree=0,
            categories=list(t.get("categories") or []),
        ))

    return PlanResponse(plan=items)


# --- Fonctions utilitaires ---

def create_user_profile() -> UserProfile:
    """Crée un profil utilisateur exemple."""
    return UserProfile(
        age=25,
        weight_kg=70.0,
        height_cm=175,
        fitness_level="intermédiaire",
        goal="prise de masse",
        available_time=60,
        equipment=["haltères", "barre", "banc", "poids libres"]
    )


def create_previous_sessions() -> List[PreviousSession]:
    """Crée des séances précédentes exemple."""
    return [
        PreviousSession(
            date="2024-01-15",
            exercises=[
                Exercise(
                    name="Développé couché",
                    description="Exercice de base pour les pectoraux",
                    primary_muscles=["pectoraux"],
                    secondary_muscles=["triceps", "épaules"],
                    sets=4,
                    reps_per_set=[12, 10, 8, 6],
                    weight_kg=60.0,
                    rest_time_seconds=120,
                    difficulty="intermédiaire"
                ),
                Exercise(
                    name="Tractions",
                    description="Exercice de base pour le dos",
                    primary_muscles=["dos"],
                    secondary_muscles=["biceps"],
                    sets=3,
                    reps_per_set=[8, 6, 4],
                    weight_kg=0.0,
                    rest_time_seconds=180,
                    difficulty="intermédiaire"
                )
            ],
            notes="Séance intense, bonnes sensations"
        ),
        PreviousSession(
            date="2024-01-17",
            exercises=[
                Exercise(
                    name="Squat",
                    description="Exercice de base pour les jambes",
                    primary_muscles=["quadriceps"],
                    secondary_muscles=["fessiers", "ischio-jambiers"],
                    sets=4,
                    reps_per_set=[10, 8, 6, 6],
                    weight_kg=80.0,
                    rest_time_seconds=180,
                    difficulty="intermédiaire"
                )
            ],
            notes="Séance jambes, progression sur les charges"
        )
    ]


def create_fallback_workout(
    user_profile: UserProfile,
    previous_sessions: List[PreviousSession],
    target_date: str
) -> WorkoutSession:
    """Crée une séance par défaut quand l'IA échoue."""

    # Analyse des muscles travaillés récemment
    muscle_frequency = {}
    for session in previous_sessions:
        for exercise in session.exercises:
            for muscle in exercise.primary_muscles:
                muscle_frequency[muscle.lower()] = muscle_frequency.get(muscle.lower(), 0) + 1

    # Déterminer les muscles à éviter (trop récemment travaillés)
    muscles_to_avoid = [muscle for muscle, count in muscle_frequency.items() if count >= 2]

    # Sélectionner les muscles à travailler
    available_muscles = ["jambes", "épaules", "abdominaux"]
    if "pectoraux" not in muscles_to_avoid:
        available_muscles.append("pectoraux")
    if "dos" not in muscles_to_avoid:
        available_muscles.append("dos")
    if "biceps" not in muscles_to_avoid:
        available_muscles.append("biceps")
    if "triceps" not in muscles_to_avoid:
        available_muscles.append("triceps")

    # Sélectionner 2-3 groupes musculaires
    selected_muscles = available_muscles[:3]

    # Créer les exercices
    exercises = []
    for muscle in selected_muscles:
        if muscle in EXERCISE_DATABASE:
            exercise_data = EXERCISE_DATABASE[muscle][0]  # Premier exercice du groupe

            # Adapter selon le niveau
            if user_profile.fitness_level == "débutant":
                sets = 3
                reps = [12, 10, 8]
                rest_time = 90
            elif user_profile.fitness_level == "avancé":
                sets = 4
                reps = [8, 6, 6, 4]
                rest_time = 120
            else:  # intermédiaire
                sets = 3
                reps = [10, 8, 6]
                rest_time = 100

            exercise = Exercise(
                name=exercise_data["name"],
                description=exercise_data["description"],
                primary_muscles=[muscle],
                secondary_muscles=[],
                sets=sets,
                reps_per_set=reps,
                weight_kg=None,
                rest_time_seconds=rest_time,
                difficulty=exercise_data["difficulty"]
            )
            exercises.append(exercise)

    # Créer la séance
    return WorkoutSession(
        title=f"Séance {', '.join(selected_muscles).title()}",
        date=target_date,
        total_duration_minutes=user_profile.available_time,
        difficulty=user_profile.fitness_level,
        focus_area=", ".join(selected_muscles),
        exercises=exercises,
        notes="Séance générée automatiquement (mode fallback)"
    )


# --- Tests et exemples ---

def test_workout_generation():
    """Test de génération de séance."""
    print("=== Test de génération de séance de musculation ===\n")

    # Création des données de test
    user = create_user_profile()
    previous_sessions = create_previous_sessions()

    print("Profil utilisateur:")
    print(f"- Âge: {user.age} ans")
    print(f"- Objectif: {user.goal}")
    print(f"- Niveau: {user.fitness_level}")
    print(f"- Temps disponible: {user.available_time} minutes\n")

    print("Séances précédentes:")
    for session in previous_sessions:
        print(f"- {session.date}: {len(session.exercises)} exercices")
        for ex in session.exercises:
            print(f"  • {ex.name} ({', '.join(ex.primary_muscles)})")
    print()

    # Génération de la nouvelle séance
    try:
        new_session = generate_workout_session(user, previous_sessions)

        if new_session is None:
            print("❌ Échec de la génération de séance")
            return

        print("🎯 Nouvelle séance générée:")
        print(f"Titre: {new_session.title}")
        print(f"Date: {new_session.date}")
        print(f"Durée: {new_session.total_duration_minutes} minutes")
        print(f"Difficulté: {new_session.difficulty}")
        print(f"Focus: {new_session.focus_area}")

        if new_session.notes:
            print(f"Notes: {new_session.notes}")

        print(f"\nExercices ({len(new_session.exercises)}):")
        for i, exercise in enumerate(new_session.exercises, 1):
            print(f"\n{i}. {exercise.name}")
            print(f"   Description: {exercise.description}")
            print(f"   Muscles: {', '.join(exercise.primary_muscles)}")
            print(f"   Séries: {exercise.sets} x {exercise.reps_per_set}")
            if exercise.weight_kg:
                print(f"   Poids: {exercise.weight_kg} kg")
            print(f"   Repos: {exercise.rest_time_seconds} secondes")
            print(f"   Difficulté: {exercise.difficulty}")

    except Exception as e:
        print(f"❌ Erreur lors de la génération: {e}")


def test_prompt_generation():
    """Test de génération des prompts."""
    print("=== Test de génération des prompts ===\n")

    print("Prompt pour Exercise:")
    print(Exercise.generate_prompt())

    print("\n" + "="*50 + "\n")

    print("Prompt pour WorkoutSession:")
    print(WorkoutSession.generate_prompt())


# --- Entrée CLI pour intégration PHP ---

def _run_cli_generate_plan():
    """Lit un payload JSON sur stdin et retourne un plan JSON sur stdout."""
    try:
        payload = json.load(sys.stdin)
        user = payload.get("user") or {}
        recent = payload.get("recent_seances") or []
        trainings = payload.get("trainings") or []
        available_time = int(payload.get("available_time") or 60)

        plan_resp = generate_ai_plan_for_frontend(user, recent, trainings, available_time)

        # Convertir en dict simple
        out = {
            "plan": [
                {
                    "training_id": int(item.training_id),
                    "name": item.name,
                    "series": int(item.series),
                    "repetitions": int(item.repetitions),
                    "duree": int(item.duree),
                    "categories": list(item.categories or []),
                }
                for item in plan_resp.plan
            ]
        }
        sys.stdout.write(json.dumps(out, ensure_ascii=False))
        sys.stdout.flush()
        sys.exit(0)
    except Exception as e:
        # Retourner une erreur structurée et un code de sortie non nul
        err = {"error": str(e)}
        try:
            sys.stdout.write(json.dumps(err, ensure_ascii=False))
            sys.stdout.flush()
        except Exception:
            sys.stderr.write(str(e))
            sys.stderr.flush()
        sys.exit(1)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Génération IA de plan de séance")
    parser.add_argument("--plan", action="store_true", help="Lire JSON sur stdin et retourner un plan JSON")
    args = parser.parse_args()

    if args.plan:
        _run_cli_generate_plan()
    else:
        # Tests de génération de prompts
        test_prompt_generation()

        print("\n" + "="*50 + "\n")

        # Test de génération de séance
        test_workout_generation() 




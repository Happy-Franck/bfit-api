#!/usr/bin/env python3
"""
Exemple complet d'utilisation du générateur de séances de musculation avec IA
"""

from workout_generator import (
    generate_workout_session,
    generate_ai_plan_for_frontend,
    UserProfile, PreviousSession, Exercise,
    create_user_profile, create_previous_sessions
)
from datetime import datetime, timedelta
import json
import sys
import os


def demo_workout_generation():
    """Démonstration complète du générateur de séances."""
    
    print("🏋️ GÉNÉRATEUR DE SÉANCES DE MUSCULATION AVEC IA")
    print("=" * 60)
    
    # 1. Création d'un profil utilisateur personnalisé
    print("\n1️⃣ CRÉATION DU PROFIL UTILISATEUR")
    print("-" * 40)
    
    user = UserProfile(
        age=28,
        weight_kg=75.0,
        height_cm=180,
        fitness_level="intermédiaire",
        goal="force et hypertrophie",
        available_time=75,
        equipment=["haltères", "barre", "banc", "rack squat", "corde à sauter"]
    )
    
    print(f"👤 Profil créé:")
    print(f"   • Âge: {user.age} ans")
    print(f"   • Poids: {user.weight_kg} kg")
    print(f"   • Taille: {user.height_cm} cm")
    print(f"   • Niveau: {user.fitness_level}")
    print(f"   • Objectif: {user.goal}")
    print(f"   • Temps disponible: {user.available_time} minutes")
    print(f"   • Équipement: {', '.join(user.equipment)}")
    
    # 2. Création d'un historique de séances
    print("\n2️⃣ HISTORIQUE DES SÉANCES")
    print("-" * 40)
    
    # Séance 1 - Pectoraux/Triceps
    session1 = PreviousSession(
        date=(datetime.now() - timedelta(days=3)).strftime("%Y-%m-%d"),
        exercises=[
            Exercise(
                name="Développé couché",
                description="Exercice de base pour les pectoraux",
                primary_muscles=["pectoraux"],
                secondary_muscles=["triceps", "épaules"],
                sets=4,
                reps_per_set=[8, 6, 6, 4],
                weight_kg=80.0,
                rest_time_seconds=180,
                difficulty="intermédiaire"
            ),
            Exercise(
                name="Développé incliné haltères",
                description="Cible les pectoraux supérieurs",
                primary_muscles=["pectoraux"],
                secondary_muscles=["triceps", "épaules"],
                sets=3,
                reps_per_set=[10, 8, 6],
                weight_kg=30.0,
                rest_time_seconds=120,
                difficulty="intermédiaire"
            ),
            Exercise(
                name="Extensions triceps",
                description="Isolation des triceps",
                primary_muscles=["triceps"],
                secondary_muscles=[],
                sets=3,
                reps_per_set=[12, 10, 8],
                weight_kg=15.0,
                rest_time_seconds=90,
                difficulty="débutant"
            )
        ],
        notes="Séance intense, progression sur le développé couché"
    )
    
    # Séance 2 - Dos/Biceps
    session2 = PreviousSession(
        date=(datetime.now() - timedelta(days=1)).strftime("%Y-%m-%d"),
        exercises=[
            Exercise(
                name="Tractions",
                description="Exercice de base pour le dos",
                primary_muscles=["dos"],
                secondary_muscles=["biceps"],
                sets=4,
                reps_per_set=[6, 5, 4, 3],
                weight_kg=0.0,
                rest_time_seconds=180,
                difficulty="intermédiaire"
            ),
            Exercise(
                name="Rowing haltère",
                description="Renforcement du dos",
                primary_muscles=["dos"],
                secondary_muscles=["biceps"],
                sets=3,
                reps_per_set=[10, 8, 6],
                weight_kg=25.0,
                rest_time_seconds=120,
                difficulty="débutant"
            ),
            Exercise(
                name="Curl haltères",
                description="Isolation des biceps",
                primary_muscles=["biceps"],
                secondary_muscles=[],
                sets=3,
                reps_per_set=[12, 10, 8],
                weight_kg=12.0,
                rest_time_seconds=90,
                difficulty="débutant"
            )
        ],
        notes="Bonnes sensations sur les tractions"
    )
    
    previous_sessions = [session1, session2]
    
    print("📅 Séances précédentes:")
    for i, session in enumerate(previous_sessions, 1):
        print(f"   {i}. {session.date} - {len(session.exercises)} exercices")
        for ex in session.exercises:
            print(f"      • {ex.name} ({', '.join(ex.primary_muscles)})")
    
    # 3. Génération de la nouvelle séance
    print("\n3️⃣ GÉNÉRATION DE LA NOUVELLE SÉANCE")
    print("-" * 40)
    
    try:
        new_session = generate_workout_session(user, previous_sessions)
        
        if new_session is None:
            print("❌ Échec de la génération de séance")
            return
        
        # 4. Affichage de la séance générée
        print("\n4️⃣ SÉANCE GÉNÉRÉE")
        print("-" * 40)
        
        # Vérification que la séance est valide
        if not hasattr(new_session, 'title') or not hasattr(new_session, 'exercises'):
            print("❌ La séance générée n'est pas valide")
            return
        
        print(f"🎯 {new_session.title}")
        print(f"📅 Date: {new_session.date}")
        print(f"⏱️  Durée: {new_session.total_duration_minutes} minutes")
        print(f"💪 Difficulté: {new_session.difficulty}")
        print(f"🎯 Focus: {new_session.focus_area}")
        
        if new_session.notes:
            print(f"📝 Notes: {new_session.notes}")
        
        print(f"\n🏋️ EXERCICES ({len(new_session.exercises)}):")
        print("-" * 40)
        
        total_time = 0
        for i, exercise in enumerate(new_session.exercises, 1):
            # Vérification que l'exercice est valide
            if not hasattr(exercise, 'name'):
                print(f"⚠️ Exercice {i}: données invalides")
                continue
                
            print(f"\n{i}. {exercise.name}")
            print(f"   📖 {exercise.description}")
            print(f"   🎯 Muscles principaux: {', '.join(exercise.primary_muscles)}")
            if exercise.secondary_muscles:
                print(f"   🔗 Muscles secondaires: {', '.join(exercise.secondary_muscles)}")
            print(f"   🔄 Séries: {exercise.sets} x {exercise.reps_per_set}")
            if exercise.weight_kg:
                print(f"   ⚖️  Poids: {exercise.weight_kg} kg")
            print(f"   ⏸️  Repos: {exercise.rest_time_seconds} secondes")
            print(f"   📊 Difficulté: {exercise.difficulty}")
            
            # Calcul du temps estimé
            exercise_time = (exercise.sets * 60) + (exercise.rest_time_seconds * (exercise.sets - 1))
            total_time += exercise_time
            print(f"   ⏱️  Temps estimé: {exercise_time // 60} min {exercise_time % 60} sec")
        
        print(f"\n⏱️  Temps total estimé: {total_time // 60} minutes {total_time % 60} secondes")
        
        # 5. Analyse de la séance
        print("\n5️⃣ ANALYSE DE LA SÉANCE")
        print("-" * 40)
        
        # Analyse des muscles travaillés
        muscles_worked = {}
        for exercise in new_session.exercises:
            if hasattr(exercise, 'primary_muscles'):
                for muscle in exercise.primary_muscles:
                    muscles_worked[muscle] = muscles_worked.get(muscle, 0) + 1
        
        print("🎯 Muscles ciblés dans cette séance:")
        for muscle, count in muscles_worked.items():
            print(f"   • {muscle}: {count} exercice(s)")
        
        # Vérification de la variété
        print(f"\n📊 Statistiques:")
        print(f"   • Nombre d'exercices: {len(new_session.exercises)}")
        print(f"   • Muscles différents: {len(muscles_worked)}")
        print(f"   • Temps respecté: {'✅' if total_time <= user.available_time * 60 else '❌'}")
        
    except Exception as e:
        print(f"❌ Erreur lors de la génération: {e}")


def demo_multiple_sessions():
    """Démonstration de génération de plusieurs séances."""
    
    print("\n" + "=" * 60)
    print("🔄 GÉNÉRATION DE PLUSIEURS SÉANCES")
    print("=" * 60)
    
    user = create_user_profile()
    
    # Simuler un historique de 2 semaines
    base_date = datetime.now() - timedelta(days=14)
    previous_sessions = []
    
    # Créer des séances passées
    for i in range(6):
        session_date = base_date + timedelta(days=i*2)
        session = PreviousSession(
            date=session_date.strftime("%Y-%m-%d"),
            exercises=[
                Exercise(
                    name=f"Exercice {i+1}",
                    description="Exercice exemple",
                    primary_muscles=["muscle_exemple"],
                    secondary_muscles=[],
                    sets=3,
                    reps_per_set=[10, 8, 6],
                    weight_kg=20.0,
                    rest_time_seconds=90,
                    difficulty="intermédiaire"
                )
            ],
            notes=f"Séance du {session_date.strftime('%d/%m/%Y')}"
        )
        previous_sessions.append(session)
    
    # Générer 3 séances futures
    for i in range(3):
        target_date = (datetime.now() + timedelta(days=i*2)).strftime("%Y-%m-%d")
        print(f"\n🎯 Génération séance pour le {target_date}")
        print("-" * 40)
        
        try:
            session = generate_workout_session(user, previous_sessions, target_date)
            if session:
                print(f"✅ Séance générée: {session.title}")
                print(f"   Focus: {session.focus_area}")
                print(f"   Exercices: {len(session.exercises)}")
            else:
                print("❌ Échec de génération")
        except Exception as e:
            print(f"❌ Erreur: {e}")


def demo_frontend_plan_generation():
    """Démonstration de génération du plan attendu par le frontend (Index.vue).

    Si un JSON est fourni sur stdin (ex: par le controller PHP), on l'utilise pour
    `user`, `recent_seances`, `trainings` et `available_time`. Sinon, on renvoie
    une erreur structurée sans recourir à des valeurs par défaut statiques.
    """
    # Valeurs utilisées: requis depuis stdin par défaut
    user = None
    recent_seances = None
    trainings = None
    available_time = 60

    try:
        # Tentative de lecture d'un payload JSON envoyé par le controller (stdin)
        if not sys.stdin.isatty():
            raw = sys.stdin.read().strip()
            if raw:
                payload = json.loads(raw)
                if isinstance(payload, dict):
                    user = payload.get("user")
                    recent_seances = payload.get("recent_seances")
                    trainings = payload.get("trainings")
                    if payload.get("available_time") is not None:
                        available_time = int(payload.get("available_time"))

        # Sans payload fourni, retourner une erreur JSON
        if user is None or trainings is None:
            sys.stdout.write(json.dumps({
                "error": "Aucun payload JSON fourni sur stdin. Fournir un JSON avec `user`, `recent_seances`, `trainings` et optionnellement `available_time`."
            }, ensure_ascii=False))
            sys.stdout.flush()
            sys.exit(1)

        plan = generate_ai_plan_for_frontend(user, recent_seances, trainings, available_time=available_time)
        sys.stdout.write(json.dumps({"plan": [getattr(p, 'model_dump', getattr(p, 'dict'))() for p in plan.plan]}, ensure_ascii=False))
        sys.stdout.flush()
        sys.exit(0)
    except Exception as e:
        # Retourner l'erreur structurée
        sys.stdout.write(json.dumps({"error": str(e)}, ensure_ascii=False))
        sys.stdout.flush()
        sys.exit(1)


if __name__ == "__main__":
    # Exécuter uniquement la démo basée sur stdin pour s'aligner avec l'intégration PHP
    demo_frontend_plan_generation() 
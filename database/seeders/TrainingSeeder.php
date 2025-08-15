<?php

namespace Database\Seeders;

use App\Models\Training;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TrainingSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Charger le JSON complet
		$jsonPath = database_path('seeders/data/musclewiki_exercises.json');
		if (!File::exists($jsonPath)) {
			return;
		}
		$raw = File::get($jsonPath);
		$decoded = json_decode($raw, true);
		if ($decoded === null) {
			return;
		}

		// Mapping des muscles du dataset -> catégories existantes (FR)
		$targetMappings = [
			'Pectoraux' => ['Chest', 'Upper Chest', 'Mid and Lower Chest', 'Pectorals', 'Pectoralis'],
			'Abdominaux' => ['Abdominals', 'Abs', 'Core'],
			'Obliques' => ['Obliques'],
			'Grands dorsaux' => ['Lats', 'Back', 'Upper Back', 'Latissimus Dorsi', 'Lower back'],
			'Rhomboïdes' => ['Rhomboids', 'Middle Back'],
			'Trapèzes' => ['Traps', 'Trapezius'],
			'Déltoïdes' => ['Shoulders', 'Delts', 'Deltoids'],
			'Biceps' => ['Biceps'],
			'Triceps' => ['Triceps'],
			'Brachiaux' => ['Brachialis', 'Brachioradialis'],
			'Avant-bras' => ['Forearms', 'Forearm'],
			'Fessier' => ['Glutes', 'Gluteus Maximus'],
			'Quadriceps' => ['Quads', 'Quadriceps', 'Inner Quadriceps', 'Outer Quadricep', 'Inner Thigh', 'Groin'],
			'Ishio' => ['Hamstrings', 'Ischios', 'Ischio-jambiers', 'Ischiocruraux'],
			'Mollets' => ['Calves', 'Gastrocnemius', 'Soleus'],
		];

		// Récupérer les catégories existantes
		$categoryNameToModel = [];
		foreach (array_keys($targetMappings) as $categoryName) {
			$category = Category::where('name', $categoryName)->first();
			if ($category) {
				$categoryNameToModel[$categoryName] = $category;
			}
		}
		if (empty($categoryNameToModel)) {
			return;
		}

		// Préparer la résolution locale des médias (images/vidéos)
		$imagesDir = public_path('storage/trainings');
		$videosDir = public_path('storage/training_videos');
		$resolveVideo = function (string $exerciseName) use ($videosDir): ?string {
			if (!File::exists($videosDir)) {
				return null;
			}
			$patterns = [];
			$patterns[] = strtolower(str_replace(' ', '_', trim($exerciseName)));
			$patterns[] = strtolower(Str::slug($exerciseName, '-'));
			$patterns[] = strtolower(str_replace(' ', '', trim($exerciseName)));
			$best = null;
			foreach (File::files($videosDir) as $file) {
				$filename = strtolower($file->getFilename());
				if (!preg_match('/\.(mp4|mov|webm)$/i', $filename)) {
					continue;
				}
				foreach ($patterns as $p) {
					if (strpos($filename, $p) !== false) {
						if (strpos($filename, '_1') === false) {
							$best = '/storage/training_videos/' . $file->getFilename();
							break 2;
						}
						$best = $best ?? '/storage/training_videos/' . $file->getFilename();
					}
				}
			}
			return $best;
		};
		$resolveImage = function (string $exerciseName, ?string $gender = null) use ($imagesDir): ?string {
			if (!File::exists($imagesDir)) {
				return null;
			}
			$patterns = [];
			$patterns[] = strtolower(Str::slug($exerciseName, '-'));
			$patterns[] = strtolower(str_replace(' ', '-', trim($exerciseName)));
			$patterns[] = strtolower(str_replace(' ', '_', trim($exerciseName)));
			$patterns[] = strtolower(str_replace(' ', '', trim($exerciseName)));
			$best = null;
			foreach (File::files($imagesDir) as $file) {
				$filename = strtolower($file->getFilename());
				if (!preg_match('/\.(jpg|jpeg|png|webp)$/i', $filename)) {
					continue;
				}
				// Filtre de genre robuste: match sur préfixe 'male-' / 'female-' pour éviter que 'female' matche 'male'
				if ($gender === 'male' && strpos($filename, 'male-') !== 0) {
					continue;
				}
				if ($gender === 'female' && strpos($filename, 'female-') !== 0) {
					continue;
				}
				foreach ($patterns as $p) {
					if (strpos($filename, $p) !== false) {
						$best = '/storage/trainings/' . $file->getFilename();
						break 2;
					}
				}
			}
			return $best;
		};

		// Collecter récursivement les exercices du dataset
		$exercises = [];
		$collect = function ($node) use (&$exercises, &$collect) {
			if (is_array($node)) {
				$hasName = isset($node['name']) && is_string($node['name']);
				$mechanicName = null;
				if (isset($node['mechanic']) && is_array($node['mechanic']) && isset($node['mechanic']['name'])) {
					$mechanicName = $node['mechanic']['name'];
				}
				$hasMuscles = isset($node['muscles']) && is_array($node['muscles']);

				if ($hasName && $mechanicName && $hasMuscles) {
					$muscleNames = [];
					foreach ($node['muscles'] as $m) {
						if (is_array($m) && isset($m['name']) && is_string($m['name'])) {
							$muscleNames[] = $m['name'];
						}
					}
					if (!empty($muscleNames) && in_array($mechanicName, ['Isolation', 'Compound'])) {
						$exercises[] = [
							'name' => trim($node['name']),
							'mechanic' => $mechanicName,
							'muscle_names' => $muscleNames,
						];
					}
				}

				foreach ($node as $child) {
					if (is_array($child)) {
						$collect($child);
					}
				}
			}
		};
		$collect($decoded);

		// Index pour éviter les doublons par nom
		$normalizeList = function (array $list) {
			$byName = [];
			foreach ($list as $ex) {
				if (!isset($ex['name']) || $ex['name'] === '') {
					continue;
				}
				$byName[$ex['name']] = $ex;
			}
			return array_values($byName);
		};

		// Importer tout le dataset
		$exercisesAll = $normalizeList($exercises);
		foreach ($exercisesAll as $ex) {
			$training = Training::firstOrCreate(
				['name' => $ex['name']],
				[
					'description' => 'Exercice importé (ALL)',
					'image' => null,
					'video' => null,
					'user_id' => null,
				]
			);

			// Attacher toutes les catégories correspondantes selon mapping
			$attachIds = [];
			foreach ($targetMappings as $categoryName => $aliases) {
				if (!isset($categoryNameToModel[$categoryName])) {
					continue;
				}
				foreach ($ex['muscle_names'] as $m) {
					if (in_array($m, $aliases, true)) {
						$attachIds[$categoryNameToModel[$categoryName]->id] = $categoryNameToModel[$categoryName]->id;
					}
				}
			}
			if (!empty($attachIds)) {
				$training->categories()->syncWithoutDetaching(array_values($attachIds));
			}

			// Renseigner/corriger les médias locaux si disponibles
			$imageMale = $resolveImage($ex['name'], 'male');
			$imageFemale = $resolveImage($ex['name'], 'female');
			$video = $resolveVideo($ex['name']);
			$dirty = false;

			if ($imageMale && $training->image_homme !== $imageMale) { $training->image_homme = $imageMale; $dirty = true; }
			if ($imageFemale && $training->image_femme !== $imageFemale) { $training->image_femme = $imageFemale; $dirty = true; }
			if ($imageMale || $imageFemale) {
				$preferred = $imageMale ?: $imageFemale;
				if ($training->image !== $preferred) { $training->image = $preferred; $dirty = true; }
			}
			if ($video && $training->video !== $video) { $training->video = $video; $dirty = true; }
			if ($dirty) { $training->save(); }
		}
	}
} 
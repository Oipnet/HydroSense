<?php

namespace App\DataFixtures;

use App\Entity\CultureProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les profils de cultures hydroponiques
 * 
 * Données basées sur les recommandations standards pour l'hydroponie.
 * Sources : guides hydroponiques professionnels et recherche agronomique.
 */
class CultureProfileFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $profiles = [
            [
                'name' => 'Laitue',
                'phMin' => 5.5,
                'phMax' => 6.5,
                'ecMin' => 0.8,
                'ecMax' => 1.2,
                'waterTempMin' => 15.0,
                'waterTempMax' => 20.0,
            ],
            [
                'name' => 'Basilic',
                'phMin' => 5.5,
                'phMax' => 6.5,
                'ecMin' => 1.0,
                'ecMax' => 1.6,
                'waterTempMin' => 18.0,
                'waterTempMax' => 24.0,
            ],
            [
                'name' => 'Fraises',
                'phMin' => 5.5,
                'phMax' => 6.5,
                'ecMin' => 1.2,
                'ecMax' => 1.8,
                'waterTempMin' => 16.0,
                'waterTempMax' => 22.0,
            ],
            [
                'name' => 'Tomates',
                'phMin' => 5.5,
                'phMax' => 6.5,
                'ecMin' => 2.0,
                'ecMax' => 3.5,
                'waterTempMin' => 18.0,
                'waterTempMax' => 24.0,
            ],
            [
                'name' => 'Concombres',
                'phMin' => 5.5,
                'phMax' => 6.0,
                'ecMin' => 1.7,
                'ecMax' => 2.5,
                'waterTempMin' => 18.0,
                'waterTempMax' => 24.0,
            ],
            [
                'name' => 'Poivrons',
                'phMin' => 5.5,
                'phMax' => 6.5,
                'ecMin' => 1.8,
                'ecMax' => 2.5,
                'waterTempMin' => 18.0,
                'waterTempMax' => 25.0,
            ],
            [
                'name' => 'Épinards',
                'phMin' => 6.0,
                'phMax' => 7.0,
                'ecMin' => 1.2,
                'ecMax' => 1.8,
                'waterTempMin' => 15.0,
                'waterTempMax' => 20.0,
            ],
            [
                'name' => 'Roquette',
                'phMin' => 6.0,
                'phMax' => 7.0,
                'ecMin' => 0.8,
                'ecMax' => 1.2,
                'waterTempMin' => 16.0,
                'waterTempMax' => 22.0,
            ],
            [
                'name' => 'Menthe',
                'phMin' => 6.0,
                'phMax' => 7.0,
                'ecMin' => 1.8,
                'ecMax' => 2.4,
                'waterTempMin' => 18.0,
                'waterTempMax' => 25.0,
            ],
            [
                'name' => 'Persil',
                'phMin' => 5.5,
                'phMax' => 6.5,
                'ecMin' => 0.8,
                'ecMax' => 1.8,
                'waterTempMin' => 16.0,
                'waterTempMax' => 22.0,
            ],
            [
                'name' => 'Coriandre',
                'phMin' => 6.0,
                'phMax' => 6.8,
                'ecMin' => 1.2,
                'ecMax' => 1.8,
                'waterTempMin' => 16.0,
                'waterTempMax' => 24.0,
            ],
            [
                'name' => 'Micro-pousses',
                'phMin' => 5.5,
                'phMax' => 6.5,
                'ecMin' => 0.6,
                'ecMax' => 1.0,
                'waterTempMin' => 18.0,
                'waterTempMax' => 22.0,
            ],
            [
                'name' => 'Chou frisé (Kale)',
                'phMin' => 5.5,
                'phMax' => 6.5,
                'ecMin' => 1.2,
                'ecMax' => 1.5,
                'waterTempMin' => 16.0,
                'waterTempMax' => 22.0,
            ],
            [
                'name' => 'Pak Choï',
                'phMin' => 6.0,
                'phMax' => 7.0,
                'ecMin' => 1.0,
                'ecMax' => 1.5,
                'waterTempMin' => 16.0,
                'waterTempMax' => 22.0,
            ],
        ];

        foreach ($profiles as $data) {
            $profile = new CultureProfile();
            $profile->setName($data['name'])
                ->setPhMin($data['phMin'])
                ->setPhMax($data['phMax'])
                ->setEcMin($data['ecMin'])
                ->setEcMax($data['ecMax'])
                ->setWaterTempMin($data['waterTempMin'])
                ->setWaterTempMax($data['waterTempMax']);

            $manager->persist($profile);
        }

        $manager->flush();

        echo "✅ " . count($profiles) . " profils de cultures ont été créés avec succès.\n";
    }
}

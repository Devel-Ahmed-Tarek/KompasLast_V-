<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CityCoordinatesSeeder extends Seeder
{
    /**
     * Representative lat/lng for German Bundesländer centers.
     * Keys are matched against city name (de or en).
     *
     * @var array<string, array{lat: float, lng: float}>
     */
    private array $coordinates = [
        'Baden-Württemberg'      => ['lat' => 48.6616, 'lng' => 9.3501],
        'Bayern'                 => ['lat' => 48.7904, 'lng' => 11.4979],
        'Berlin'                 => ['lat' => 52.5200, 'lng' => 13.4050],
        'Brandenburg'            => ['lat' => 52.4125, 'lng' => 12.5316],
        'Bremen'                 => ['lat' => 53.0793, 'lng' => 8.8017],
        'Hamburg'                => ['lat' => 53.5511, 'lng' => 9.9937],
        'Hessen'                 => ['lat' => 50.6521, 'lng' => 9.1624],
        'Mecklenburg-Vorpommern' => ['lat' => 53.6127, 'lng' => 12.4296],
        'Niedersachsen'          => ['lat' => 52.6367, 'lng' => 9.8451],
        'Nordrhein-Westfalen'    => ['lat' => 51.4332, 'lng' => 7.6616],
        'Rheinland-Pfalz'        => ['lat' => 49.9130, 'lng' => 7.4500],
        'Saarland'               => ['lat' => 49.3964, 'lng' => 7.0230],
        'Sachsen'                => ['lat' => 51.1045, 'lng' => 13.2017],
        'Sachsen-Anhalt'         => ['lat' => 51.9503, 'lng' => 11.6923],
        'Schleswig-Holstein'     => ['lat' => 54.2194, 'lng' => 9.6961],
        'Thüringen'              => ['lat' => 50.9848, 'lng' => 11.0299],
    ];

    public function run(): void
    {
        $updated = 0;
        $skipped = 0;

        $cities = City::all();

        foreach ($cities as $city) {
            $de = $city->getTranslation('name', 'de', false);
            $en = $city->getTranslation('name', 'en', false);

            $coords = $this->coordinates[$de]
                ?? $this->coordinates[$en]
                ?? null;

            if (! $coords) {
                $skipped++;
                $label = $de ?: $en ?: ("#{$city->id}");
                $this->command?->warn("Skipped city without coordinates: {$label}");
                continue;
            }

            $city->update([
                'latitude'  => $coords['lat'],
                'longitude' => $coords['lng'],
            ]);

            $updated++;
        }

        $this->command?->info("Updated {$updated} city/cities with coordinates.");
        if ($skipped > 0) {
            $this->command?->warn("Skipped {$skipped} city/cities (no matching coordinates).");
        }
    }
}

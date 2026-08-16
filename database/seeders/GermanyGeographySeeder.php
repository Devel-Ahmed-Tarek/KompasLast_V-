<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GermanyGeographySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/germany_places.json');
        if (! is_readable($path)) {
            throw new \RuntimeException("Missing geography file: {$path}");
        }

        $places = json_decode((string) file_get_contents($path), true);
        if (! is_array($places) || $places === []) {
            throw new \RuntimeException('germany_places.json is empty or invalid.');
        }

        $germany = $this->seedGermany();
        $states = $this->seedStates($germany);
        $this->cleanupLegacyStateCities($germany, $states);
        $imported = $this->importLocalPlaces($germany, $states, $places);

        $this->command?->info("Imported/updated {$imported} German places from local dataset.");
        $this->command?->info('States: ' . State::where('country_id', $germany->id)->count());
        $this->command?->info('Cities (Stadt): ' . City::where('country_id', $germany->id)->where('place_type', City::TYPE_CITY)->count());
        $this->command?->info('Municipalities (Gemeinde): ' . City::where('country_id', $germany->id)->where('place_type', City::TYPE_MUNICIPALITY)->count());
    }

    private function seedGermany(): Country
    {
        $germany = Country::all()->first(function (Country $country) {
            $de = $country->getTranslation('name', 'de', false);
            $en = $country->getTranslation('name', 'en', false);

            return $de === 'Deutschland' || $en === 'Germany';
        });

        if (! $germany) {
            $germany = Country::create([
                'name' => [
                    'en' => 'Germany',
                    'ar' => 'ألمانيا',
                    'de' => 'Deutschland',
                    'fr' => 'Allemagne',
                    'it' => 'Germania',
                ],
            ]);
        }

        return $germany;
    }

    /**
     * @return array<string, State>
     */
    private function seedStates(Country $germany): array
    {
        $states = [
            ['code' => 'BW', 'lat' => 48.6616, 'lng' => 9.3501, 'en' => 'Baden-Württemberg', 'de' => 'Baden-Württemberg', 'ar' => 'بادن-فورتمبيرغ', 'fr' => 'Bade-Wurtemberg', 'it' => 'Baden-Württemberg'],
            ['code' => 'BY', 'lat' => 48.7904, 'lng' => 11.4979, 'en' => 'Bavaria', 'de' => 'Bayern', 'ar' => 'بافاريا', 'fr' => 'Bavière', 'it' => 'Baviera'],
            ['code' => 'BE', 'lat' => 52.5200, 'lng' => 13.4050, 'en' => 'Berlin', 'de' => 'Berlin', 'ar' => 'برلين', 'fr' => 'Berlin', 'it' => 'Berlino'],
            ['code' => 'BB', 'lat' => 52.4125, 'lng' => 12.5316, 'en' => 'Brandenburg', 'de' => 'Brandenburg', 'ar' => 'براندنبورغ', 'fr' => 'Brandebourg', 'it' => 'Brandeburgo'],
            ['code' => 'HB', 'lat' => 53.0793, 'lng' => 8.8017, 'en' => 'Bremen', 'de' => 'Bremen', 'ar' => 'بريمن', 'fr' => 'Brême', 'it' => 'Brema'],
            ['code' => 'HH', 'lat' => 53.5511, 'lng' => 9.9937, 'en' => 'Hamburg', 'de' => 'Hamburg', 'ar' => 'هامبورغ', 'fr' => 'Hambourg', 'it' => 'Amburgo'],
            ['code' => 'HE', 'lat' => 50.6521, 'lng' => 9.1624, 'en' => 'Hesse', 'de' => 'Hessen', 'ar' => 'هيسن', 'fr' => 'Hesse', 'it' => 'Assia'],
            ['code' => 'MV', 'lat' => 53.6127, 'lng' => 12.4296, 'en' => 'Mecklenburg-Western Pomerania', 'de' => 'Mecklenburg-Vorpommern', 'ar' => 'مكلنبورغ-فوربومرن', 'fr' => 'Mecklembourg-Poméranie-Occidentale', 'it' => 'Meclemburgo-Pomerania Anteriore'],
            ['code' => 'NI', 'lat' => 52.6367, 'lng' => 9.8451, 'en' => 'Lower Saxony', 'de' => 'Niedersachsen', 'ar' => 'ساكسونيا السفلى', 'fr' => 'Basse-Saxe', 'it' => 'Bassa Sassonia'],
            ['code' => 'NW', 'lat' => 51.4332, 'lng' => 7.6616, 'en' => 'North Rhine-Westphalia', 'de' => 'Nordrhein-Westfalen', 'ar' => 'شمال الراين-وستفاليا', 'fr' => 'Rhénanie-du-Nord-Westphalie', 'it' => 'Renania Settentrionale-Vestfalia'],
            ['code' => 'RP', 'lat' => 49.9130, 'lng' => 7.4500, 'en' => 'Rhineland-Palatinate', 'de' => 'Rheinland-Pfalz', 'ar' => 'راينلاند بالاتينات', 'fr' => 'Rhénanie-Palatinat', 'it' => 'Renania-Palatinato'],
            ['code' => 'SL', 'lat' => 49.3964, 'lng' => 7.0230, 'en' => 'Saarland', 'de' => 'Saarland', 'ar' => 'سارلاند', 'fr' => 'Sarre', 'it' => 'Saarland'],
            ['code' => 'SN', 'lat' => 51.1045, 'lng' => 13.2017, 'en' => 'Saxony', 'de' => 'Sachsen', 'ar' => 'ساكسونيا', 'fr' => 'Saxe', 'it' => 'Sassonia'],
            ['code' => 'ST', 'lat' => 51.9503, 'lng' => 11.6923, 'en' => 'Saxony-Anhalt', 'de' => 'Sachsen-Anhalt', 'ar' => 'ساكسونيا أنهالت', 'fr' => 'Saxe-Anhalt', 'it' => 'Sassonia-Anhalt'],
            ['code' => 'SH', 'lat' => 54.2194, 'lng' => 9.6961, 'en' => 'Schleswig-Holstein', 'de' => 'Schleswig-Holstein', 'ar' => 'شليسفيغ-هولشتاين', 'fr' => 'Schleswig-Holstein', 'it' => 'Schleswig-Holstein'],
            ['code' => 'TH', 'lat' => 50.9848, 'lng' => 11.0299, 'en' => 'Thuringia', 'de' => 'Thüringen', 'ar' => 'تورينغن', 'fr' => 'Thuringe', 'it' => 'Turingia'],
        ];

        $map = [];
        foreach ($states as $row) {
            $map[$row['code']] = State::query()->updateOrCreate(
                [
                    'country_id' => $germany->id,
                    'code'       => $row['code'],
                ],
                [
                    'name' => [
                        'en' => $row['en'],
                        'de' => $row['de'],
                        'ar' => $row['ar'],
                        'fr' => $row['fr'],
                        'it' => $row['it'],
                    ],
                    'latitude'  => $row['lat'],
                    'longitude' => $row['lng'],
                ]
            );
        }

        return $map;
    }

    /**
     * @param  array<string, State>  $states
     */
    private function cleanupLegacyStateCities(Country $germany, array $states): void
    {
        $cities = City::query()->where('country_id', $germany->id)->whereNull('ags_code')->get();
        foreach ($cities as $city) {
            $de = $city->getTranslation('name', 'de', false);
            $en = $city->getTranslation('name', 'en', false);
            foreach ($states as $state) {
                $stateDe = $state->getTranslation('name', 'de', false);
                $stateEn = $state->getTranslation('name', 'en', false);
                if ($de === $stateDe || $en === $stateEn || $de === $stateEn || $en === $stateDe) {
                    $city->state_id = $state->id;
                    $city->place_type = City::TYPE_REGION;
                    $city->latitude = $city->latitude ?: $state->latitude;
                    $city->longitude = $city->longitude ?: $state->longitude;
                    $city->save();
                    break;
                }
            }
        }
    }

    /**
     * @param  array<string, State>  $states
     * @param  array<int, array<string, mixed>>  $places
     */
    private function importLocalPlaces(Country $germany, array $states, array $places): int
    {
        $existingAgs = City::query()
            ->whereNotNull('ags_code')
            ->pluck('id', 'ags_code')
            ->all();

        $now = now();
        $batch = [];
        $count = 0;

        foreach ($places as $place) {
            $stateCode = $place['state'] ?? null;
            $ags = $place['ags'] ?? null;
            if (! $stateCode || ! $ags || ! isset($states[$stateCode])) {
                continue;
            }

            $state = $states[$stateCode];
            $name = $place['name'];
            $type = ($place['type'] ?? '') === 'city' ? City::TYPE_CITY : City::TYPE_MUNICIPALITY;
            $payload = [
                'country_id' => $germany->id,
                'state_id'   => $state->id,
                'place_type' => $type,
                'ags_code'   => $ags,
                'name'       => json_encode([
                    'de' => $name,
                    'en' => $name,
                    'ar' => $name,
                    'fr' => $name,
                    'it' => $name,
                ], JSON_UNESCAPED_UNICODE),
                'latitude'   => $place['lat'],
                'longitude'  => $place['lng'],
                'updated_at' => $now,
            ];

            if (isset($existingAgs[$ags])) {
                DB::table('cities')->where('id', $existingAgs[$ags])->update($payload);
                $count++;
                continue;
            }

            $payload['created_at'] = $now;
            $batch[] = $payload;

            if (count($batch) >= 500) {
                DB::table('cities')->insert($batch);
                $count += count($batch);
                $batch = [];
            }
        }

        if ($batch) {
            DB::table('cities')->insert($batch);
            $count += count($batch);
        }

        return $count;
    }
}

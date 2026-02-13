<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class MinyaCitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء دولة ألمانيا (Germany) إذا لم تكن موجودة
        $germany = Country::firstOrCreate(
            [],
            [
                'name' => [
                    'en' => 'Germany',
                    'ar' => 'ألمانيا',
                    'de' => 'Deutschland',
                    'fr' => 'Allemagne',
                    'it' => 'Germania',
                ]
            ]
        );

        // قائمة كاملة بمدن ألمانيا
        $states = [
            [
                "en" => "Baden-Württemberg",
                "ar" => "بادن-فورتمبيرغ",
                "de" => "Baden-Württemberg",
                "fr" => "Bade-Wurtemberg",
                "it" => "Baden-Württemberg",
            ],
            [
                "en" => "Bayern",
                "ar" => "بافاريا",
                "de" => "Bayern",
                "fr" => "Bavière",
                "it" => "Baviera",
            ],
            [
                "en" => "Berlin",
                "ar" => "برلين",
                "de" => "Berlin",
                "fr" => "Berlin",
                "it" => "Berlino",
            ],
            [
                "en" => "Brandenburg",
                "ar" => "براندنبورغ",
                "de" => "Brandenburg",
                "fr" => "Brandebourg",
                "it" => "Brandeburgo",
            ],
            [
                "en" => "Bremen",
                "ar" => "بريمن",
                "de" => "Bremen",
                "fr" => "Brême",
                "it" => "Brema",
            ],
            [
                "en" => "Hamburg",
                "ar" => "هامبورغ",
                "de" => "Hamburg",
                "fr" => "Hambourg",
                "it" => "Amburgo",
            ],
            [
                "en" => "Hessen",
                "ar" => "هيسن",
                "de" => "Hessen",
                "fr" => "Hesse",
                "it" => "Assia",
            ],
            [
                "en" => "Mecklenburg-Vorpommern",
                "ar" => "مكلنبورغ-فوربومرن",
                "de" => "Mecklenburg-Vorpommern",
                "fr" => "Mecklembourg-Poméranie-Occidentale",
                "it" => "Meclemburgo-Pomerania Anteriore",
            ],
            [
                "en" => "Niedersachsen",
                "ar" => "ساكسونيا السفلى",
                "de" => "Niedersachsen",
                "fr" => "Basse-Saxe",
                "it" => "Bassa Sassonia",
            ],
            [
                "en" => "Nordrhein-Westfalen",
                "ar" => "شمال الراين-وستفاليا",
                "de" => "Nordrhein-Westfalen",
                "fr" => "Rhénanie-du-Nord-Westphalie",
                "it" => "Renania Settentrionale-Vestfalia",
            ],
            [
                "en" => "Rheinland-Pfalz",
                "ar" => "راينلاند بالاتينات",
                "de" => "Rheinland-Pfalz",
                "fr" => "Rhénanie-Palatinat",
                "it" => "Renania-Palatinato",
            ],
            [
                "en" => "Saarland",
                "ar" => "سارلاند",
                "de" => "Saarland",
                "fr" => "Sarre",
                "it" => "Saarland",
            ],
            [
                "en" => "Sachsen",
                "ar" => "ساكسونيا",
                "de" => "Sachsen",
                "fr" => "Saxe",
                "it" => "Sassonia",
            ],
            [
                "en" => "Sachsen-Anhalt",
                "ar" => "ساكسونيا أنهالت",
                "de" => "Sachsen-Anhalt",
                "fr" => "Saxe-Anhalt",
                "it" => "Sassonia-Anhalt",
            ],
            [
                "en" => "Schleswig-Holstein",
                "ar" => "شليسفيغ-هولشتاين",
                "de" => "Schleswig-Holstein",
                "fr" => "Schleswig-Holstein",
                "it" => "Schleswig-Holstein",
            ],
            [
                "en" => "Thüringen",
                "ar" => "تورينغن",
                "de" => "Thüringen",
                "fr" => "Thuringe",
                "it" => "Turingia",
            ],
        ];
        // إضافة جميع المحافظات
        foreach ($states as $cityData) {
            City::firstOrCreate(
                [
                    'country_id' => $germany->id,
                    'name' => $cityData
                ]
            );
        }

        $this->command->info('Germany country and all major cities have been seeded successfully!');
    }
}

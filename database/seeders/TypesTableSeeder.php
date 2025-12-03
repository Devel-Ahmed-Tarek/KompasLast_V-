<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('types')->insert([
            [
                'name'  => json_encode([
                    'en' => 'Moving',
                    'de' => 'Moving',
                    'fr' => 'Moving',
                    'it' => 'Moving',
                ]),
                'price' => 100,
            ],
            [
                'name'  => json_encode([
                    'en' => 'Moving & cleaning',
                    'fr' => 'Moving & cleaning',
                    'de' => 'Moving & cleaning',
                    'it' => 'Moving & cleaning',
                ]),
                'price' => 100,
            ],
            [
                'name'  => json_encode([
                    'en' => 'Cleaning',
                    'de' => 'Cleaning',
                    'fr' => 'Cleaning',
                    'it' => 'Cleaning',
                ]),
                'price' => 100,
            ],
            [
                'name'  => json_encode([
                    'en' => 'Disposal',
                    'de' => 'Disposal',
                    'fr' => 'Disposal',
                    'it' => 'Disposal',
                ]),
                'price' => 100,
            ],
            [
                'name'  => json_encode([
                    'en' => 'Storage',
                    'de' => 'Storage',
                    'fr' => 'Storage',
                    'it' => 'Storage',
                ]),
                'price' => 100,
            ],
        ]);
    }
}

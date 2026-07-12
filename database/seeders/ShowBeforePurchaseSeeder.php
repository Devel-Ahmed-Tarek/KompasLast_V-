<?php

namespace Database\Seeders;

use App\Models\TypeQuestion;
use Illuminate\Database\Seeder;

class ShowBeforePurchaseSeeder extends Seeder
{
    /**
     * Set all questions to visible in the shop before purchase.
     */
    public function run(): void
    {
        $updated = TypeQuestion::query()
            ->where('show_before_purchase', false)
            ->update(['show_before_purchase' => true]);

        $this->command->info("Updated {$updated} question(s): show_before_purchase = true");
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (! Schema::hasColumn('offers', 'track_token')) {
                $table->string('track_token', 64)->nullable()->unique()->after('confirm_token');
            }
        });

        // Backfill so existing offers can open the tracking portal.
        DB::table('offers')->whereNull('track_token')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('offers')->where('id', $row->id)->update([
                    'track_token' => Str::random(64),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (Schema::hasColumn('offers', 'track_token')) {
                $table->dropUnique(['track_token']);
                $table->dropColumn('track_token');
            }
        });
    }
};

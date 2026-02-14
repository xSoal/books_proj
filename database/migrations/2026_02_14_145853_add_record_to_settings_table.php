<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->insert([
            'type'       => 'about_us_full',
            'value'      => '', 
            'ua'         => 'Про нас (повна версія)',
            'en'         => 'About us (full version)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('type', 'about_us_full')->delete();
    }
};
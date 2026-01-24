<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('settings', function (Blueprint $table) {
            $table->text('ua');
            $table->text('en');
        });


        DB::table('settings')->insert([
            'type' => 'about_us',
            'ua' => 'Про нас',
            'en' => 'About project text',
            'created_at' => now(), 
            'updated_at' => now(), 
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            DB::table('settings')->where('type', 'about_us')->delete();
        });
    }
};

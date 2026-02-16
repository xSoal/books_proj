<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('characteristics', function (Blueprint $table) {
            $table->boolean('is_author')->default(false);
            $table->boolean('is_type')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characteristics', function (Blueprint $table) {
            $table->dropColumn(['is_author', 'is_type']);
        });
    }
};

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
            $table->boolean('in_filter')->default(false)->after('active');
            $table->boolean('is_numeric')->default(false)->after('in_filter');
            $table->boolean('can_sorted_by')->default(false)->after('is_numeric');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characteristics', function (Blueprint $table) {
            $table->dropColumn(['in_filter', 'is_numeric', 'can_sorted_by']);
        });
    }
};

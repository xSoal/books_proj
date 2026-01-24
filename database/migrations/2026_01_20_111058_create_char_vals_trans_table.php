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
        Schema::create('char_vals_trans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('char_val_id')
                ->constrained('char_vals')
                ->onDelete('cascade');
            $table->string('lang');
            $table->string('name');
            $table->text('description');
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('char_vals_trans');
    }
};

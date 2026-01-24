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
        Schema::create('tags_translates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')
                ->constrained('tags')
                ->onDelete('cascade');
            $table->string('lang');
            $table->string('slug');
            $table->text('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags_translates');
    }
};

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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'search' или 'view'
            $table->unsignedBigInteger('book_id')->nullable()->index(); // Для статистики просмотров конкретных записей 
            $table->string('search_query')->nullable();
            $table->json('filters')->nullable(); // фильтрі
            $table->integer('results_count')->default(0); 
            $table->string('locale', 2); //  ua en
            $table->ipAddress('user_ip')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $fillable = [
        'type', 
        'book_id', 
        'search_query', 
        'filters', 
        'results_count', 
        'locale', 
        'user_ip'
    ];

    // JSON из базы в массив PHP
    protected $casts = [
        'filters' => 'array',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
<?php

namespace App\Models;

use App\Models\BookTranslate;
use App\Models\CharacteristicValue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'img',
        'sort',
        'active'
    ];

    public function translates(){
        return $this->hasMany(BookTranslate::class, 'book_id', 'id');
    }

}

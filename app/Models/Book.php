<?php

namespace App\Models;

use App\Models\BookTranslate;
use App\Models\CharacteristicValue;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'img',
        'sort',
        'active',
    ];

    public function translates(){
        return $this->hasMany(BookTranslate::class, 'book_id', 'id');
    }
    
    public function tags(){
        return $this->belongsToMany(Tag::class, 'books_tags'); 
    }

    public function char_vals()
    {
        return $this->belongsToMany(
            CharacteristicValue::class, 
            'books_char_val',           
            'book_id',                  
            'char_val_id'               
        );
    }

}

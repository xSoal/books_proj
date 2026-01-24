<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacteristicTranslate extends Model
{
    use HasFactory;
    
    protected $table = 'characteristics_translates';

    protected $fillable = [
        'characteristic_id',
        'lang',
        'name',
        'description',
        'slug'
    ];
}

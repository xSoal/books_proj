<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacteristicValueTranslate extends Model
{
    use HasFactory;

    protected $table = 'char_vals_trans';

    protected $fillable = [
        'char_val_id',
        'lang',
        'name',
        'description',
        'slug'
    ];


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacteristicValue extends Model
{
    use HasFactory;

    protected $table = 'char_vals';

    protected $fillable = [
        'characteristic_id',
        'active'
    ];

    public function translates(){
        return $this->hasMany(CharacteristicValueTranslate::class, 'char_val_id', 'id');
    }
}

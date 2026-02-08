<?php

namespace App\Models;

use App\Models\CharacteristicTranslate;
use App\Models\CharacteristicValue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Characteristic extends Model
{
    use HasFactory;

    protected $fillable = [
        'img',
        'active',
        'sort'
    ];

    public function translates(){
        return $this->hasMany(CharacteristicTranslate::class, 'characteristic_id', 'id');
    }

    public function char_vals() {
        return $this->hasMany(CharacteristicValue::class, 'characteristic_id', 'id');
    }
}

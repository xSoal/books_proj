<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translate extends Model 
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'en',
        'ua',
    ];


}

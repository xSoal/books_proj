<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagTranslate extends Model
{
    use HasFactory;

    protected $table = 'tags_translates';

    protected $fillable = [
        'name',
        'slug',
        'lang'
    ];
}

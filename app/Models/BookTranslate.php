<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookTranslate extends Model
{
    use HasFactory;

    protected $table = 'books_translates';

    protected $fillable = [
        'lang',
        'slug',
        'name',
        'anotation',
        'meta_title',
        'meta_desc',
        'og_title',
        'og_desc',
        'og_img',
    ];
}

<?php

namespace App\Models;

use App\Models\TagTranslate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
        'need_approve',
    ];

    
    public function translates(){
        return $this->hasMany(TagTranslate::class, 'tag_id', 'id');
    }
}

<?php

namespace App\Models;

use App\Models\PartnerTranslate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Partner extends Model 
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'img',
        'active',
        'link'
    ];

    public function translates() {
        return $this->hasMany(PartnerTranslate::class, 'partner_id', 'id');
    }


}

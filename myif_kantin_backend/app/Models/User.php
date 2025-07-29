<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $primaryKey = 'user_id';
    
    protected $fillable = [
        'name',
        'phone',
        'email',
        'username',
        'password',
        'role',
        'extra'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'extra' => 'array',
        'password' => 'hashed',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }
}
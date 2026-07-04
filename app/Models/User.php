<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'coins',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'coins' => 'integer',
    ];

    public function gachaLogs(): HasMany
    {
        return $this->hasMany(GachaLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function deductCoins(int $amount): bool
    {
        if ($this->coins < $amount) {
            return false;
        }
        $this->coins -= $amount;
        return true;
    }
}

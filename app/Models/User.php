<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'status', 'last_login_at', 'email_verified_at'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'last_login_at' => 'datetime', 'password' => 'hashed']; }

    public function profile(): HasOne { return $this->hasOne(Profile::class); }
    public function vcards(): HasMany { return $this->hasMany(VCard::class); }
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function domains(): HasMany { return $this->hasMany(CustomDomain::class); }
}

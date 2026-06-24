<?php

namespace App\Models;

use App\Infrastructure\Persistence\Models\LeagueModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasUuids, HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'is_admin',
        'last_visited_league_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public $incrementing = false;

    protected $keyType = 'string';

    public function leagues(): BelongsToMany
    {
        return $this->belongsToMany(LeagueModel::class, 'league_user', 'user_id', 'league_id')
            ->withPivot('role')
            ->withTimestamps();
    }
}

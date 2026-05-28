<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'discipline_id',
        'role',
    ];

    protected $appends = [
        'discipline_names',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department(): BelongsTo
{
    return $this->belongsTo(Department::class);
}
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function disciplines()
    {
        return $this->belongsToMany(Discipline::class, 'discipline_user', 'user_id', 'discipline_id');
    }

    public function getDisciplineNamesAttribute(): string
    {
        $names = $this->disciplines->pluck('name')->filter()->all();

        if (empty($names) && $this->discipline) {
            return $this->discipline->name;
        }

        return collect($names)->implode(', ');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}

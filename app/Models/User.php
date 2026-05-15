<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_ORGANIZATOR = 'organizator';

    public const ROLE_ADMIN = 'admin';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_BLOCKED = 'blocked';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'is_blocked',
        'status',
        'avatar',
        'phone',
        'location',
        'password',
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
            'is_blocked' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOrganizator(): bool
    {
        return $this->role === self::ROLE_ORGANIZATOR;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'organizator_id');
    }

    public function sectionApplications(): HasMany
    {
        return $this->hasMany(SectionApplication::class);
    }

    public function sectionReviews(): HasMany
    {
        return $this->hasMany(SectionReview::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class)->latest('id');
    }
}

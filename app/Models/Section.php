<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Section extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'organizator_id',
        'title',
        'slug',
        'description',
        'price',
        'image',
        'location',
        'sport_type',
        'age_group',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function organizator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizator_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(SectionApplication::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SectionSchedule::class)->latest('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SectionReview::class)->latest('created_at');
    }

    public function address(): HasOne
    {
        return $this->hasOne(SectionAddress::class);
    }
}

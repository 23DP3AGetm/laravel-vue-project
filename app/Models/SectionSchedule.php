<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_day_off',
    ];

    protected function casts(): array
    {
        return [
            'is_day_off' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'city',
        'street',
        'full_address',
        'coordinates',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}

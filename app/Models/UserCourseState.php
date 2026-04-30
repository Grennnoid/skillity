<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCourseState extends Model
{
    protected $fillable = [
        'user_id',
        'course_slug',
        'course_title',
        'is_enrolled',
        'is_favorite',
    ];

    protected $casts = [
        'is_enrolled' => 'boolean',
        'is_favorite' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


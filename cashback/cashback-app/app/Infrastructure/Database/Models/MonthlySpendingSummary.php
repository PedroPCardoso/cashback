<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlySpendingSummary extends Model
{
    protected $fillable = [
        'category_id',
        'year',
        'month',
        'total_spent_cents',
        'cashback_earned_cents',
        'status',
    ];

    /**
     */
    /** @phpstan-ignore missingType.generics */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

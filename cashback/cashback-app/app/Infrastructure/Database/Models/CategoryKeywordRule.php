<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryKeywordRule extends Model
{
    protected $fillable = [
        'category_id',
        'keyword',
        'priority',
    ];

    /**
     */
    /** @phpstan-ignore missingType.generics */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

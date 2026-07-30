<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'description',
        'amount_cents',
        'currency',
        'date',
        'category_id',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'date' => 'date',
    ];

    /**
     */
    /** @phpstan-ignore missingType.generics */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

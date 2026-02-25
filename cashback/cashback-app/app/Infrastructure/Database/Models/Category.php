<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'type',
        'monthly_limit_cents',
        'cashback_rate',
    ];

    protected $casts = [
        'monthly_limit_cents' => 'integer',
        'cashback_rate' => 'float',
    ];

    /**
     */
    /** @phpstan-ignore missingType.generics */
    public function keywordRules(): HasMany
    {
        return $this->hasMany(CategoryKeywordRule::class);
    }
}

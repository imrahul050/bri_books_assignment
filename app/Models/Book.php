<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'book';

    protected $fillable = [
        'title',
        'author',
        'cover_image',
        'price',
        'published_date',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'published_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('_deleted', 0);
        });
    }
}

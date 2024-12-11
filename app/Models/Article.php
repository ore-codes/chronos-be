<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

final class Article extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'title',
        'content',
        'author',
        'source',
        'category',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'author' => $this->author,
            'source' => $this->source,
            'category' => $this->category,
            'published_at' => $this->published_at,
        ];
    }

    public function scoutMetadata(): array
    {
        return [
            'filterableAttributes' => ['author', 'category', 'source', 'published_at'],
        ];
    }

}

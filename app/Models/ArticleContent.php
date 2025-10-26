<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleContent extends Model
{
    use HasFactory;

    protected $table = 'article_contents';

    protected $fillable = [
        'article_id',
        'content',
        'tags',
    ];

    protected $casts = [
        'content' => 'json',
        'tags' => 'json', // Kolom tags harus di-cast sebagai array
    ];

    /**
     * Relasi ke artikel induk.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}

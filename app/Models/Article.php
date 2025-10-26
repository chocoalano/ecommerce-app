<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // --- Relasi ---

    /**
     * Relasi ke konten artikel.
     */
    public function content(): HasOne
    {
        return $this->hasOne(ArticleContent::class);
    }

    // --- Fungsi Pencarian Data yang Baik (Search Scope) ---

    /**
     * Scope untuk melakukan pencarian artikel berdasarkan judul, slug, SEO description,
     * atau tag (melalui tabel article_contents).
     */
    public function scopeSearch(Builder $query, string $keyword): void
    {
        $query->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('slug', 'like', '%' . $keyword . '%')
              ->orWhere('seo_description', 'like', '%' . $keyword . '%')
              // Tambahkan pencarian berdasarkan tag di tabel relasi
              ->orWhereHas('content', function (Builder $q) use ($keyword) {
                  // Pencarian JSON tags menggunakan MySQL JSON_CONTAINS atau LIKE di array JSON
                  // Karena ini adalah Livewire/Eloquent standar, kita asumsikan LIKE pada representasi string JSON
                  $q->where('tags', 'like', '%' . $keyword . '%');
              });
    }

    // --- Fungsi Rekomendasi Artikel yang Baik (Recommendation Scope) ---

    /**
     * Scope untuk merekomendasikan artikel berdasarkan kemiripan tag.
     * Artikel yang direkomendasikan harus dipublikasikan dan berbeda dari artikel saat ini.
     * @param int|null $excludeId ID artikel yang sedang dilihat.
     * @param array $currentTags Array tag dari artikel yang sedang dilihat.
     */
    public function scopeRecommended(Builder $query, array $currentTags = [], ?int $excludeId = null): void
    {
        $query->where('is_published', true)
              ->whereNotNull('published_at')
              ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
              ->when(!empty($currentTags), function (Builder $q) use ($currentTags) {
                  // Rekomendasi berdasarkan kecocokan Tag
                  $q->whereHas('content', function (Builder $subQuery) use ($currentTags) {
                      foreach ($currentTags as $tag) {
                          // Mencari artikel yang memiliki setidaknya satu tag yang sama
                          // Menggunakan LIKE pada string JSON untuk simulasi pencarian dalam array
                          $subQuery->orWhere('tags', 'like', '%' . $tag . '%');
                      }
                  });
              })
              ->orderBy('published_at', 'desc') // Urutkan yang terbaru jika tag tidak spesifik
              ->limit(4); // Batasi hasilnya
    }

    public function featured_image(int $id): ?string
    {
        // 1. Ambil record ArticleContent berdasarkan article_id
        $articleContent = ArticleContent::where('article_id', $id)->first();

        if ($articleContent) {
            // 2. Dekode konten JSON. Pastikan hasilnya adalah array.
            // Jika konten sudah berupa array, biarkan.
            $contentBlocks = is_string($articleContent->content)
                ? json_decode($articleContent->content, true)
                : $articleContent->content;

            // Periksa apakah hasilnya array yang dapat diiterasi
            if (is_array($contentBlocks)) {
                // 3. Iterasi setiap blok konten untuk mencari blok "image"
                foreach ($contentBlocks as $block) {
                    // Pastikan blok adalah array dan memiliki kunci 'type'
                    if (is_array($block) && isset($block['type']) && $block['type'] === 'image') {

                        // 4. Periksa apakah blok 'image' memiliki kunci 'data' dan 'url' di dalamnya
                        if (isset($block['data']['url'])) {
                            // Kembalikan URL gambar pertama yang ditemukan
                            return $block['data']['url'];
                        }
                    }
                }
            }
        }

        // 5. Kembalikan null jika artikel tidak ditemukan, konten tidak valid,
        // atau tidak ada blok gambar yang ditemukan.
        return null;
    }
    public function featured_content(): ?string
    {
        // 1. Ambil konten artikel menggunakan relasi yang sudah ada ($this->content)
        $articleContent = $this->content;

        if ($articleContent) {
            // 2. Dekode konten JSON. Pastikan hasilnya adalah array.
            $contentBlocks = is_string($articleContent->content)
                ? json_decode($articleContent->content, true)
                : $articleContent->content;

            // Periksa apakah hasilnya array yang dapat diiterasi
            if (is_array($contentBlocks)) {
                // 3. Iterasi setiap blok konten untuk mencari blok "paragraph"
                foreach ($contentBlocks as $block) {
                    // Pastikan blok adalah array, memiliki 'type', dan tipenya adalah 'paragraph'
                    if (is_array($block) && isset($block['type']) && $block['type'] === 'paragraph') {

                        // 4. Periksa apakah blok 'paragraph' memiliki kunci 'data' dan 'content' di dalamnya
                        if (isset($block['data']['content'])) {
                            // Kembalikan konten paragraf pertama yang ditemukan
                            return $block['data']['content'];
                        }
                    }
                }
            }
        }

        // 5. Kembalikan null jika tidak ada konten atau tidak ada paragraf.
        return null;
    }
}

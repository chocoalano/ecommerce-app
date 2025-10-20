<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
    protected function handleRecordCreation(array $data): Model
    {
        // Ambil data relasi builder (pageContents) jika ada
        $pageContents = $data['pageContents'] ?? [];
        unset($data['pageContents']);

        // Simpan page utama
        $page = static::getModel()::create($data);

        // Simpan konten builder (hasOne)
        if (!empty($pageContents) && is_array($pageContents)) {
            // Ambil blok pertama (karena hasOne)
            $contentBlock = $pageContents[0] ?? null;
            if ($contentBlock && isset($contentBlock['content'])) {
                $page->pageContents()->create([
                    'content' => $contentBlock['content'],
                    'order' => $contentBlock['order'] ?? 0,
                ]);
            }
        }

        return $page;
    }
}

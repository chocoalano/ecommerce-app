<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- Kolom Utama dan Relasi ---
                Section::make('Informasi Dasar Artikel')
                    ->description('Judul, Slug, dan Konten Utama Artikel.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Artikel')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            // Otomatis membuat slug saat judul diisi
                            ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL ramah SEO. Ubah hanya jika diperlukan.'),
                    ]),

                // --- Pengaturan Publikasi & SEO ---
                Section::make('Pengaturan Publikasi & Meta SEO')
                    ->description('Kontrol status artikel dan tag SEO untuk Google.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Publikasikan Artikel')
                            ->live()
                            ->default(false)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                // Set published_at jika di-publish
                                if ($get('is_published') && ! $get('published_at')) {
                                    $set('published_at', Carbon::now());
                                }
                            }),

                        DatePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->default(Carbon::now())
                            ->visible(fn (Get $get) => $get('is_published')),

                        TextInput::make('seo_title')
                            ->label('SEO Title Tag')
                            ->maxLength(60)
                            ->helperText('Judul singkat yang akan muncul di hasil pencarian Google (Maks. 60 karakter).'),

                        Textarea::make('seo_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->maxLength(160)
                            ->columnSpanFull()
                            ->helperText('Deskripsi singkat artikel untuk hasil pencarian (Maks. 160 karakter).'),
                    ]),

                Section::make('Konten & Tag Artikel')
                    ->description('Isi utama artikel dan tag terkait.')
                    ->relationship('content')
                    ->columnSpanFull()
                    ->schema([
                        Builder::make('content')
                            ->blocks([
                                Block::make('heading')
                                    ->schema([
                                        TextInput::make('content')
                                            ->label('Heading')
                                            ->required(),
                                        Select::make('level')
                                            ->options([
                                                'h1' => 'Heading 1',
                                                'h2' => 'Heading 2',
                                                'h3' => 'Heading 3',
                                                'h4' => 'Heading 4',
                                                'h5' => 'Heading 5',
                                                'h6' => 'Heading 6',
                                            ])
                                            ->required(),
                                    ])
                                    ->columns(2),
                                Block::make('paragraph')
                                    ->schema([
                                        Textarea::make('content')
                                            ->label('Paragraph')
                                            ->required(),
                                    ]),
                                Block::make('image')
                                    ->schema([
                                        FileUpload::make('url')
                                            ->label('Image')
                                            ->disk('public')
                                            ->directory('articles/images')
                                            ->image()
                                            ->required(),
                                        TextInput::make('alt')
                                            ->label('Alt text')
                                            ->required(),
                                    ]),
                            ]),
                            TagsInput::make('tags')
                                ->label('Tags Artikel')
                                ->separator(',')
                                ->helperText('Masukkan tag terkait artikel. Gunakan koma (,) untuk memisahkan beberapa tag.')
                                ->required(),
                    ]),
            ]);
    }
}

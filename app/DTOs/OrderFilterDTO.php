<?php
namespace App\DTOs;

use Illuminate\Http\Request;

final class OrderFilterDTO
{
    public function __construct(
        public ?string $status = null,
        public ?array $status_in = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?string $search = null,
        public int $perPage = 5,
        public int $page = 1,
        public string $sortBy = 'created_at',
        public string $sortDir = 'desc'
    ) {}

    public static function fromRequest(Request $r): self
    {
        return new self(
            status:   $r->filled('status') ? strtolower($r->string('status')) : null,
            status_in:   $r->filled('status_in') ? $r->array('status_in') : null,
            dateFrom: $r->filled('date_from') ? $r->string('date_from') : null,
            dateTo:   $r->filled('date_to') ? $r->string('date_to') : null,
            search:   $r->filled('search') ? $r->string('search') : null,
            perPage:  (int) $r->input('per_page', 5),
            page:     (int) $r->input('page', 1),
            sortBy:   $r->input('sort_by', 'created_at'),
            sortDir:  strtolower($r->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc',
        );
    }
}

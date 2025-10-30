<?php
namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status'    => 'nullable|in:pending,confirmed,processing,shipped,completed,cancelled,PENDING,CONFIRMED,PROCESSING,SHIPPED,COMPLETED,CANCELLED',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'search'    => 'nullable|string|max:100',
            'per_page'  => 'nullable|integer|min:1|max:50',
            'page'      => 'nullable|integer|min:1',
            'sort_by'   => 'nullable|in:created_at,placed_at,grand_total,order_no',
            'sort_dir'  => 'nullable|in:asc,desc',
        ];
    }
}

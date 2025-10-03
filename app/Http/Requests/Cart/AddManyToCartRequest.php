<?php

namespace App\Http\Requests\Cart;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddManyToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'    => ['required_without:session_id', 'nullable', 'integer', 'exists:users,id'],
            'session_id' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'currency'   => ['required', 'string', 'size:3'],

            'applied_promos'   => ['sometimes', 'array'],
            'applied_promos.*' => ['string', 'max:50'],

            'items'              => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', Rule::exists('product_variants','id')->where(fn($q) => $q->where('is_active', 1))],
            'items.*.qty'        => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.meta_json'  => ['sometimes', 'array'],

            // Larangan input hitungan server
            'items.*.unit_price'  => ['prohibited'],
            'items.*.row_total'   => ['prohibited'],
            'subtotal_amount'     => ['prohibited'],
            'discount_amount'     => ['prohibited'],
            'shipping_amount'     => ['prohibited'],
            'tax_amount'          => ['prohibited'],
            'grand_total'         => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currency') && is_string($this->currency)) {
            $this->merge(['currency' => strtoupper($this->currency)]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // Cek stok per item bila kolom stock tersedia
            if (!is_array($this->items ?? null)) {
                return;
            }

            $variantIds = collect($this->items)->pluck('variant_id')->filter()->unique()->all();
            $variants   = ProductVariant::query()->select('id','stock')->whereIn('id', $variantIds)->get()->keyBy('id');

            foreach (($this->items ?? []) as $i => $item) {
                $vr = $variants[$item['variant_id']] ?? null;
                if ($vr && !is_null($vr->stock)) {
                    if ($vr->stock < (int) ($item['qty'] ?? 0)) {
                        $v->errors()->add("items.$i.qty", 'Stok tidak mencukupi.');
                    }
                }
            }
        });
    }
}

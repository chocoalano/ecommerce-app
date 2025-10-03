<?php

namespace App\Http\Requests\Cart;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
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
            // Cart metadata
            'currency'         => ['required', 'string', 'size:3'],
            'applied_promos'   => ['sometimes', 'array'],
            'applied_promos.*' => ['string', 'max:50'],

            // Item
            'variant_id'       => ['required', 'integer', Rule::exists('product_variants', 'id')->where(function ($q) {
                // Jika ada kolom is_active di variants, aktifkan filter ini
                $q->where('is_active', 1);
            })],
            'qty'              => ['required', 'integer', 'min:1', 'max:1000'],

            // Dilarang diisi client (server yang hitung)
            'unit_price'      => ['prohibited'],
            'row_total'       => ['prohibited'],
            'subtotal_amount' => ['prohibited'],
            'discount_amount' => ['prohibited'],
            'shipping_amount' => ['prohibited'],
            'tax_amount'      => ['prohibited'],
            'grand_total'     => ['prohibited'],

            // Opsional metadata item
            'meta_json'       => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required_without'    => 'user_id atau session_id wajib diisi salah satu.',
            'session_id.required_without' => 'user_id atau session_id wajib diisi salah satu.',
            'currency.size'               => 'Kode mata uang harus 3 huruf (mis: IDR, USD).',
            'unit_price.prohibited'       => 'Harga item dihitung oleh sistem.',
            'row_total.prohibited'        => 'Row total dihitung oleh sistem.',
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
            // Opsional cek stok jika kolom `stock` tersedia
            $variant = ProductVariant::query()->select('id', 'stock')->find($this->variant_id);
            if ($variant && !is_null($variant->stock)) {
                if ($variant->stock < (int) $this->qty) {
                    $v->errors()->add('qty', 'Stok tidak mencukupi.');
                }
            }
        });
    }
}

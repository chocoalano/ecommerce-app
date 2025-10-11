<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\Inventory\Inventory;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Validation\ValidationException;

class ManageStockMovements extends ManageRecords
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    // Normalisasi tipe
                    $data['type'] = strtoupper($data['type']);

                    // Validasi qty
                    if (!is_numeric($data['qty'] ?? null)) {
                        throw ValidationException::withMessages(['qty' => 'Qty harus numerik.']);
                    }
                    $qty = (int) $data['qty'];

                    if ($data['type'] !== 'ADJUST' && $qty <= 0) {
                        throw ValidationException::withMessages(['qty' => 'Qty harus > 0 untuk tipe IN/OUT/RESERVE/RELEASE.']);
                    }
                    if ($data['type'] === 'ADJUST' && $qty === 0) {
                        throw ValidationException::withMessages(['qty' => 'Qty ADJUST tidak boleh 0.']);
                    }

                    // Lokasi wajib untuk selain ADJUST
                    if ($data['type'] !== 'ADJUST' && empty($data['location_id'])) {
                        throw ValidationException::withMessages(['location_id' => 'Lokasi wajib untuk tipe selain ADJUST.']);
                    }

                    // Cek inventory baris terkait (boleh null kalau ADJUST tanpa lokasi -> tidak sync ke inventory tertentu)
                    $inventory = null;
                    if (!empty($data['location_id'])) {
                        $inventory = Inventory::query()
                            ->firstOrNew([
                                'product_id'  => $data['product_id'],
                                'location_id' => $data['location_id'],
                            ]);
                        if (!$inventory->exists) {
                            // Untuk IN/ADJUST boleh membuat baris baru; untuk OUT/RESERVE/RELEASE harus sudah ada
                            if (in_array($data['type'], ['OUT','RESERVE','RELEASE'])) {
                                throw ValidationException::withMessages([
                                    'location_id' => 'Belum ada stok di lokasi ini untuk produk tersebut.',
                                ]);
                            }
                            // init default
                            $inventory->qty_on_hand = $inventory->qty_on_hand ?? 0;
                            $inventory->qty_reserved = $inventory->qty_reserved ?? 0;
                            $inventory->safety_stock = $inventory->safety_stock ?? 0;
                        }
                    }

                    // Validasi ketersediaan saat OUT/RESERVE/RELEASE/ADJUST(-)
                    if ($inventory) {
                        $available = ($inventory->qty_on_hand ?? 0) - ($inventory->qty_reserved ?? 0);

                        if ($data['type'] === 'OUT' && $qty > $available) {
                            throw ValidationException::withMessages(['qty' => "Qty OUT melebihi available ($available)."]);
                        }

                        if ($data['type'] === 'RESERVE' && $qty > $available) {
                            throw ValidationException::withMessages(['qty' => "Qty RESERVE melebihi available ($available)."]);
                        }

                        if ($data['type'] === 'RELEASE' && $qty > ($inventory->qty_reserved ?? 0)) {
                            throw ValidationException::withMessages(['qty' => "Qty RELEASE melebihi reserved ({$inventory->qty_reserved})."]);
                        }

                        if ($data['type'] === 'ADJUST' && $qty < 0) {
                            $newOnHand = ($inventory->qty_on_hand ?? 0) + $qty;
                            if ($newOnHand < ($inventory->qty_reserved ?? 0)) {
                                throw ValidationException::withMessages(['qty' => 'ADJUST negatif menyebabkan on hand < reserved.']);
                            }
                        }
                    }

                    return $data;
                })
        ];
    }
}

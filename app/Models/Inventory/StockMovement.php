<?php

namespace App\Models\Inventory;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockMovement extends Model
{
    /** @use HasFactory<\Database\Factories\Inventory\StockMovementFactory> */
    use HasFactory;
    public const TYPE_IN       = 'IN';
    public const TYPE_OUT      = 'OUT';
    public const TYPE_RESERVE  = 'RESERVE';
    public const TYPE_RELEASE  = 'RELEASE';
    public const TYPE_ADJUST   = 'ADJUST';

    protected $fillable = [
        'product_id', 'location_id', 'type', 'qty',
        'ref_type', 'ref_id', 'note',
    ];

    protected $casts = [
        'product_id'  => 'integer',
        'location_id' => 'integer',
        'qty'         => 'integer',
    ];

    /** Relationships */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    /** Hooks: apply movement to inventories & refresh product stock */
    protected static function booted(): void
    {
        static::created(function (StockMovement $m) {
            // Jika movement tidak memiliki lokasi, lewati penyesuaian fisik (tetap tercatat sebagai log).
            if (is_null($m->location_id)) {
                static::refreshProductStockSummary($m->product_id);
                return;
            }

            DB::transaction(function () use ($m) {
                // Lock baris inventory terkait
                $inv = Inventory::query()
                    ->where('product_id', $m->product_id)
                    ->where('location_id', $m->location_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inv) {
                    $inv = new Inventory([
                        'product_id'   => $m->product_id,
                        'location_id'  => $m->location_id,
                        'qty_on_hand'  => 0,
                        'qty_reserved' => 0,
                        'safety_stock' => 0,
                    ]);
                }

                // Normalisasi qty berdasarkan tipe
                $absQty = abs((int)$m->qty);
                $deltaOnHand = 0;
                $deltaReserved = 0;

                switch ($m->type) {
                    case self::TYPE_IN:
                        $deltaOnHand = +$absQty;
                        break;
                    case self::TYPE_OUT:
                        $deltaOnHand = -$absQty;
                        break;
                    case self::TYPE_RESERVE:
                        $deltaReserved = +$absQty;
                        break;
                    case self::TYPE_RELEASE:
                        $deltaReserved = -$absQty;
                        break;
                    case self::TYPE_ADJUST:
                        // ADJUST menerima tanda asli pada qty (positif/negatif)
                        $deltaOnHand = (int)$m->qty;
                        break;
                }

                $inv->qty_on_hand  = max(0, (int)$inv->qty_on_hand  + $deltaOnHand);
                $inv->qty_reserved = max(0, (int)$inv->qty_reserved + $deltaReserved);
                $inv->save();

                // Refresh ringkasan stok pada products.stock (jika Anda memakainya sebagai summary)
                static::refreshProductStockSummary($m->product_id);
            });
        });
    }

    protected static function refreshProductStockSummary(int $productId): void
    {
        // Hitung total available = SUM(qty_on_hand - qty_reserved) semua lokasi
        $available = Inventory::forProduct($productId)
            ->selectRaw('COALESCE(SUM(qty_on_hand - qty_reserved), 0) as avail')
            ->value('avail');

        // Update kolom 'stock' di products agar sinkron (opsional tapi umum dipakai)
        Product::whereKey($productId)->update(['stock' => (int)$available]);
    }

}

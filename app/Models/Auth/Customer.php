<?php

namespace App\Models\Auth;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'code', 'parent_id', 'sponsor_id', 'position', 'name', 'full_name', 'email', 'password', 'phone', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'password' => 'hashed',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Customer $customer) {
            $prefix = now()->format('Ymd');
                $pad = 4; // jumlah digit urut, ubah kalau perlu

                $maxCode = DB::table($customer->getTable())
                    ->where('code', 'like', $prefix . '%')
                    ->max('code');

                if ($maxCode) {
                    $lastSeq = (int) substr($maxCode, strlen($prefix));
                    $nextSeq = $lastSeq + 1;
                } else {
                    $nextSeq = 1;
                }

                $customer->code = $prefix . str_pad($nextSeq, $pad, '0', STR_PAD_LEFT);
        });
    }

    // --- Relationships ---
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    // --- MLM Relationships ---
    /**
     * Parent (placement) relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Children (placement) relation
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Sponsor relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function sponsor()
    {
        return $this->belongsTo(self::class, 'sponsor_id');
    }

    /**
     * Sponsorees relation
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sponsorees()
    {
        return $this->hasMany(self::class, 'sponsor_id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }

    public function carts()
    {
        return $this->hasMany(\App\Models\CartProduct\Cart::class);
    }

    public function activeCart()
    {
        return $this->hasOne(\App\Models\CartProduct\Cart::class)->latest();
    }

    public function cartItems()
    {
        return $this->hasManyThrough(
            \App\Models\CartProduct\CartItem::class,
            \App\Models\CartProduct\Cart::class
        );
    }

    // --- Cart Helper Methods ---

    /**
     * Get or create active cart for customer
     */
    public function getOrCreateCart(): \App\Models\CartProduct\Cart
    {
        $cart = $this->activeCart;

        if (!$cart) {
            $cart = $this->carts()->create([
                'currency' => 'IDR',
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'tax_amount' => 0,
                'grand_total' => 0,
            ]);
        }

        return $cart;
    }

    /**
     * Get ancestors from closure table (ordered by depth asc, excluding self)
     */
    public function ancestors()
    {
        return $this->belongsToMany(self::class, 'customer_networks', 'descendant_id', 'ancestor_id')
            ->wherePivot('depth', '>', 0)
            ->orderBy('customer_networks.depth', 'asc');
    }

    /**
     * Get descendants from closure table (excluding self)
     */
    /**
     * Ambil semua turunan (downline) beserta jaringan di bawahnya secara rekursif.
     * Menggunakan closure table untuk mengambil seluruh subtree.
     */
    public function descendants()
    {
        return $this->belongsToMany(self::class, 'customer_networks', 'ancestor_id', 'descendant_id')
            ->wherePivot('depth', '>', 0)
            ->orderBy('customer_networks.depth', 'asc')
            ->with(['children.descendants']);
    }

    /**
     * Helper: insert this node into closure table when creating under a parent
     * Call within a DB transaction.
     */
    public function insertIntoClosureTable(?self $parent = null): void
    {
        // Insert self->self
        DB::table('customer_networks')->insert([
            'ancestor_id' => $this->id,
            'descendant_id' => $this->id,
            'depth' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($parent) {
            // copy all ancestor relations from parent and append new relations
            // ensure we only use ancestor_ids that actually exist in customers table
            $ancestors = DB::table('customer_networks as cn')
                ->join('customers as c', 'cn.ancestor_id', '=', 'c.id')
                ->where('cn.descendant_id', $parent->id)
                ->select('cn.ancestor_id', 'cn.depth')
                ->get();

            $inserts = [];
            foreach ($ancestors as $a) {
                $inserts[] = [
                    'ancestor_id' => $a->ancestor_id,
                    'descendant_id' => $this->id,
                    'depth' => $a->depth + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // also add parent -> self (depth 1) if not present
            if (!collect($inserts)->contains(fn($row) => $row['ancestor_id'] === $parent->id && $row['descendant_id'] === $this->id)) {
                $inserts[] = [
                    'ancestor_id' => $parent->id,
                    'descendant_id' => $this->id,
                    'depth' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($inserts)) {
                DB::table('customer_networks')->insert($inserts);
            }
        }
    }

    /**
     * Move this node (and its subtree) to a new parent.
     * Handles closure table updates (delete old ancestor links, insert new ones).
     * Use inside a DB::transaction when calling from outside.
     */
    public function moveToParent(?int $newParentId): void
    {
        $nodeId = $this->id;

        DB::transaction(function () use ($nodeId, $newParentId) {
            // Descendants of node (including node)
            // join customers to ensure descendant ids still exist
            $descendants = DB::table('customer_networks as cn')
                ->join('customers as c', 'cn.descendant_id', '=', 'c.id')
                ->where('cn.ancestor_id', $nodeId)
                ->select('cn.descendant_id', 'cn.depth')
                ->get();

            $descendantIds = $descendants->pluck('descendant_id')->toArray();

            if (empty($descendantIds)) {
                return;
            }

            // Old ancestors of node that are NOT in the subtree
            $oldAncestors = DB::table('customer_networks')
                ->where('descendant_id', $nodeId)
                ->whereNotIn('ancestor_id', $descendantIds)
                ->pluck('ancestor_id')
                ->toArray();

            if (!empty($oldAncestors)) {
                DB::table('customer_networks')
                    ->whereIn('ancestor_id', $oldAncestors)
                    ->whereIn('descendant_id', $descendantIds)
                    ->delete();
            }

            // If new parent is null, we only needed to remove old ancestor links.
            if (is_null($newParentId)) {
                return;
            }

            // Ancestors of new parent (including new parent)
            // ensure ancestors reference existing customers
            $newParentAncestors = DB::table('customer_networks as cn')
                ->join('customers as c', 'cn.ancestor_id', '=', 'c.id')
                ->where('cn.descendant_id', $newParentId)
                ->select('cn.ancestor_id', 'cn.depth')
                ->get();

            // Prepare mapping of node->descendant depths
            $nodeToDesc = [];
            foreach ($descendants as $d) {
                $nodeToDesc[$d->descendant_id] = $d->depth; // depth from node to descendant
            }

            $inserts = [];
            foreach ($newParentAncestors as $a) {
                foreach ($nodeToDesc as $descId => $depthNodeToDesc) {
                    $inserts[] = [
                        'ancestor_id' => $a->ancestor_id,
                        'descendant_id' => $descId,
                        'depth' => $a->depth + 1 + $depthNodeToDesc,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($inserts)) {
                // Use insert ignoring duplicates if DB supports upsert; fallback to insert and ignore exceptions
                DB::table('customer_networks')->insert($inserts);
            }
        });
    }

    /**
     * Get total items in active cart
     */
    public function getCartItemsCountAttribute(): int
    {
        return $this->activeCart?->getTotalQtyAttribute() ?? 0;
    }

    /**
     * Get formatted cart total
     */
    public function getFormattedCartTotalAttribute(): string
    {
        $total = $this->activeCart?->grand_total ?? 0;
        return 'Rp ' . number_format((float) $total, 0, ',', '.');
    }

    public function activeMembers()
    {
        return $this->descendants()->where('is_active', true);
    }

    public function inactiveMembers()
    {
        return $this->descendants()->where('is_active', false);
    }

    public function subscribers()
    {
        return $this->hasOne(NewsletterSubscriber::class, 'email', 'email');
    }
}

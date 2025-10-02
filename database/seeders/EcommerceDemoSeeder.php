<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\Address;            // hapus jika tidak ada
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use App\Models\PromotionGift;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipment;

class EcommerceDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================
        // 0) Users & Admin
        // ==========================
        $admin = User::factory()
            ->indoPhone()
            ->create([
                'full_name' => 'Admin SAS',
                'email' => 'admin@example.com',
                'is_active' => 1,
                'email_verified_at' => now(),
            ]);

        // Roles
        $superadminRole = Role::firstOrCreate(
            ['name' => 'Superadmin'],
            ['code' => 'Role-' . Str::uuid()]
        );
        $buyerRole = Role::firstOrCreate(
            ['name' => 'Buyer'],
            ['code' => 'Role-' . Str::uuid()]
        );

        // Admin => Superadmin
        UserRole::updateOrCreate(
            ['user_id' => $admin->id, 'role_id' => $superadminRole->id],
            []
        );

        // Customers
        $customers = User::factory()->count(10)->indoPhone()->create();
        $inactive = User::factory()->count(2)->inactive()->create();

        // ==========================
        // 0b) Addresses (opsional)
        // ==========================
        if (class_exists(Address::class)) {
            // 2 alamat admin
            Address::factory()->count(2)->create(['user_id' => $admin->id]);

            // 1 alamat default untuk tiap customer
            foreach ($customers as $u) {
                Address::factory()->create([
                    'user_id' => $u->id,
                    'is_default' => 1,
                ]);
            }
        }

        // ==========================
        // 1) Categories, Products, Variants
        // ==========================
        $cats = \Database\Factories\CategoryFactory::new()->count(6)->create();

        $products = collect();
        foreach ($cats as $cat) {
            $batch = \Database\Factories\ProductFactory::new()->count(8)->make();
            foreach ($batch as $p) {
                $product = new Product($p->toArray());
                $product->save();
                $product->productCategories()->attach($cat->id);
                $product->productMedia()->createMany(
                    \Database\Factories\ProductMediaFactory::new()->count(1)->make()->toArray()
                );
                $products->push($product);

                // 1–3 variants per product
                for ($i = 0; $i < rand(1, 3); $i++) {
                    $v = \Database\Factories\ProductVariantFactory::new()->make();
                    $variant = new ProductVariant($v->toArray());
                    $variant->product_id = $product->id;
                    $variant->save();
                    $variant->media()->createMany(
                        \Database\Factories\ProductVariantMediaFactory::new()->count(5)->make()->toArray()
                    );
                }
            }
        }
        $variants = ProductVariant::query()->get();

        // ==========================
        // 2) Promotions & Details
        // ==========================
        $promotions = \Database\Factories\PromotionFactory::new()->count(8)->create();

        foreach ($promotions as $promo) {
            // Promotion products
            for ($i = 0; $i < rand(2, 5); $i++) {
                $targetVariant = $variants->random();
                $pp = \Database\Factories\PromotionProductFactory::new()->make([
                    'promotion_id' => $promo->id,
                    'product_id' => $targetVariant->product_id,
                    'variant_id' => $targetVariant->id,
                ]);
                (new PromotionProduct($pp->toArray()))->save();
            }

            // Gift with purchase
            if (in_array($promo->type, ['GIFT_WITH_PURCHASE'])) {
                for ($i = 0; $i < rand(1, 2); $i++) {
                    $giftVariant = $variants->random();
                    $pg = \Database\Factories\PromotionGiftFactory::new()->make([
                        'promotion_id' => $promo->id,
                        'gift_variant_id' => $giftVariant->id,
                    ]);
                    (new PromotionGift($pg->toArray()))->save();
                }
            }
        }

        // ==========================
        // 3) Vouchers (sebagian terkait promo)
        // ==========================
        $vouchers = collect();
        for ($i = 0; $i < 12; $i++) {
            $promo = $this->pick($promotions->all());
            $vf = \Database\Factories\VoucherFactory::new()->make([
                'promotion_id' => optional($promo)->id,
            ]);
            $vouchers->push(Voucher::create($vf->toArray()));
        }

        // ==========================
        // 4) Orders, Items, Payments, Shipments, Voucher Redemptions
        // ==========================
        $addressesByUser = collect();
        if (class_exists(Address::class)) {
            $addressesByUser = Address::query()->get()->groupBy('user_id');
        }

        // Buat 30 order acak milik admin+customers
        $buyers = collect([$admin])->merge($customers);
        for ($i = 0; $i < 30; $i++) {
            $user = $buyers->random();
            $addr = $addressesByUser->has($user->id)
                ? optional($addressesByUser[$user->id])->random()
                : null;

            // Order
            $order = new Order(\Database\Factories\OrderFactory::new()->make()->toArray());
            $order->user_id = $user->id;
            if ($addr) {
                $order->billing_address_id = $addr->id;
                $order->shipping_address_id = $addr->id;
            }
            // status & payment_status sudah dari factory, bisa disesuaikan jika perlu
            $order->save();

            // Items
            $itemCount = rand(1, 4);
            $discount = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                /** @var \App\Models\ProductVariant $variant */
                $variant = $variants->random();

                // Buat data dasar dari factory (tanpa paksa unit_price dulu)
                $data = \Database\Factories\OrderItemFactory::new()->make([
                    'order_id' => $order->id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'name' => $variant->name ?? 'Item',
                    'sku' => $variant->variant_sku ?? strtoupper(fake()->bothify('SKU-########')),
                ])->toArray();

                // Pastikan unit_price selalu terisi (pakai base_price varian, fallback ke angka acak)
                $unitPrice = (float) ($variant->base_price ?? ($data['unit_price'] ?? 0));
                if ($unitPrice <= 0) {
                    $unitPrice = fake()->randomFloat(2, 10_000, 200_000); // fallback aman
                }

                // Pastikan qty & discount_amount valid
                $qty = max(1, (int) ($data['qty'] ?? 1));
                $lineTotal = $unitPrice * $qty;

                $discountAmount = (float) ($data['discount_amount'] ?? 0);
                // batasi diskon agar tidak melebihi 90% line
                $discountAmount = min($discountAmount, $lineTotal * 0.9);

                // Set field akhir sesuai model OrderItem
                $data['unit_price'] = $unitPrice;
                $data['qty'] = $qty;
                $data['discount_amount'] = $discountAmount;
                $data['row_total'] = max(0, $lineTotal - $discountAmount);

                OrderItem::create($data);

                // akumulasi untuk rekap order
                $discount += $discountAmount;
            }


            // Update total order
            $order->grand_total = max(0, $discount + ($order->shipping_cost ?? 0));
            $order->save();

            $paymentMethod = PaymentMethod::create([
                'code' => 'PM-' . Str::uuid(),
                'name' => fake()->randomElement([
                    'Bank'.fake()->randomElement(['Transfer', 'VA']).'BCA Virtual Account',
                    fake()->randomElement(['Transfer', 'VA']).'GoPay',
                    fake()->randomElement(['Transfer', 'VA']).'OVO',
                    fake()->randomElement(['Transfer', 'VA']).'Credit Card Visa',
                    fake()->randomElement(['Transfer', 'VA']).'DANA',
                ]),
                'is_active' => fake()->boolean(90),
            ]);


            // Payment (sinkron dengan payment_status)
            $payment = new Payment(\Database\Factories\PaymentFactory::new()->make([
                'order_id' => $order->id,
                'method_id' => $paymentMethod->id,
                'amount' => $order->grand_total,
                'status' => $order->status === 'PAID' ? 'CAPTURED' : ($this->chance(10) ? 'FAILED' : 'CANCELED'),
            ])->toArray());
            $payment->save();

            // Shipment jika status memungkinkan
            if (in_array($order->status, ['processing', 'shipped', 'completed'])) {
                $shipment = new Shipment(\Database\Factories\ShipmentFactory::new()->make([
                    'order_id' => $order->id,
                ])->toArray());
                $shipment->save();
            }

            // Voucher redemption (acak)
            if ($vouchers->isNotEmpty() && $this->chance(35)) {
                VoucherRedemption::create([
                    'voucher_id' => $vouchers->random()->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'redeemed_at' => now(),
                ]);
            }
        }
    }

    // ===== Helpers =====

    private function chance(int $percent): bool
    {
        return random_int(1, 100) <= $percent;
    }

    private function pick(array $arr)
    {
        if (empty($arr))
            return null;
        return $arr[array_rand($arr)];
    }
}

<?php

namespace Database\Seeders;

use App\Models\OrderProduct\Order;
use App\Models\OrderProduct\OrderReturn;
use App\Models\OrderProduct\Payment;
use App\Models\OrderProduct\PaymentTransaction;
use App\Models\OrderProduct\Refund;
use App\Models\OrderProduct\ReturnItem;
use App\Models\OrderProduct\Shipment;
use App\Models\OrderProduct\ShipmentItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat order lengkap dengan 3 item, alamat, dan hitung total
        $order = Order::factory()->forCustomer()->withAddresses()->withItems(3)->create();

        // Bayar & catat transaksi
        $payment = Payment::factory()->forOrder($order, $order->grand_total)->captured()->create();
        PaymentTransaction::factory()->forPayment($payment, Payment::ST_CAPTURED, $order->grand_total ?? 0)->create();

        // Shipment & shipment items
        $shipment = Shipment::factory()->for($order)->shipped()->create();
        foreach ($order->items as $oi) {
            ShipmentItem::factory()->forShipment($shipment, $oi, $oi->qty)->create();
        }

        // Retur sebagian & refund parsial
        $ret = OrderReturn::factory()->for($order)->approved()->create();
        ReturnItem::factory()->forReturn($ret, $order->items->first(), 1)->create();
        Refund::factory()->forOrder($order, 50000, $payment)->create();
    }
}

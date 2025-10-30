<?php
namespace App\Repositories\Orders;

use App\Contracts\OrderRepository;
use App\DTOs\OrderFilterDTO;
use App\Models\OrderProduct\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepository
{
    public function paginateForCustomer(int $customerId, OrderFilterDTO $f): LengthAwarePaginator
    {
        $q = Order::query()
            ->where('customer_id', $customerId)
            ->with(['items.product', 'shippingAddress'])
            ->orderBy($f->sortBy, $f->sortDir);

        if ($f->status) {
            $q->whereRaw('LOWER(status) = ?', [strtolower($f->status)]);
        }
        if ($f->status_in && is_array($f->status_in) && count($f->status_in) > 0) {
            $q->whereIn('status', $f->status_in);
        }
        if ($f->dateFrom) {
            $q->whereDate('created_at', '>=', $f->dateFrom);
        }
        if ($f->dateTo) {
            $q->whereDate('created_at', '<=', $f->dateTo);
        }
        if ($f->search) {
            $q->where('order_no', 'like', '%' . $f->search . '%');
        }
        // penting: withQueryString agar prev/next tetap bawa filter
        return $q->paginate($f->perPage, ['*'], 'page', $f->page)->withQueryString();
    }

    public function findByIdForCustomer(int $orderId, int $customerId): ?Order
    {
        return Order::query()
            ->where('id', $orderId)
            ->where('customer_id', $customerId)
            ->with(['items.product', 'shippingAddress'])
            ->first();
    }

    public function cancel(Order $order): bool
    {
        // contoh simple rule: hanya boleh cancel PENDING
        if (strtoupper($order->status) !== 'PENDING') {
            return false;
        }
        $order->status = 'CANCELLED';
        return (bool) $order->save();
    }
}

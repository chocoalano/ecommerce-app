<?php
namespace App\Services\Orders;

use App\Contracts\OrderRepository;
use App\DTOs\OrderFilterDTO;
use App\Http\Resources\OrderResource;
use App\Models\Auth\Customer;
use App\Models\OrderProduct\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(private readonly OrderRepository $orders) {}

    public function listCustomerOrders(Customer $customer, OrderFilterDTO $filters): LengthAwarePaginator
    {
        return $this->orders->paginateForCustomer($customer->id, $filters);
    }

    public function showCustomerOrder(Customer $customer, int $orderId): ?Order
    {
        return $this->orders->findByIdForCustomer($orderId, $customer->id);
    }

    public function cancelCustomerOrder(Customer $customer, int $orderId): bool
    {
        $order = $this->orders->findByIdForCustomer($orderId, $customer->id);
        if (!$order) return false;
        return $this->orders->cancel($order);
    }

    /**
     * Helper: bentuk response JSON sesuai contoh kamu.
     */
    public function asJsonResponse(LengthAwarePaginator $paginator)
    {
        return response()->json([
            'success'    => true,
            'orders'     => OrderResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
            ],
        ]);
    }
}

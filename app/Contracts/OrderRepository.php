<?php
namespace App\Contracts;

use App\DTOs\OrderFilterDTO;
use App\Models\OrderProduct\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepository
{
    public function paginateForCustomer(int $customerId, OrderFilterDTO $filters): LengthAwarePaginator;

    public function findByIdForCustomer(int $orderId, int $customerId): ?Order;

    public function cancel(Order $order): bool;
}

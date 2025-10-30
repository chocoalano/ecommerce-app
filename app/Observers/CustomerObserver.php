<?php

namespace App\Observers;

use App\Models\Auth\Customer;
use Illuminate\Support\Facades\DB;
use App\Jobs\SyncCustomerNetwork;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        // Dispatch job after DB commit to perform closure sync asynchronously
        DB::afterCommit(function () use ($customer) {
            SyncCustomerNetwork::dispatch('created', $customer->id);
        });
    }

    /**
     * Handle the Customer "updated" event.
     * If parent_id changed, move subtree appropriately.
     */
    public function updated(Customer $customer): void
    {
        // We only care if parent_id changed
        if (!$customer->wasChanged('parent_id')) {
            return;
        }

        $oldParent = $customer->getOriginal('parent_id');
        $newParent = $customer->parent_id;

        DB::afterCommit(function () use ($customer, $oldParent, $newParent) {
            SyncCustomerNetwork::dispatch('updated', $customer->id, $oldParent, $newParent);
        });
    }

    /**
     * Handle the Customer "deleted" event.
     * Cleanup closure rows where ancestor or descendant is this customer.
     */
    public function deleted(Customer $customer): void
    {
        DB::afterCommit(function () use ($customer) {
            SyncCustomerNetwork::dispatch('deleted', $customer->id);
        });
    }
}

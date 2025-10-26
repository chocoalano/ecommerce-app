<?php

namespace App\Jobs;

use App\Models\Auth\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncCustomerNetwork implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $action;
    public int $customerId;
    public $oldParentId;
    public $newParentId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $action, int $customerId, $oldParentId = null, $newParentId = null)
    {
        $this->action = $action;
        $this->customerId = $customerId;
        $this->oldParentId = $oldParentId;
        $this->newParentId = $newParentId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        switch ($this->action) {
            case 'created':
                $this->handleCreated();
                break;
            case 'updated':
                $this->handleUpdated();
                break;
            case 'deleted':
                $this->handleDeleted();
                break;
        }
    }

    protected function handleCreated(): void
    {
        $customer = Customer::find($this->customerId);
        if (!$customer) {
            return;
        }

        // Use the model helper which already uses transactions
        $parent = $customer->parent; // may be null
        $customer->insertIntoClosureTable($parent);
    }

    protected function handleUpdated(): void
    {
        $customer = Customer::find($this->customerId);
        if (!$customer) {
            return;
        }

        // Move subtree to new parent
        $customer->moveToParent($this->newParentId);
    }

    protected function handleDeleted(): void
    {
        // Customer may have been deleted already; just clean closure rows
        DB::table('customer_networks')
            ->where('ancestor_id', $this->customerId)
            ->orWhere('descendant_id', $this->customerId)
            ->delete();
    }
}

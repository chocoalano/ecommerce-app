<?php

namespace App\Repositories\Auth;

use App\Models\Auth\Customer;

class CustomerRepository
{
    public function all()
    {
        return Customer::all();
    }

    public function find($id)
    {
        return Customer::find($id);
    }

    public function create(array $data)
    {
        return Customer::create($data);
    }

    public function update($id, array $data)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($data);
        return $customer;
    }

    public function delete($id)
    {
        return Customer::destroy($id);
    }

    public function addresses($id)
    {
        return Customer::findOrFail($id)->addresses;
    }
}

<?php

namespace App\Repositories\Auth;

use App\Models\Auth\CustomerAddress;

class AddressRepository
{
    public function all()
    {
        return CustomerAddress::all();
    }

    public function find($id)
    {
        return CustomerAddress::find($id);
    }

    public function create(array $data)
    {
        return CustomerAddress::create($data);
    }

    public function update($id, array $data)
    {
        $address = CustomerAddress::findOrFail($id);
        $address->update($data);
        return $address;
    }

    public function delete($id)
    {
        return CustomerAddress::destroy($id);
    }
}

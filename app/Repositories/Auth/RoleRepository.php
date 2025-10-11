<?php

namespace App\Repositories\Auth;

use App\Models\Auth\Role;

class RoleRepository
{
    public function all()
    {
        return Role::all();
    }

    public function find($id)
    {
        return Role::find($id);
    }

    public function create(array $data)
    {
        return Role::create($data);
    }

    public function update($id, array $data)
    {
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role;
    }

    public function delete($id)
    {
        return Role::destroy($id);
    }
}

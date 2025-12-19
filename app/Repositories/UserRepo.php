<?php

namespace App\Repositories;

use App\Filters\UserFilter;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRepo
{
    /**
     * Get All Categories With Filter Value
     */
    function getAll(array $data, UserFilter $filters) 
    {
        $users = User::filter($filters);
        if (isset($data['page']) && isset($data['per_page'])) {
            $users = $users->paginate($data['per_page']);
        } else {
            $users = $users->get();
        }
        return $users;
    }
    
    /**
     * Get All Permissions.
     */
    public function all()
    {
        return User::all();
    }

    /**
     * Get Permissions With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->search ?? '';
        $users = User::where(function($query) use ($search) {
            if($search) {
                $query->where('name', 'LIKE', "%$search%");
            }
        })
        ->paginate($perPage);
        return $users;
    }

    /**
     * Get Permission with id
     */
    public function get($id)
    {
        return User::findOrFail($id);
    }

    /**
     * Create Permission
     */
    public function create(array $data)
    {
        $data['created_by'] = auth()->user->id ?? null;

        $user = User::create($data);

        if (isset($data['roles'])) {
            $roles = Role::whereIn('id', $data['roles'])->get();
            $user->assignRole($roles);
        }
        
        return $user;
    }

    /**
     * Update Permission
     */
    public function update($id, array $data)
    {
        $user = User::findOrFail($id);
        $data['updated_by'] = auth()->user->id ?? null;
        $user->update($data);

        if (isset($data['roles'])) {
            $roles = Role::whereIn('id', $data['roles'])->get();
            $user->syncRoles($roles);
        }

        return $user;
    }

    /**
     * Delete Permission
     */
    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids) {
        $users = User::whereIn('id', $ids)->get();
        foreach ($users as $key => $user) {
            $user->delete();
        }
    }
}

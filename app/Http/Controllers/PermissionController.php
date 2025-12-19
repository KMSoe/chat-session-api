<?php

namespace App\Http\Controllers;

use App\Filters\PermissionFilter;
use App\Http\Requests\PermissionFormRequest;
use App\Http\Resources\PermissionCollection;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\PermissionRepo;

class PermissionController extends Controller
{
    protected $permissionRepo;

    public function __construct(PermissionRepo $permissionRepo)
    {
        $this->permissionRepo = $permissionRepo;
    }

    function getAll(Request $request, PermissionFilter $filters)  
    {
        $permissions = $this->permissionRepo->getAll($request->all(), $filters);
        return new PermissionCollection($permissions);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (filter_var($request->query('all'), FILTER_VALIDATE_BOOLEAN)) {
            $permissions = $this->permissionRepo->all();
        } else {
            $permissions = $this->permissionRepo->paginate($request->all());
        }
        return new PermissionCollection($permissions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermissionFormRequest $request)
    {
        $request->validated();
        try {
            $permission = $this->permissionRepo->create($request->all());
            return new PermissionResource($permission);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = $this->permissionRepo->get($id);
        return new PermissionResource($permission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissionFormRequest $request, $id)
    {
        $request->validated();
        try {
            $permission = $this->permissionRepo->update($id,  $request->all());
            return new PermissionResource($permission);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->permissionRepo->delete($id);
            return response()->json(['success' => true], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    /**
     * Change Status of Role and Permission
     */
    public function changeStatus(Request $request, $id)
    {
        $permission = $this->permissionRepo->updateStatus($id,  $request->all());
        return new PermissionResource($permission);
    }
}

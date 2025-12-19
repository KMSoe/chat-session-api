<?php
namespace App\Http\Controllers;

use App\Enums\TableView;
use App\Filters\RoleFilter;
use App\Http\Requests\RoleFormRequest;
use App\Http\Resources\MyRoleResource;
use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleDetailResource;
use App\Http\Services\RoleService;
use App\Models\CustomRole;
use App\Repositories\RoleRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    protected $roleRepo;
    protected RoleService $roleService;

    public function __construct(RoleRepo $roleRepo, RoleService $roleService)
    {
        $this->roleRepo    = $roleRepo;
        $this->roleService = $roleService;
    }

    public function getAll(Request $request, RoleFilter $filters)
    {
        $roles = $this->roleRepo->getAll($request->all(), $filters);
        return new RoleCollection($roles);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $roles = $this->roleRepo->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'table_view_id' => TableView::ROLE->value,
                'roles'         => $roles,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleFormRequest $request)
    {
        $request->validated();
        try {
            $role = $this->roleRepo->create($request->all());
            return response()->json([
                'status' => true,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role          = CustomRole::findOrFail($id);
        $role->modules = $this->roleRepo->getModulesAndPremissionsByRole($id);

        // $role->parent_modules = Module::with(['children.allPermissions'])->whereNull('parent_module_id')->get();

        return response()->json([
            'status'  => true,
            'data'    => [
                'role' => new RoleDetailResource($role),
                // 'role' => $role,
            ],
            'message' => '',
        ], 200);
    }

    public function getMyPermssions()
    {
        return response()->json([
            'status'  => true,
            'data'    => [
                'role' => new MyRoleResource($this->roleRepo->getUserRole()),
            ],
            'message' => '',
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleFormRequest $request, $id)
    {
        $request->validated();
        try {
            $role = $this->roleRepo->update($id, $request->all());
            return response()->json([
                'status' => true,
            ]);
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
            $this->roleRepo->delete($id);
            return response()->json(['success' => true], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:roles,id',
        ]);

        try {
            $this->roleRepo->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }
}

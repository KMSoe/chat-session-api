<?php
namespace App\Http\Controllers;

use App\Filters\UserFilter;
use App\Http\Requests\UserFormRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    protected $userRepo;

    public function __construct(UserRepo $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function getAll(Request $request, UserFilter $filters)
    {
        $roles = $this->userRepo->getAll($request->all(), $filters);
        return new UserCollection($roles);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // if (filter_var($request->query('all'), FILTER_VALIDATE_BOOLEAN)) {
        //     $roles = $this->userRepo->all();
        // } else {
        //     $roles = $this->userRepo->paginate($request->all());
        // }
        // return new UserCollection($roles);

        $users = $this->userRepo->paginate($request);

        return response()->json([
            'status' => true,
            'data'   => [
                'users' => $users,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserFormRequest $request)
    {
        $request->validated();
        try {
            $role = $this->userRepo->create($request->all());
            return new UserResource($role);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = $this->userRepo->get($id);
        return new UserResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserFormRequest $request, $id)
    {
        $request->validated();
        try {
            $role = $this->userRepo->update($id, $request->all());
            return new UserResource($role);
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
            $this->userRepo->delete($id);
            return response()->json(['success' => true], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        try {
            $this->userRepo->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }
}

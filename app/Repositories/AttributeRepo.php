<?php

namespace App\Repositories;

use App\Http\Resources\AttributeResource;
use App\Http\Services\CustomValuesService;
use App\Models\Attribute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Password\App\Helpers\PasswordHelper;

class AttributeRepo
{
    protected $customValuesService;

    public function __construct(CustomValuesService $customValuesService)
    {
        $this->customValuesService = $customValuesService;
    }

    /**
     * Get All Attributes.
     */
    public function getAll()
    {
        return Attribute::all();
    }

    /**
     * Get Attributes With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;
        $attributes = Attribute::query();

        // Filter by search
        if (isset($request['search'])) {
            $attributes->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['search'] . '%');
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $attributes->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $attributes->orderBy('created_at', 'DESC');
        }

        $attributes = $attributes->paginate($perPage);

        $data = $attributes->getCollection()->map(function ($item) {
            return new AttributeResource($item);
        });
        
        return $attributes->setCollection($data);
    }

    /**
     * Get Attribute with id
     */
    public function get($id)
    {
        return Attribute::findOrFail($id);
    }

    /**
     * Create Attribute
     */
    public function create(array $data)
    {
        $this->customValuesService->checkDuplicateName($data['name'], $data['module_ids'] ?? []);
        $data['options'] = json_encode($data['options'] ?? []);
        $attribute = Attribute::create($data);
        $attribute->modules()->sync($data['module_ids'] ?? []);

        return $attribute;
    }

    /**
     * Update Attribute
     */
    public function update($id, array $data)
    {
        $this->customValuesService->checkDuplicateName($data['name'], $data['module_ids'] ?? [], $id);
        $data['options'] = json_encode($data['options'] ?? []);
        $attribute = Attribute::findOrFail($id);
        $attribute->update($data);
        $attribute->modules()->sync($data['module_ids'] ?? []);

        return $attribute;
    }

    /**
     * Delete Attribute
     */
    public function delete($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->modules()->detach();
        $attribute->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids) {
        $attributes = Attribute::whereIn('id', $ids)->get();
        foreach ($attributes as $key => $attribute) {
            $attribute->delete();
        }
    }

    /**
     * Update Status
     */
    public function updateStatus($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->update([
            'status' => !$attribute->status
        ]);

        return $attribute;
    }

    public function getOriginalFile($data)
    {
        $user = auth()->user();

        $checked_result = PasswordHelper::checkAuthSessionToSeePassword($user, $data['password']);

        if ($checked_result['status'] == false) {
            return response()->json($checked_result, 400);
        }

        $file = $this->customValuesService->getOriginalFileInfo($data['file_id']);
        
        return $file;
    }

    public function getOriginalHTML($data)
    {
        $user = auth()->user();

        $checked_result = PasswordHelper::checkAuthSessionToSeePassword($user, $data['password']);

        if ($checked_result['status'] == false) {
            return response()->json($checked_result, 400);
        }

        $file = $this->customValuesService->getOriginalHTMLInfo($data['encrypted_value']);
        
        return $file;
    }
}

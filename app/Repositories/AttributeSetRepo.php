<?php

namespace App\Repositories;

use App\Http\Resources\AttributeSetResource;
use App\Models\AttributeSet;
use App\Models\Module;
use Dom\Attr;
use Illuminate\Support\Str;

class AttributeSetRepo
{
    /**
     * Get All Attribute Sets.
     */
    public function getAll()
    {
        return AttributeSet::with('attributes')->get();
    }

    /**
     * Get Attribute Sets With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;
        $attributeSets = AttributeSet::query();

        // Filter by search
        if (isset($request['search'])) {
            $attributeSets->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['search'] . '%');
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $attributeSets->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $attributeSets->orderBy('created_at', 'DESC');
        }

        $attributeSets = $attributeSets->with('attributes')->paginate($perPage);

        $data = $attributeSets->getCollection()->map(function ($item) {
            return new AttributeSetResource($item);
        });
        
        return $attributeSets->setCollection($data);
    }

    /**
     * Get Attribute Set with id
     */
    public function get($id)
    {
        return AttributeSet::with('attributes')->findOrFail($id);
    }

    /**
     * Create Attribute Set
     */
    public function create(array $data)
    {
        $data['created_by'] = auth()->user()->id;
        $attributeSet = AttributeSet::create($data);
        $this->createOrUpdateAttributes($attributeSet, $data);

        return $attributeSet->load('attributes');
    }

    /**
     * Update Attribute Set
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = auth()->user()->id;
        $attributeSet = AttributeSet::findOrFail($id);
        $attributeSet->update($data);
        $this->createOrUpdateAttributes($attributeSet, $data);

        return $attributeSet->load('attributes');
    }

    public function createOrUpdateAttributes($attributeSet, $data)
    {
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $syncData = [];
            foreach ($data['attributes'] as $attribute) {
                $syncData[$attribute['id']] = ['sort' => $attribute['sort'] ?? 0];
            }
            
            $currentIds = $attributeSet->attributes()->pluck('attributes.id')->toArray();
            $newIds = array_keys($syncData);
            
            $toDetach = array_diff($currentIds, $newIds);
            if (!empty($toDetach)) {
                $attributeSet->attributes()->detach($toDetach);
            }
            
            $attributeSet->attributes()->syncWithoutDetaching($syncData);
            
            foreach ($syncData as $attributeId => $pivotData) {
                $attributeSet->attributes()->updateExistingPivot($attributeId, $pivotData);
            }
        }
    }

    /**
     * Delete Attribute Set
     */
    public function delete($id)
    {
        $attributeSet = AttributeSet::findOrFail($id);
        $attributeSet->attributes()->detach();
        $attributeSet->delete();
    }

    public function getByModule($moduleName)
    {
        $module = Module::whereRaw('BINARY `name` = ?', [$moduleName])->firstOrFail();
        $sets = AttributeSet::with('attributes')->where('module_id', $module->id)->where('is_active', 1)->get();

        return $sets;
    }
}

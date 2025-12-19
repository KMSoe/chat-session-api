<?php

namespace Modules\CRM\App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\CRM\App\Models\ItemTemplate;
use Modules\CRM\App\Http\Resources\ItemTemplateResource;

class ItemTemplateRepo
{
    /**
     * Get All ItemTemplates.
     */
    public function getAll()
    {
        return ItemTemplate::all();
    }

    /**
     * Get ItemTemplates With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $itemTemplates = ItemTemplate::query();

        // Filter by search
        if(isset($request['search'])) 
        {
            $itemTemplates->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['search'] . '%');
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $itemTemplates->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $itemTemplates->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $itemTemplates->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $itemTemplates->get();

            return ItemTemplateResource::collection($items);
        }

        $itemTemplates = $itemTemplates->with('items')->paginate($perPage);

        $data = $itemTemplates->getCollection()->map(function ($item) {
            return new ItemTemplateResource($item);
        });

        return $itemTemplates->setCollection($data);
    }

    /**
     * Get ItemTemplate with id
     */
    public function get($id)
    {
        return ItemTemplate::with('items')->findOrFail($id);
    }

    /**
     * Create ItemTemplate
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;
        $itemTemplate = ItemTemplate::create($data);

        if(isset($data['item_ids']) && is_array($data['item_ids'])) {
            $itemTemplate->items()->sync($data['item_ids']);
        }

        return $itemTemplate->load('items');
    }
    
    /**
     * Update ItemTemplate
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $itemTemplate = ItemTemplate::findOrFail($id);

        $itemTemplate->update($data);

        if(isset($data['item_ids']) && is_array($data['item_ids'])) {
            $itemTemplate->items()->sync($data['item_ids']);
        }

        return $itemTemplate->load('items');
    }

    /**
     * Delete ItemTemplate
     */
    public function delete($id)
    {
        $itemTemplate = ItemTemplate::findOrFail($id);
        $itemTemplate->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids) 
    {
        $itemTemplates = ItemTemplate::whereIn('id', $ids)->get();
        foreach ($itemTemplates as $key => $itemTemplate) {
            $itemTemplate->delete();
        }
    }
}

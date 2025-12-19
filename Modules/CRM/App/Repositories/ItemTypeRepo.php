<?php
namespace Modules\CRM\App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CRM\App\Models\ItemType;
use Modules\CRM\App\Models\ItemTypeAttribute;
use Modules\CRM\App\resources\ItemTypeResource;

class ItemTypeRepo
{
    /**
     * Get ItemTypes With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $ItemTypes = ItemType::with(['attributes']);

        // Filter by search
        if (isset($request['search'])) {
            $ItemTypes->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['search'] . '%')
                    ->orWhere('description', 'like', '%' . $request['search'] . '%');
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $ItemTypes->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $ItemTypes->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $ItemTypes->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $ItemTypes->get();

            return ItemTypeResource::collection($items);
        }

        $ItemTypes = $ItemTypes->paginate($perPage);

        $data = $ItemTypes->getCollection()->map(function ($item) {
            return new ItemTypeResource($item);
        });

        return $ItemTypes->setCollection($data);
    }

    public function getAll()
    {
        return ItemType::with(['attributes'])->get();
    }

    /**
     * Get ItemType with id
     */
    public function get($id)
    {
        return ItemType::with(['attributes'])->findOrFail($id);
    }

    /**
     * Create ItemType
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;

        DB::beginTransaction();
        $item_type = ItemType::create($data);

        if (isset($data['attribute_ids'])) {
            $item_type->attributes()->sync($data['attribute_ids']);
        }

        DB::commit();

        return $item_type;
    }

    /**
     * Update ItemType
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;

        DB::beginTransaction();
        $item_type = ItemType::findOrFail($id);
        $item_type->update($data);

        if (isset($data['attribute_ids'])) {
            $item_type->attributes()->sync($data['attribute_ids']);
        }

        DB::commit();

        return $item_type;
    }

    /**
     * Delete ItemType
     */
    public function delete($id)
    {
        $item_type = ItemType::findOrFail($id);
        $item_type->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids)
    {
        DB::beginTransaction();
        ItemTypeAttribute::whereIn('item_type_id', $ids)->delete();
        ItemType::whereIn('id', $ids)->delete();
        DB::commit();

        return true;
    }
}

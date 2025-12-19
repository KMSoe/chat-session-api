<?php
namespace Modules\CRM\App\Repositories;

use App\Http\Services\CustomValuesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CRM\App\Models\Item;
use Modules\CRM\App\Models\ItemType;
use Modules\CRM\App\resources\ItemResource;

class ItemRepo
{
    protected $customValuesService;
    protected $customValuesModule = 'item';

    public function __construct(CustomValuesService $customValuesService)
    {
        $this->customValuesService = $customValuesService;
    }

    /**
     * Get All items.
     */
    public function getAll()
    {
        return Item::with(['itemType.attributes', 'customValues.attribute'])->get();
    }

    /**
     * Get items With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $items = Item::with(['itemType.attributes', 'customValues.attribute']);

        if (isset($request['item_type_id'])) {
            $items->where('item_type_id', $request['item_type_id']);
        }

        // Filter by search
        if (isset($request['search'])) {
            $items->where(function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request['search'] . '%')
                    ->orWhere('description', 'like', '%' . $request['search'] . '%');
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $items->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $items->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $items->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $items->get();

            return ItemResource::collection($items);
        }

        $items = $items->paginate($perPage);

        $data = $items->getCollection()->map(function ($item) {
            return new ItemResource($item);
        });

        return $items->setCollection($data);
    }

    public function get($id)
    {
        return Item::with(['itemType.attributes', 'customValues.attribute'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;

        DB::beginTransaction();
        $item = Item::create($data);

        $item_type = ItemType::with(['attributes'])->where('id', $data['item_type_id'])->first();

        if ($item_type) {
            $attributeNames = $item_type->attributes()
                ->pluck('name')
                ->toArray();

            $customData = [];

            foreach ($attributeNames as $name) {
                if (is_array($data)) {
                    if (array_key_exists($name, $data)) {
                        $customData[$name] = $data[$name];
                    }
                } else {
                    if ($data->has($name)) {
                        $customData[$name] = $data->input($name);
                    }
                }
            }

            if (isset($customData) && ! empty($customData)) {
                $this->customValuesService->saveItemCustomValuesWithAttributes($customData, $item->id, $item_type->attributes);
            }
        }

        DB::commit();

        return $item;
    }

    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;

        $item = Item::findOrFail($id);
        $item->update($data);

        $item_type = ItemType::with(['attributes'])->where('id', $data['item_type_id'])->first();

        if ($item_type) {
            $attributeNames = $item_type->attributes()
                ->pluck('name')
                ->toArray();

            $customData = [];

            foreach ($attributeNames as $name) {
                if (is_array($data)) {
                    if (array_key_exists($name, $data)) {
                        $customData[$name] = $data[$name];
                    }
                } else {
                    if ($data->has($name)) {
                        $customData[$name] = $data->input($name);
                    }
                }
            }

            if (isset($customData) && ! empty($customData)) {
                $this->customValuesService->saveItemCustomValuesWithAttributes($customData, $item->id, $item_type->attributes);
            }
        }

        return $item;
    }

    public function delete($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids)
    {
        return Item::whereIn('id', $ids)->delete();

    }
}

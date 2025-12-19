<?php

namespace Modules\CRM\App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\CRM\App\Http\Resources\TaxResource;
use Modules\CRM\App\Models\Tax;

class TaxRepo
{
    /**
     * Get All Taxes.
     */
    public function getAll()
    {
        return Tax::all();
    }

    /**
     * Get Taxes With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $taxes = Tax::query();

        // Filter by search
        if(isset($request['search'])) 
        {
            $taxes->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['search'] . '%');
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $taxes->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $taxes->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $taxes->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $taxes->get();

            return TaxResource::collection($items);
        }

        $taxes = $taxes->paginate($perPage);

        $data = $taxes->getCollection()->map(function ($item) {
            return new TaxResource($item);
        });

        return $taxes->setCollection($data);
    }

    /**
     * Get Tax with id
     */
    public function get($id)
    {
        return Tax::findOrFail($id);
    }

    /**
     * Create Tax
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;
        $tax = Tax::create($data);

        return $tax;
    }
    
    /**
     * Update Tax
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $tax = Tax::findOrFail($id);

        $tax->update($data);

        return $tax;
    }

    /**
     * Delete Tax
     */
    public function delete($id)
    {
        $tax = Tax::findOrFail($id);
        $tax->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids) {
        $taxes = Tax::whereIn('id', $ids)->get();
        foreach ($taxes as $key => $tax) {
            $tax->delete();
        }
    }
}

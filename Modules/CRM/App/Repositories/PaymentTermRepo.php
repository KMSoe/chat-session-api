<?php

namespace Modules\CRM\App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\CRM\App\Http\Resources\PaymentTermResource;
use Modules\CRM\App\Models\PaymentTerm;

class PaymentTermRepo
{
    /**
     * Get All Payment Terms.
     */
    public function getAll()
    {
        return PaymentTerm::all();
    }

    /**
     * Get Payment Terms With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $payment_terms = PaymentTerm::query();

        // Filter by search
        if(isset($request['search'])) 
        {
            $payment_terms->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['search'] . '%');
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $payment_terms->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $payment_terms->orderBy('created_at', 'DESC');
        }

        $payment_terms = $payment_terms->paginate($perPage);

        $data = $payment_terms->getCollection()->map(function ($item) {
            return new PaymentTermResource($item);
        });

        return $payment_terms->setCollection($data);
    }

    /**
     * Get Payment Term with id
     */
    public function get($id)
    {
        return PaymentTerm::findOrFail($id);
    }

    /**
     * Create Payment Term
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;
        $payment_term = PaymentTerm::create($data);

        return $payment_term;
    }
    
    /**
     * Update Payment Term
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $payment_term = PaymentTerm::findOrFail($id);

        $payment_term->update($data);

        return $payment_term;
    }

    /**
     * Delete Payment Term
     */
    public function delete($id)
    {
        $payment_term = PaymentTerm::findOrFail($id);
        $payment_term->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids) 
    {
        $payment_terms = PaymentTerm::whereIn('id', $ids)->get();
        foreach ($payment_terms as $key => $payment_term) {
            $payment_term->delete();
        }
    }
}

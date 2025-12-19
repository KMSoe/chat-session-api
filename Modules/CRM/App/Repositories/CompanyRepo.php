<?php

namespace Modules\CRM\App\Repositories;

use App\Http\Services\CustomValuesService;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\CRM\App\Http\Resources\CompanyListingResource;
use Modules\CRM\App\Http\Resources\CompanyResource;
use Modules\CRM\App\Models\Company;

class CompanyRepo
{
    protected $customValuesService;
    protected $customValuesModule = 'company';

    public function __construct(CustomValuesService $customValuesService)
    {
        $this->customValuesService = $customValuesService;
    }

    /**
     * Get All Companies.
     */
    public function getAll()
    {
        return Company::with('contacts', 'customValues.attribute', 'createdBy', 'updatedBy', 'currency', 'billingCountry', 'billingState', 'shippingCountry', 'shippingState', 'tax', 'logo')->get();
    }

    /**
     * Get Companies With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $companies = Company::query();

        // Filter by search
        if(isset($request['search'])) 
        {
            $companies->where(function ($query) use ($request) {
                $query->where('company_code', 'like', '%' . $request['search'] . '%')
                        ->orWhere('name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('domain', 'like', '%' . $request['search'] . '%');
                });
        }

        // Filter by country_ids
        if(isset($request['country_ids']) && !empty($request['country_ids'])) {
            $countryIds = explode(',', $request['country_ids']);
            $companies->whereIn('billing_country_id', $countryIds)
                      ->orWhereIn('shipping_country_id', $countryIds);
        }

        // Filter by currency_ids
        if(isset($request['currency_ids']) && !empty($request['currency_ids'])) {
            $currencyIds = explode(',', $request['currency_ids']);
            $companies->whereIn('currency_id', $currencyIds);
        }

        // Filter by district
        if(isset($request['district']) && !empty($request['district'])) {
            $companies->where('billing_district', 'like', '%' . $request['district'] . '%')
                      ->orWhere('shipping_district', 'like', '%' . $request['district'] . '%');
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $companies->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $companies->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $companies->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $companies->get();

            return CompanyResource::collection($items);
        }

        $companies = $companies->with('contacts', 'customValues.attribute', 'createdBy', 'updatedBy', 'currency', 'billingCountry', 'billingState', 'shippingCountry', 'shippingState', 'tax', 'logo')->paginate($perPage);

        $data = $companies->getCollection()->map(function ($item) {
            return new CompanyListingResource($item);
        });

        return $companies->setCollection($data);
    }

    /**
     * Get Company with id
     */
    public function get($id)
    {
        return Company::with('contacts', 'customValues.attribute', 'createdBy', 'updatedBy', 'currency', 'billingCountry', 'billingState', 'shippingCountry', 'shippingState', 'tax', 'logo')->findOrFail($id);
    }

    /**
     * Create Company
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;
        $company = Company::create($data);

        if(isset($data['contacts']) && is_array($data['contacts'])) {
            foreach ($data['contacts'] as $contactData) {
                $contactData['created_by'] = auth()->user()->id ?? 0;
                $contactData['password'] = encrypt($contactData['password'] ?? null);
                $company->contacts()->create($contactData);
            }
        }

        $module = Module::whereRaw('BINARY `name` = ?', [$this->customValuesModule])->first();

        if ($module) {
            $attributeNames = $module->attributes()
                ->where('status', 1)
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
                $this->customValuesService->save($customData, $company->id, $this->customValuesModule);
            }
        }

        return $company->load('contacts', 'customValues.attribute', 'createdBy', 'updatedBy', 'currency', 'billingCountry', 'billingState', 'shippingCountry', 'shippingState', 'tax', 'logo');
    }
    
    /**
     * Update Company
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $company = Company::findOrFail($id);
        $company->update($data);

        if(isset($data['contacts']) && is_array($data['contacts'])) {
            $existingContactIds = [];
            
            foreach ($data['contacts'] as $contactData) {
                if (in_array($contactData['id'] ?? null, [null, "null", "undefined", "", "undefined"], true)) {
                    $contactData['id'] = null;
                }
                
                $contactData['updated_by'] = auth()->user()->id ?? 0;
                $contactData['company_id'] = $company->id;
                
                if ($contactData['id']) {
                    $contact = $company->contacts()->find($contactData['id']);
                    if ($contact) {
                        $contact->update($contactData);
                        $existingContactIds[] = $contactData['id'];
                    }
                } else {
                    unset($contactData['id']);
                    $contactData['created_by'] = auth()->user()->id ?? 0;
                    $newContact = $company->contacts()->create($contactData);
                    $existingContactIds[] = $newContact->id;
                }
            }
        
            if (!empty($existingContactIds)) {
                $company->contacts()->whereNotIn('id', $existingContactIds)->delete();
            }
        }

        $module = Module::whereRaw('BINARY `name` = ?', [$this->customValuesModule])->first();

        if ($module) {
            $attributeNames = $module->attributes()
                ->where('status', 1)
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
                $this->customValuesService->save($customData, $company->id, $this->customValuesModule);
            }
        }

        return $company->load('contacts', 'customValues.attribute', 'createdBy', 'updatedBy', 'currency', 'billingCountry', 'billingState', 'shippingCountry', 'shippingState', 'tax', 'logo');
    }

    /**
     * Delete Company
     */
    public function delete($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids) {
        $companies = Company::whereIn('id', $ids)->get();
        foreach ($companies as $key => $company) {
            $company->delete();
        }
    }

    public function getComments($companyId)
    {
        $company = Company::findOrFail($companyId);

        $comments = $company->comments()->get()->map(function ($comment) {
            return [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'commented_by' => $comment->commentBy?->name,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ];
        });

        return $comments;
    }

    public function addComment($companyId, $data)
    {
        $company = Company::findOrFail($companyId);

        $commentData = [
            'comment'   => $data['comment'],
            'commented_by' => auth()->user()->id,
        ];

        $comment = $company->comments()->create($commentData);

        return $comment;
    }

    public function updateComment($companyId, $commentId, $data)
    {
        $company = Company::findOrFail($companyId);

        $comment = $company->comments()->where('id', $commentId)->firstOrFail();
        
        $comment->update([
            'comment' => $data['comment'],
        ]);

        return $comment;
    }

    public function deleteComment($companyId, $commentId)
    {
        $company = Company::findOrFail($companyId);

        $comment = $company->comments()->where('id', $commentId)->firstOrFail();

        $comment->delete();
    }
}

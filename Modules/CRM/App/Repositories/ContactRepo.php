<?php

namespace Modules\CRM\App\Repositories;

use App\Http\Services\CustomValuesService;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\CRM\App\Http\Resources\ContactResource;
use Modules\CRM\App\Models\Contact;
use Modules\Password\App\Helpers\CryptoHelper;

class ContactRepo
{
    protected $customValuesService;
    protected $customValuesModule = 'contact';

    public function __construct(CustomValuesService $customValuesService)
    {
        $this->customValuesService = $customValuesService;
    }

    /**
     * Get All Contacts.
     */
    public function getAll()
    {
        return Contact::with('customValues.attribute', 'company')->get();
    }

    /**
     * Get Contacts With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $contacts = Contact::query();

        // Filter by search
        if(isset($request['search'])) 
        {
            $contacts->where(function ($query) use ($request) {
                $query->where('contact_code', 'like', '%' . $request['search'] . '%')
                        ->orWhere('first_name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('last_name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('email', 'like', '%' . $request['search'] . '%');
                });
        }

        if(isset($request['company_ids'])) 
        {
            $company_ids = is_array($request['company_ids']) ? $request['company_ids'] : explode(',', $request['company_ids']);
            $contacts->whereIn('company_id', $company_ids);
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $contacts->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $contacts->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $contacts->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $contacts->get();

            return ContactResource::collection($items);
        }

        $contacts = $contacts->with('customValues.attribute', 'company')->paginate($perPage);

        $data = $contacts->getCollection()->map(function ($item) {
            return new ContactResource($item);
        });

        return $contacts->setCollection($data);
    }

    /**
     * Get Contact with id
     */
    public function get($id)
    {
        return Contact::with('customValues.attribute', 'company')->findOrFail($id);
    }

    /**
     * Create Contact
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;
        $data['password'] = encrypt($data['password']) ?? null;

        if(isset($data['is_primary']) && $data['is_primary'] == true) {
            Contact::where('company_id', $data['company_id'])->update(['is_primary' => false]);
        }
        
        $contact = Contact::create($data);

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
                $this->customValuesService->save($customData, $contact->id, $this->customValuesModule);
            }
        }

        return $contact->load('customValues.attribute', 'company');
    }
    
    /**
     * Update Contact
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $contact = Contact::findOrFail($id);

        if(isset($data['is_primary']) && $data['is_primary'] == true && $contact->is_primary == false) {
            Contact::where('company_id', $data['company_id'])->update(['is_primary' => false]);
        }

        $contact->update($data);

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
                $this->customValuesService->save($customData, $contact->id, $this->customValuesModule);
            }
        }

        return $contact->load('customValues.attribute', 'company');
    }

    /**
     * Delete Contact
     */
    public function delete($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids) {
        $contacts = Contact::whereIn('id', $ids)->get();
        foreach ($contacts as $key => $contact) {
            $contact->delete();
        }
    }

    public function updatePassword($id, $data)
    {
        $contact = Contact::findOrFail($id);

        if (!password_verify($data['old_password'], $contact->password)) {
            throw new \Exception('Old password is incorrect');
        }

        $contact->update([
            'password' => encrypt($data['new_password']),
        ]);
    }
}

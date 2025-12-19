<?php

namespace Modules\CRM\App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\CRM\App\Console\Commands\GenerateRecurringInvoice;
use Modules\CRM\App\Http\Resources\RecurringInvoiceListingResource;
use Modules\CRM\App\Models\RecurringInvoice;

class RecurringInvoiceRepo
{
    private GenerateRecurringInvoice $generateRecurringInvoice;

    public function __construct(GenerateRecurringInvoice $generateRecurringInvoice)
    {
        $this->generateRecurringInvoice = $generateRecurringInvoice;
    }

    /**
     * Get All Recurring Invoices.
     */
    public function getAll()
    {
        return RecurringInvoice::with('company', 'contact', 'logs', 'salePerson')->get();
    }

    /**
     * Get Recurring Invoices With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $recurringInvoices = RecurringInvoice::query();

        // Filter by search
        if(isset($request['search'])) 
        {
            $recurringInvoices->where(function ($query) use ($request) {
                // Add search conditions here
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $recurringInvoices->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $recurringInvoices->orderBy('created_at', 'DESC');
        }

        $recurringInvoices = $recurringInvoices->with('company', 'contact', 'logs', 'salePerson')->paginate($perPage);

        $data = $recurringInvoices->getCollection()->map(function ($item) {
            return new RecurringInvoiceListingResource($item);
        });

        return $recurringInvoices->setCollection($data);
    }

    /**
     * Get Recurring Invoice with id
     */
    public function get($id)
    {
        return RecurringInvoice::with(
            'company', 
            'contact',
            'currency',
            'paymentTerm',
            'project',
            'itemTemplate',
            'tax',
            'salePerson',
            'organizationSignatures.assignedSigner',
            'items.tax',
            'items.item',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy',
            'logs'
        )->findOrFail($id);
    }

    private function createActivityLog($recurringInvoice, $data)
    {
        $data['description'] = $data['description'] ?? null;
        $data['action_by']   = auth()->user()->id ?? 0;

        return $recurringInvoice->activityLogs()->create($data);
    }

    /**
     * Create Recurring Invoice
     */
    public function create(array $data)
    {
        $today = date('Y-m-d');
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $data['discount_value'] = $data['discount_value'] ?? 0;
        $recurringInvoice = RecurringInvoice::create($data);

        if(isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $recurringInvoice->items()->create($itemData);
            }
        }

        if(isset($data['organization_signatures']) && is_array($data['organization_signatures'])) {
            foreach ($data['organization_signatures'] as $signatureData) {
                $signatureData['type'] = 'organization';
                $recurringInvoice->organizationSignatures()->create($signatureData);
            }
        }

        if(isset($data['customer_signatures']) && is_array($data['customer_signatures'])) {
            foreach ($data['customer_signatures'] as $signatureData) {
                $signatureData['type'] = 'customer';
                $recurringInvoice->customerSignatures()->create($signatureData);
            }
        }

        if(isset($data['communications']) && is_array($data['communications'])) {
            foreach ($data['communications'] as $contactId) {
                $recurringInvoice->emailCommunications()->create(['contact_id' => $contactId]);
            }
        }

        if(isset($data['files']) && is_array($data['files'])) {
            foreach ($data['files'] as $fileId) {
                $recurringInvoice->files()->create(['file_id' => $fileId]);
            }
        }

        $this->generateRecurringInvoice->generateInvoice($recurringInvoice);

        $this->createActivityLog($recurringInvoice, [
            'description' => 'Recurring Invoice created.',
        ]);

        return $recurringInvoice->load(
            'company', 
            'contact',
            'currency',
            'paymentTerm',
            'project',
            'itemTemplate',
            'tax',
            'salePerson',
            'organizationSignatures.assignedSigner',
            'items.tax',
            'items.item',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy',
            'logs'
        );
    }
    
    /**
     * Update Recurring Invoice
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $recurringInvoice = RecurringInvoice::findOrFail($id);

        $recurringInvoice->update($data);

        if(isset($data['items']) && is_array($data['items'])) {
            $recurringInvoice->items()->delete();
            foreach ($data['items'] as $itemData) {
                $recurringInvoice->items()->create($itemData);
            }
        }

        if(isset($data['organization_signatures']) && is_array($data['organization_signatures'])) {
            $recurringInvoice->organizationSignatures()->delete();
            foreach ($data['organization_signatures'] as $signatureData) {
                $signatureData['type'] = 'organization';
                $recurringInvoice->organizationSignatures()->create($signatureData);
            }
        }

        if(isset($data['customer_signatures']) && is_array($data['customer_signatures'])) {
            $recurringInvoice->customerSignatures()->delete();
            foreach ($data['customer_signatures'] as $signatureData) {
                $signatureData['type'] = 'customer';
                $recurringInvoice->customerSignatures()->create($signatureData);
            }
        }

        if(isset($data['communications']) && is_array($data['communications'])) {
            $recurringInvoice->emailCommunications()->delete();
            foreach ($data['communications'] as $contactId) {
                $recurringInvoice->emailCommunications()->create(['contact_id' => $contactId]);
            }
        }

        if(isset($data['files']) && is_array($data['files'])) {
            $recurringInvoice->files()->delete();
            foreach ($data['files'] as $fileId) {
                $recurringInvoice->files()->create(['file_id' => $fileId]);
            }
        }

        $this->createActivityLog($recurringInvoice, [
            'description' => 'Recurring Invoice updated.',
        ]);

        return $recurringInvoice->load(
            'company', 
            'contact',
            'currency',
            'paymentTerm',
            'project',
            'itemTemplate',
            'tax',
            'salePerson',
            'organizationSignatures.assignedSigner',
            'items.tax',
            'items.item',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy',
            'logs'
        );
    }

    /**
     * Delete Recurring Invoice
     */
    public function delete($id)
    {
        $recurringInvoice = RecurringInvoice::findOrFail($id);
        $recurringInvoice->delete();
    }

    public function getActivityLogs($invoiceId)
    {
        $invoice = RecurringInvoice::findOrFail($invoiceId);

        $activityLogs = $invoice->activityLogs()->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'description' => $log->description,
                'action_by' => $log->actionBy?->name,
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ];
        });

        return $activityLogs;
    }
}
<?php
namespace Modules\CRM\App\Repositories;

use App\Models\CodePrefix;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CRM\App\Http\Resources\ContactWithUnpaidInvociesResource;
use Modules\CRM\App\Models\Contact;
use Modules\CRM\App\Models\Invoice;
use Modules\CRM\App\Models\InvoiceReceivedPayment;
use Modules\CRM\App\Models\ReceivedPayment;
use Modules\CRM\App\resources\ReceivedPaymentResource;

class ReceivedPaymentRepo
{

    public function getAll()
    {
        return ReceivedPayment::with([
            'contact',
            'invoiceReceivedPayments.invoice',
            'refundHistories.invoice',
            'emailCommunications.contact',
            'files.file',

            'createdBy',
            'updatedBy',
        ])->get();
    }

    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $payments = ReceivedPayment::with([
            'contact',
            'invoiceReceivedPayments.invoice',
            'refundHistories.invoice',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy',
        ]);

        // Filter by search
        if (isset($request['search'])) {
            $payments->where(function ($query) use ($request) {
                $query->where('payment_number', 'like', '%' . $request['search'] . '%')
                    ->orWhereHas('contact', function ($q) use ($request) {
                        $q->where('first_name', 'like', '%' . $request['search'] . '%')
                            ->orWhere('last_name', 'like', '%' . $request['search'] . '%');
                    });
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $payments->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $payments->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $payments->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $payments->get();

            return ReceivedPaymentResource::collection($items);
        }

        $payments = $payments->paginate($perPage);

        $data = $payments->getCollection()->map(function ($item) {
            return new ReceivedPaymentResource($item);
        });

        return $payments->setCollection($data);
    }

    public function getContactsWithUnpaidInvoices($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $contacts = Contact::with(['unpaidInvoices']);

        // Filter by search
        if (isset($request['search'])) {
            $contacts->where(function ($query) use ($request) {
                $query->where('contact_code', 'like', '%' . $request['search'] . '%')
                    ->orWhere('first_name', 'like', '%' . $request['search'] . '%')
                    ->orWhere('last_name', 'like', '%' . $request['search'] . '%')
                    ->orWhere('email', 'like', '%' . $request['search'] . '%');
            });
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

        $contacts = $contacts->paginate($perPage);

        $data = $contacts->getCollection()->map(function ($item) {
            return new ContactWithUnpaidInvociesResource($item);
        });

        return $contacts->setCollection($data);
    }

    /**
     * Get Invoice with id
     */
    public function get($id)
    {
        return ReceivedPayment::with([
            'contact',
            'invoiceReceivedPayments.invoice',
            'refundHistories.invoice',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy',
        ])->findOrFail($id);
    }

    /**
     * Create Invoice
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;

        DB::beginTransaction();
        $payment = ReceivedPayment::create($data);

        if (isset($data['invoice_applications']) && is_array($data['invoice_applications'])) {
            foreach ($data['invoice_applications'] as $invoice_data) {
                $payment->invoiceReceivedPayments()->create([
                    'invoice_id'     => $invoice_data['invoice_id'],
                    'payment_amount' => $invoice_data['payment_amount'],
                    'status'         => $data['status'],
                ]);

                if ($data['status'] == 'paid') {
                    $invoice                    = Invoice::with(['payments'])->findOrFail($invoice_data['invoice_id']);
                    $invoice->last_payment_date = $data['payment_received_date'] ? Carbon::parse($data['payment_received_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
                    $invoice->balance_due       = $invoice->balance_due - $invoice_data['payment_amount'];

                    $total_amount_received = $invoice->payments->where('status', 'paid')
                        ->sum('payment_amount');

                    if ($total_amount_received >= $invoice->grand_total) {
                        $invoice->status = 'paid';
                    } else {
                        $invoice->status = 'partially_paid';
                    }
                    $invoice->save();
                }
            }
        }

        if (isset($data['communications']) && is_array($data['communications'])) {
            foreach ($data['communications'] as $contactId) {
                $payment->emailCommunications()->create(['contact_id' => $contactId]);
            }
        }

        if (isset($data['files']) && is_array($data['files'])) {
            foreach ($data['files'] as $fileId) {
                $payment->files()->create(['file_id' => $fileId]);
            }
        }

        DB::commit();

        // $this->createActivityLog($payment, [
        //     'description' => 'Payment is newly created',
        // ]);

        return $payment->load(
            'contact',
            'emailCommunications.contact',
            'files.file',
            'invoiceReceivedPayments',
            'createdBy',
            'updatedBy',
        );
    }

    public function recordPaymentForSpecificInvoice(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id ?? 0;

        DB::beginTransaction();
        $payment = ReceivedPayment::create($data);

        $payment->invoiceReceivedPayments()->create([
            'invoice_id'     => $data['invoice_id'],
            'payment_amount' => $data['amount_received'],
            'status'         => $data['status'],
        ]);

        if ($data['status'] == 'paid') {
            $invoice                    = Invoice::findOrFail($data['invoice_id']);
            $invoice->last_payment_date = $data['payment_received_date'] ? Carbon::parse($data['payment_received_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $invoice->balance_due       = $invoice->balance_due - $data['amount_received'];

            if ($data['amount_received'] >= $invoice->balance_due) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partially_paid';
            }
            $invoice->save();
        }

        // $this->createActivityLog($payment, [
        //     'description' => 'Payment recorded for specific invoice',
        // ]);

        DB::commit();

        return $payment->load(
            'contact',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy',
        );
    }

    public function updatePaymentForSpecificInvoice(array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;

        DB::beginTransaction();
        $invoice_received_payment = InvoiceReceivedPayment::with(['receivedPayment'])->findOrFail($data['invoice_payment_id']);
        $payment                  = $invoice_received_payment->receivedPayment;
        $previous_amount          = $invoice_received_payment->payment_amount;
        $payment->update($data);

        $invoice_received_payment->update([
            'invoice_id'     => $data['invoice_id'],
            'payment_amount' => $data['amount_received'],
            'status'         => $data['status'],
        ]);

        if ($data['status'] == 'paid') {
            $invoice                    = Invoice::findOrFail($data['invoice_id']);
            $invoice->last_payment_date = $data['payment_received_date'] ? Carbon::parse($data['payment_received_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $invoice->balance_due       = $invoice->balance_due - $previous_amount - $data['amount_received'];

            if ($data['amount_received'] >= $invoice->balance_due) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partially_paid';
            }
            $invoice->save();
        }

        // $this->createActivityLog($payment, [
        //     'description' => 'Payment recorded for specific invoice',
        // ]);

        DB::commit();

        return $payment->load(
            'contact',
            'emailCommunications.contact',
            'files.file',
            'createdBy',
            'updatedBy',
        );
    }

    public function refundPaymentForSpecificInvoice(array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;

        DB::beginTransaction();
        $invoice_received_payment = InvoiceReceivedPayment::with(['receivedPayment'])->findOrFail($data['invoice_payment_id']);
        $payment                  = $invoice_received_payment->receivedPayment;
        $payment->update([
            'payment_mode'     => $data['payment_mode'],
            'description'      => $data['description'],
            'reference_number' => $data['reference_number'],
            'refunded_date'    => $data['refunded_date'] ? Carbon::parse($data['refunded_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
            'status'           => 'refunded',
        ]);

        $invoice_received_payment->update([
            'status' => 'refunded',
        ]);

        $invoice              = Invoice::findOrFail($data['invoice_id']);
        $invoice->balance_due = $invoice->balance_due + $invoice_received_payment->payment_amount;
        $invoice->save();

        // $this->createActivityLog($payment, [
        //     'description' => 'Payment recorded for specific invoice',
        // ]);

        DB::commit();
    }

    public function deletePaymentForSpecificInvoice($invoice_payment_id)
    {

        DB::beginTransaction();
        $invoice_received_payment = InvoiceReceivedPayment::with(['receivedPayment'])->findOrFail($invoice_payment_id);

        $invoice              = Invoice::findOrFail($invoice_received_payment->invoice_id);
        $invoice->balance_due = $invoice->balance_due + $invoice_received_payment->payment_amount;
        $invoice->save();

        $invoice_received_payment->delete();

        // $this->createActivityLog($payment, [
        //     'description' => 'Payment recorded for specific invoice',
        // ]);

        DB::commit();
    }

    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $payment            = ReceivedPayment::findOrFail($id);

        DB::beginTransaction();
        $payment->update($data);

        if (isset($data['invoice_applications']) && is_array($data['invoice_applications'])) {
            $payment->invoiceReceivedPayments()->delete();
            foreach ($data['invoice_applications'] as $invoice_data) {
                $payment->invoiceReceivedPayments()->create([
                    'invoice_id'     => $invoice_data['invoice_id'],
                    'payment_amount' => $invoice_data['payment_amount'],
                    'status'         => $data['status'],
                ]);

                if ($data['status'] == 'paid') {
                    $invoice                    = Invoice::with(['payments'])->findOrFail($invoice_data['invoice_id']);
                    $invoice->last_payment_date = $data['payment_received_date'] ? Carbon::parse($data['payment_received_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
                    $invoice->balance_due       = $invoice->balance_due - $invoice_data['payment_amount'];

                    $total_amount_received = $invoice->payments->where('status', 'paid')
                        ->sum('payment_amount');

                    if ($total_amount_received >= $invoice->grand_total) {
                        $invoice->status = 'paid';
                    } else {
                        $invoice->status = 'partially_paid';
                    }
                    $invoice->save();
                }
            }
        }

        if (isset($data['communications']) && is_array($data['communications'])) {
            $payment->emailCommunications()->delete();
            foreach ($data['communications'] as $contactId) {
                $payment->emailCommunications()->create(['contact_id' => $contactId]);
            }
        }

        if (isset($data['files']) && is_array($data['files'])) {
            $payment->files()->delete();
            foreach ($data['files'] as $fileId) {
                $payment->files()->create(['file_id' => $fileId]);
            }
        }

        DB::commit();

        // $this->createActivityLog($payment, [
        //     'description' => 'Payment is edited',
        // ]);

        return $payment->load(
            'contact',
            'emailCommunications.contact',
            'files.file',
            'invoiceReceivedPayments',
            'createdBy',
            'updatedBy',
        );
    }

    public function refund($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;

        DB::beginTransaction();
        $payment = ReceivedPayment::with(['invoiceReceivedPayments.invoice'])->findOrFail($id);
        $payment->update([
            'payment_mode'     => $data['payment_mode'],
            'description'      => $data['description'],
            'reference_number' => $data['reference_number'],
            'refunded_date'    => $data['refunded_date'] ? Carbon::parse($data['refunded_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
            'status'           => 'refunded',
        ]);

        foreach ($payment->invoiceReceivedPayments as $key => $invoiceReceivedPayment) {
            $invoiceReceivedPayment->update([
                'status' => 'refunded',
            ]);

            $invoice              = $invoiceReceivedPayment->invoice;
            $invoice->balance_due = $invoice->balance_due + $invoiceReceivedPayment->payment_amount;
            $invoice->save();
        }

        DB::commit();
    }

    public function void($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;

        $payment = ReceivedPayment::findOrFail($id);
        // $payment->update([
        //     'status'
        // ]);

    }

    public function delete($id)
    {
        $payment = ReceivedPayment::findOrFail($id);

        DB::beginTransaction();

        $payment->invoiceReceivedPayments()->delete();
        $payment->emailCommunications()->delete();
        $payment->files()->delete();
        $payment->delete();

        DB::commit();
    }

    // private function createActivityLog($payment, $data)
    // {
    //     $data['description'] = $data['description'] ?? null;
    //     $data['action_by']   = auth()->user()->id ?? 0;

    //     return $payment->activityLogs()->create($data);
    // }

    // public function getActivityLogs($paymentId)
    // {
    //     $payment = ReceivedPayment::findOrFail($paymentId);

    //     $activityLogs = $payment->activityLogs()->get()->map(function ($log) {
    //         return [
    //             'id'          => $log->id,
    //             'description' => $log->description,
    //             'action_by'   => $log->actionBy?->name,
    //             'created_at'  => $log->created_at,
    //             'updated_at'  => $log->updated_at,
    //         ];
    //     });

    //     return $activityLogs;
    // }

    public function getInitialData()
    {
        $prefix                = CodePrefix::where('module_name', 'payment_received')->first();
        $payment_received_type = $prefix && $prefix->type == 'automatic' ? 'automatic' : 'manual';
        $payment_number        = $this->generatePaymentNumber();

        return [
            'payment_received_type' => $payment_received_type,
            'payment_number'        => $payment_number,
        ];
    }

    public function generatePaymentNumber()
    {
        $config = CodePrefix::where('module_name', 'payment_received')->first();

        if (! $config || $config->type !== 'automatic') {
            return null;
        }

        $prefix   = rtrim($config->prefix, '-') . '-';
        $nextBase = $config->next_number;

        $paddingLength = strlen($nextBase);

        $pattern = '/^' . preg_quote($prefix, '/') . '(\d{' . $paddingLength . '})$/';

        $payments = ReceivedPayment::select('payment_number')
            ->where('payment_number', 'LIKE', $prefix . '%')
            ->get();

        $valid = $payments->filter(function ($q) use ($pattern) {
            return preg_match($pattern, $q->payment_number);
        });

        if ($valid->isEmpty()) {
            return $prefix . str_pad($nextBase, $paddingLength, '0', STR_PAD_LEFT);
        }

        $last = $valid->sortByDesc(function ($q) use ($prefix) {
            return (int) str_replace($prefix, '', $q->payment_number);
        })->first();

        $currentNumber = (int) str_replace($prefix, '', $last->payment_number);
        $nextNumber    = str_pad($currentNumber + 1, $paddingLength, '0', STR_PAD_LEFT);

        return $prefix . $nextNumber;
    }

    public function attachFile($id, $data)
    {
        $payment = ReceivedPayment::findOrFail($id);

        if (! $payment->files()->where('file_id', $data['file_id'])->exists()) {
            $payment->files()->create(['file_id' => $data['file_id']]);
        }
    }

    public function deleteAttachedFile($id, $fileId)
    {
        $payment = ReceivedPayment::findOrFail($id);

        $payment->files()->where('file_id', $fileId)->delete();
    }
}

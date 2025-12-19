<?php
namespace Modules\CRM\App\Repositories;

use App\Models\CodePrefix;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CRM\App\Models\CreditNote;
use Modules\CRM\App\Models\CreditNoteInvoiceApplication;
use Modules\CRM\App\Models\CreditNoteRefundHistory;
use Modules\CRM\App\resources\CreditNoteResource;

class CreditNoteRepo
{

    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $credit_notes = CreditNote::with([
            'company',
            'contact.unpaidInvoices',
            'currency',
            'itemTemplate',
            'tax',
            'items.tax',
            'items.item',
            'files.file',
            'invoiceApplications.invoice',
            'refundHistories',
            'createdBy',
            'updatedBy',
        ]);

        // Filter by search
        if (isset($request['search'])) {
            $credit_notes->where(function ($query) use ($request) {
                $query->whereHas('contact', function ($subquery) use ($request) {
                    $subquery->where('first_name', 'LIKE', '%' . $request['search'] . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $request['search'] . '%');
                });
            });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $credit_notes->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $credit_notes->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $credit_notes->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $credit_notes->get();

            return CreditNoteResource::collection($items);
        }

        $credit_notes = $credit_notes->paginate($perPage);

        $data = $credit_notes->getCollection()->map(function ($item) {
            return new CreditNoteResource($item);
        });

        return $credit_notes->setCollection($data);
    }

    public function get($id)
    {
        return CreditNote::with([
            'company',
            'contact.unpaidInvoices',
            'currency',
            'itemTemplate',
            'tax',
            'items.tax',
            'items.item',
            'files.file',
            'invoiceApplications.invoice',
            'refundHistories',
            'createdBy',
            'updatedBy',
        ])->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['created_by']       = Auth::guard('api')->user()->id ?? 0;
        $data['tax_amount']       = $data['tax_amount'] ?? 0;
        $data['discount_value']   = $data['discount_value'] ?? 0;
        $data['credit_amount']    = $data['grand_total'];
        $data['credit_applied']   = 0;
        $data['remaining_credit'] = $data['grand_total'];

        DB::beginTransaction();
        $credit_note = CreditNote::create($data);

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $credit_note->items()->create($itemData);
            }
        }

        if (isset($data['files']) && is_array($data['files'])) {
            foreach ($data['files'] as $fileId) {
                $credit_note->files()->create(['file_id' => $fileId]);
            }
        }

        $this->createActivityLog($credit_note, [
            'description' => 'Credit Note is newly created',
        ]);

        DB::commit();

        return $credit_note->load(
            'company',
            'contact.unpaidInvoices',
            'currency',
            'itemTemplate',
            'tax',
            'items.tax',
            'items.item',
            'files.file',
            'invoiceApplications.invoice',
            'refundHistories',
            'createdBy',
            'updatedBy',
        );
    }

    public function update($id, array $data)
    {
        $data['updated_by']       = Auth::guard('api')->user()->id ?? 0;
        $data['credit_amount']    = $data['grand_total'];
        $data['remaining_credit'] = $data['grand_total'];
        $credit_note              = CreditNote::findOrFail($id);

        DB::beginTransaction();
        $credit_note->update($data);

        if (isset($data['items']) && is_array($data['items'])) {
            $credit_note->items()->delete();
            foreach ($data['items'] as $itemData) {
                $credit_note->items()->create($itemData);
            }
        }

        if (isset($data['files']) && is_array($data['files'])) {
            $credit_note->files()->delete();
            foreach ($data['files'] as $fileId) {
                $credit_note->files()->create(['file_id' => $fileId]);
            }
        }

        $this->createActivityLog($credit_note, [
            'description' => 'Credit Note is edited',
        ]);

        DB::commit();

        return $credit_note->load(
            'contact.unpaidInvoices',
            'currency',
            'itemTemplate',
            'tax',
            'items.tax',
            'items.item',
            'files.file',
            'invoiceApplications.invoice',
            'refundHistories',
            'createdBy',
            'updatedBy',
        );
    }

    public function applyToInvoices($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $credit_note        = CreditNote::findOrFail($id);

        DB::beginTransaction();
        if (isset($data['invoice_applications']) && is_array($data['invoice_applications'])) {
            $total_applied = 0;
            foreach ($data['invoice_applications'] as $invoice_data) {
                $credit_note->invoiceApplications()->create([
                    'invoice_id'               => $invoice_data['invoice_id'],
                    'credit_note_applied_date' => $invoice_data['credit_note_applied_date'] ? Carbon::parse($invoice_data['credit_note_applied_date'])->format('Y-m-d') :
                    Carbon::now()->format('Y-m-d'),
                    'payment_amount'           => $invoice_data['payment_amount'],
                ]);
                $total_applied += $invoice_data['payment_amount'];

            }

            $credit_note->credit_applied += $total_applied;
            $credit_note->remaining_credit -= $total_applied;

            if ($credit_note->remaining_credit <= 0) {
                $credit_note->status = 'closed';
            }

            $credit_note->save();

        }

        DB::commit();
    }

    public function applyForSpecificInvoice($data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $credit_note        = CreditNote::findOrFail($data['credit_note_id']);

        DB::beginTransaction();

        CreditNoteInvoiceApplication::create([
            'credit_note_id'           => $data['credit_note_id'],
            'invoice_id'               => $data['invoice_id'],
            'credit_note_applied_date' => $data['credit_note_applied_date'] ? Carbon::parse($data['credit_note_applied_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
            'payment_amount'           => $data['payment_amount'],
        ]);

        $credit_note->credit_applied += $data['payment_amount'];
        $credit_note->remaining_credit -= $data['payment_amount'];

        if ($credit_note->remaining_credit <= 0) {
            $credit_note->status = 'closed';
        }

        $credit_note->save();

        DB::commit();
    }

    public function deleteCreditNoteApplication($credit_note_application_id)
    {
        $credit_note_application = CreditNoteInvoiceApplication::with(['creditNote'])->findOrFail($credit_note_application_id);

        $credit_note = $credit_note_application->creditNote;

        DB::beginTransaction();

        $credit_note->credit_applied -= $credit_note_application->payment_amount;
        $credit_note->remaining_credit += $credit_note_application->payment_amount;
        $credit_note->save();

        $credit_note_application->delete();
        DB::commit();
    }

    public function refund($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id ?? 0;
        $credit_note        = CreditNote::findOrFail($id);

        DB::beginTransaction();
        $credit_note->refundHistories()->create([
            'payment_mode'     => $data['payment_mode'],
            'description'      => $data['description'],
            'reference_number' => $data['reference_number'] ?? '',
            'refunded_date'    => $data['refunded_date'] ? Carbon::parse($data['refunded_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
            'refunded_amount'  => $data['refunded_amount'],
        ]);

        $credit_note->remaining_credit -= $data['refunded_amount'];
        $credit_note->save();

        DB::commit();
    }

    public function updateRefundHistory($id, array $data)
    {
        $data['updated_by']         = Auth::guard('api')->user()->id ?? 0;
        $credit_note_refund_history = CreditNoteRefundHistory::with(['creditNote'])->findOrFail($id);

        DB::beginTransaction();
        $previous_refunded_amount = $credit_note_refund_history->refunded_amount;
        $credit_note_refund_history->update([
            'payment_mode'     => $data['payment_mode'],
            'description'      => $data['description'],
            'reference_number' => $data['reference_number'] ?? '',
            'refunded_date'    => $data['refunded_date'] ? Carbon::parse($data['refunded_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
            'refunded_amount'  => $data['refunded_amount'],
        ]);

        $credit_note = $credit_note_refund_history->creditNote;
        $credit_note->remaining_credit -= $previous_refunded_amount - $data['refunded_amount'];
        $credit_note->save();

        DB::commit();
    }

    public function deleteRefundHistory($id)
    {
        $credit_note_refund_history = CreditNoteRefundHistory::with(['creditNote'])->findOrFail($id);

        DB::beginTransaction();
        $refunded_amount = $credit_note_refund_history->refunded_amount;
        $credit_note_refund_history->delete();

        $credit_note = $credit_note_refund_history->creditNote;
        $credit_note->remaining_credit += $refunded_amount;
        $credit_note->save();

        DB::commit();
    }

    public function delete($id)
    {
        $credit_note = CreditNote::findOrFail($id);

        DB::beginTransaction();

        $credit_note->items()->delete();
        $credit_note->files()->delete();
        $credit_note->delete();

        DB::commit();
    }

    public function getInitialData()
    {
        $prefix             = CodePrefix::where('module_name', 'credit_note')->first();
        $credit_note_type   = $prefix && $prefix->type == 'automatic' ? 'automatic' : 'manual';
        $credit_note_number = $this->generateCreditNoteNumber();

        return [
            'credit_note_type'   => $credit_note_type,
            'credit_note_number' => $credit_note_number,
        ];
    }

    public function generateCreditNoteNumber()
    {
        $config = CodePrefix::where('module_name', 'credit_note')->first();

        if (! $config || $config->type !== 'automatic') {
            return null;
        }

        $prefix   = rtrim($config->prefix, '-') . '-';
        $nextBase = $config->next_number;

        $paddingLength = strlen($nextBase);

        $pattern = '/^' . preg_quote($prefix, '/') . '(\d{' . $paddingLength . '})$/';

        $credit_notes = CreditNote::select('credit_note_number')
            ->where('credit_note_number', 'LIKE', $prefix . '%')
            ->get();

        $valid = $credit_notes->filter(function ($q) use ($pattern) {
            return preg_match($pattern, $q->credit_note_number);
        });

        if ($valid->isEmpty()) {
            return $prefix . str_pad($nextBase, $paddingLength, '0', STR_PAD_LEFT);
        }

        $last = $valid->sortByDesc(function ($q) use ($prefix) {
            return (int) str_replace($prefix, '', $q->credit_note_number);
        })->first();

        $currentNumber = (int) str_replace($prefix, '', $last->credit_note_number);
        $nextNumber    = str_pad($currentNumber + 1, $paddingLength, '0', STR_PAD_LEFT);

        return $prefix . $nextNumber;
    }

    private function createActivityLog($credit_note, $data)
    {
        $data['description'] = $data['description'] ?? null;
        $data['action_by']   = auth()->user()->id ?? 0;

        return $credit_note->activityLogs()->create($data);
    }

    public function getActivityLogs($credit_note_id)
    {
        $credit_note = CreditNote::findOrFail($credit_note_id);

        $activityLogs = $credit_note->activityLogs()->get()->map(function ($log) {
            return [
                'id'          => $log->id,
                'description' => $log->description,
                'action_by'   => $log->actionBy?->name,
                'created_at'  => $log->created_at,
                'updated_at'  => $log->updated_at,
            ];
        });

        return $activityLogs;
    }

    public function getComments($credit_note_id)
    {
        $credit_note = CreditNote::findOrFail($credit_note_id);

        $comments = $credit_note->comments()->get()->map(function ($comment) {
            return [
                'id'           => $comment->id,
                'comment'      => $comment->comment,
                'commented_by' => $comment->commentBy?->name,
                'created_at'   => $comment->created_at,
                'updated_at'   => $comment->updated_at,
            ];
        });

        return $comments;
    }

    public function addComment($creditNoteId, $data)
    {
        $credit_note = CreditNote::findOrFail($creditNoteId);

        $commentData = [
            'comment'      => $data['comment'],
            'commented_by' => auth()->user()->id,
        ];

        $comment = $credit_note->comments()->create($commentData);

        return $comment;
    }

    public function updateComment($creditNoteId, $commentId, $data)
    {
        $credit_note = CreditNote::findOrFail($creditNoteId);

        $comment = $credit_note->comments()->where('id', $commentId)->firstOrFail();

        $comment->update([
            'comment' => $data['comment'],
        ]);

        return $comment;
    }

    public function deleteComment($creditNoteId, $commentId)
    {
        $credit_note = CreditNote::findOrFail($creditNoteId);

        $comment = $credit_note->comments()->where('id', $commentId)->firstOrFail();

        $comment->delete();
    }

    public function attachFile($id, $data)
    {
        $credit_note = CreditNote::findOrFail($id);

        if (! $credit_note->files()->where('file_id', $data['file_id'])->exists()) {
            $credit_note->files()->create(['file_id' => $data['file_id']]);
        }
    }

    public function deleteAttachedFile($id, $fileId)
    {
        $credit_note = CreditNote::findOrFail($id);

        $credit_note->files()->where('file_id', $fileId)->delete();
    }

}

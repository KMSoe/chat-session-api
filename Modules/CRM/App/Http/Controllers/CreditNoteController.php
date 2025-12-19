<?php
namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Http\Requests\ApplyCreditNoteToInvoiceRequest;
use Modules\CRM\App\Http\Requests\CreditNoteRequest;
use Modules\CRM\App\Http\Requests\RefundCreditNoteRequest;
use Modules\CRM\App\resources\CreditNoteCommentResource;
use Modules\CRM\App\resources\CreditNoteResource;
use Modules\CRM\App\Services\CreditNoteService;

class CreditNoteController extends Controller
{
    protected $service;

    public function __construct(CreditNoteService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $credit_notes = $this->service->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id'     => Module::where('name', 'credit_note')->first()?->id,
                'table_view_id' => TableView::fromName('credit_note'),
                'credit_notes'  => $credit_notes,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreditNoteRequest $request)
    {
        $request->validated();
        try {
            $credit_note = $this->service->create($request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'credit_note' => new CreditNoteResource($credit_note),
                ],
                'message' => 'Successfully saved',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $credit_note = $this->service->get($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'credit_note' => new CreditNoteResource($credit_note),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreditNoteRequest $request, $id)
    {
        $request->validated();
        try {
            $credit_note = $this->service->update($id, $request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'credit_note' => new CreditNoteResource($credit_note),
                ],
                'message' => 'Successfully updated',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function applyToInvoices(ApplyCreditNoteToInvoiceRequest $request, $id)
    {
        $request->validated();

        // Check amount to be applied is not more than available credit note amount

        try {
            $this->service->applyToInvoices($id, $request->all());
            return response()->json([
                'status'  => true,
                'data'    => [
                ],
                'message' => 'Successfully applied',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function applyForSpecificInvoice(Request $request, $invoiceId)
    {
        $request->merge([
            'invoice_id' => $invoiceId,
        ]);

        $request->validate([
            'invoice_id'               => 'required|integer|exists:invoices,id',
            'credit_note_id'           => 'required|integer|exists:credit_notes,id',
            'credit_note_applied_date' => ['required', 'date_format:Y-m-d'],
            'payment_amount'           => ['required', 'numeric', 'min:0.01'],
        ]);

        // Check amount to be applied is not more than available credit note amount

        $this->service->applyForSpecificInvoice($request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
            ],
            'message' => 'Successfully saved',
        ], 201);

    }

    public function deleteCreditNoteApplication($invoiceId, $credit_note_application_id)
    {
        $this->service->deleteCreditNoteApplication($credit_note_application_id);

        return response()->json([], 204);
    }

    public function refund(RefundCreditNoteRequest $request, $id)
    {
        $request->validated();
        // Check amount to be refunded is not more than available credit note amount
        try {
            $this->service->refund($id, $request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                ],
                'message' => 'Successfully refunded',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateRefundHistory(RefundCreditNoteRequest $request, $credit_note_id, $refund_history_id)
    {
        $request->validated();
        try {
            $this->service->updateRefundHistory($refund_history_id, $request->all());
            return response()->json([], 204);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteRefundHistory($credit_note_id, $refund_history_id)
    {
        try {
            $this->service->deleteRefundHistory($refund_history_id);
            return response()->json([
                'status'  => true,
                'data'    => [
                ],
                'message' => 'Successfully refunded',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return response()->json([
                'status'  => true,
                'message' => "Successfully deleted",
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getInitialData()
    {
        try {
            $data = $this->service->getInitialData();

            return response()->json([
                'status' => true,
                'data'   => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getActivityLogs($id)
    {
        try {
            $activityLogs = $this->service->getActivityLogs($id);

            return response()->json([
                'status' => true,
                'data'   => [
                    'activity_logs' => $activityLogs,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getComments($id)
    {
        try {
            $comments = $this->service->getComments($id);

            return response()->json([
                'status' => true,
                'data'   => [
                    'comments' => $comments,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function addComment($id, Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        try {
            $comment = $this->service->addComment($id, $data);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'comment' => new CreditNoteCommentResource($comment),
                ],
                'message' => 'Comment added successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateComment($creditNoteId, $commentId, Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        try {
            $comment = $this->service->updateComment($creditNoteId, $commentId, $data);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'comment' => new CreditNoteCommentResource($comment),
                ],
                'message' => 'Comment updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteComment($creditNoteId, $commentId)
    {
        try {
            $this->service->deleteComment($creditNoteId, $commentId);

            return response()->json([
                'status'  => true,
                'message' => 'Comment deleted successfully',
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function attachFile($id, Request $request)
    {
        $data = $request->validate([
            'file_id' => 'required|integer|exists:files,id',
        ]);

        try {
            $this->service->attachFile($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'File attached successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteAttachedFile($id, $fileId)
    {
        try {
            $this->service->deleteAttachedFile($id, $fileId);

            return response()->json([
                'status'  => true,
                'message' => 'Attached file deleted successfully',
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

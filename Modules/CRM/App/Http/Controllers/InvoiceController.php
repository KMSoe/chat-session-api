<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Enum\InvoiceStatus;
use Modules\CRM\App\Http\Requests\InvoiceInvitationLinkRequest;
use Modules\CRM\App\Http\Requests\InvoiceRequest;
use Modules\CRM\App\Http\Requests\InvoiceStatusUpdateRequest;
use Modules\CRM\App\Http\Resources\InvoiceCommentResource;
use Modules\CRM\App\Http\Resources\InvoiceDetailsResource;
use Modules\CRM\App\Http\Resources\RecurringInvoiceDetailsResource;
use Modules\CRM\App\Services\InvoiceService;

class InvoiceController extends Controller
{
    protected $service;

    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $invoices = $this->service->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id' => Module::where('name', 'invoice')->first()?->id,
                'table_view_id' => TableView::fromName('invoice'),
                'invoices'      => $invoices,
            ],
        ], 200);
    }

    public function getInvoiceBoard(Request $request)
    {
        $invoices = $this->service->getInvoiceBoard($request->all());

        return response()->json([
            'status' => true,
            'data'   => $invoices,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvoiceRequest $request)
    {
        $request->validated();
        try {
            $invoice = $this->service->create($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'invoice' => new InvoiceDetailsResource($invoice),
                ],
                'message' => 'Successfully saved'
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
        $invoice = $this->service->get($id);
        
        return response()->json([
            'status' => true,
            'data'   => [
                'invoice' => new InvoiceDetailsResource($invoice),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvoiceRequest $request, $id)
    {
        $request->validated();
        try {
            $invoice = $this->service->update($id, $request->all());
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'invoice' => new InvoiceDetailsResource($invoice),
                ],
                'message' => 'Successfully updated'
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

    public function approvalStatusUpdate(InvoiceStatusUpdateRequest $request)
    {
        $request->validated();
        try {
            $this->service->approvalStatusUpdate($request->all());
            return response()->json([
                'status'  => true,
                'message' => "Successfully updated",
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
                    'comment' => new InvoiceCommentResource($comment),
                ],
                'message' => 'Comment added successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateComment($invoiceId, $commentId, Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        try {
            $comment = $this->service->updateComment($invoiceId, $commentId, $data);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'comment' => new InvoiceCommentResource($comment),
                ],
                'message' => 'Comment updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteComment($invoiceId, $commentId)
    {
        try {
            $this->service->deleteComment($invoiceId, $commentId);

            return response()->json([
                'status'  => true,
                'message' => 'Comment deleted successfully',
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function sentMail($id, Request $request)
    {
        $data = $request->validate([
            'to' => 'required|array|email',
            'cc' => 'nullable|array|email',
            'bcc' => 'nullable|array|email',
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
        ]);

        try {
            $this->service->sentMail($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'Email sent successfully',
            ], 200);
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

    public function getSharedLinks($id)
    {
        try {
            $sharedLinks = $this->service->getSharedLinks($id);

            return response()->json([
                'status' => true,
                'data'   => [
                    'shared_links' => $sharedLinks,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function generateSharedLink($id, InvoiceInvitationLinkRequest $request)
    {
        $data = $request->validated();

        try {
            $sharedLink = $this->service->generateSharedLink($id, $data);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'shared_link' => $sharedLink,
                ],
                'message' => 'Shared link generated successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function disableSharedLink($id, Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:invoice_invitation_links,id',
        ]);

        try {
            $this->service->disableSharedLink($id, $request->all());

            return response()->json([
                'status'  => true,
                'message' => 'Shared link disabled successfully',
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

    public function signInvoice($id, Request $request)
    {
        $data = $request->validate([
            'signature_file_id' => 'required|integer|exists:files,id',
            'signature_label' => 'required|string|max:255',
        ]);

        try {
            $this->service->signInvoice($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'Invoice signed successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function signClientInvoice($id, Request $request)
    {
        $data = $request->validate([
            'signature_file_id' => 'required|integer|exists:files,id',
            'signature_label' => 'required|string|max:255',
        ]);

        try {
            $this->service->signClientInvoice($id, $data);

            return response()->json([
                'status' => true,
                'message' => 'Invoice signed successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getInvoicePublicDetails(string $id, Request $request)
    {
        try{
            $invoice = $this->service->getInvoicePublicDetails($id, $request->all());
        
            return response()->json([
                'status' => true,
                'data'   => [
                    'invoice' => new InvoiceDetailsResource($invoice),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getStatuses()
    {
        try {
            $statuses = collect(InvoiceStatus::cases())->map(function ($status) {
                return [
                    'label' => $status->label(),
                    'value' => $status->value,
                ];
            })->values()->toArray();
            
            return response()->json([
                'status' => true,
                'data'   => [
                    'statuses' => $statuses,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function addExpectedPaymentDate($id, Request $request)
    {
        $data = $request->validate([
            'expected_payment_date' => 'required|date',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->addExpectedPaymentDate($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'Expected payment date added successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function markAsVoid($id, Request $request)
    {
        $data = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->markAsVoid($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'Invoice marked as void successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function markAsWriteOff($id, Request $request)
    {
        $data = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->markAsWriteOff($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'Invoice marked as write-off successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancelWriteOff($id, Request $request)
    {
        $data = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->cancelWriteOff($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'Invoice write-off cancelled successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function downloadPdf($id)
    {
        try {
            $pdfContent = $this->service->generatePdf($id);

            return $pdfContent;
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

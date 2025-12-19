<?php
namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Enum\QuotationStatus;
use Modules\CRM\App\Http\Requests\QuotationInvitationLinkRequest;
use Modules\CRM\App\Http\Requests\QuotationRequest;
use Modules\CRM\App\Http\Requests\QuotationStatusUpdateRequest;
use Modules\CRM\App\Http\Resources\QuotationCommentResource;
use Modules\CRM\App\Http\Resources\QuotationDetailsResource;
use Modules\CRM\App\Services\QuotationService;

class QuotationController extends Controller
{
    protected $service;

    public function __construct(QuotationService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $quotations = $this->service->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id'     => Module::where('name', 'quotation')->first()?->id,
                'table_view_id' => TableView::fromName('quotation'),
                'quotations'    => $quotations,
            ],
        ], 200);
    }

    public function getQuotationBoard(Request $request)
    {
        $quotations = $this->service->getQuotationBoard($request->all());

        return response()->json([
            'status' => true,
            'data'   => $quotations,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuotationRequest $request)
    {
        $request->validated();
        try {
            $quotation = $this->service->create($request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'quotation' => new QuotationDetailsResource($quotation),
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
        $quotation = $this->service->get($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'quotation' => new QuotationDetailsResource($quotation),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuotationRequest $request, $id)
    {
        $request->validated();
        try {
            $quotation = $this->service->update($id, $request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'quotation' => new QuotationDetailsResource($quotation),
                ],
                'message' => 'Successfully updated',
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

    public function approvalStatusUpdate(QuotationStatusUpdateRequest $request)
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

    public function quotationStatusUpdate($id, Request $request)
    {
        $request->validate([
            'status'  => 'required|in:sent,accepted,declined',
        ]);

        try {
            $this->service->quotationStatusUpdate($id, $request->all());
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
                    'comment' => new QuotationCommentResource($comment),
                ],
                'message' => 'Comment added successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateComment($quotationId, $commentId, Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        try {
            $comment = $this->service->updateComment($quotationId, $commentId, $data);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'comment' => new QuotationCommentResource($comment),
                ],
                'message' => 'Comment updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteComment($quotationId, $commentId)
    {
        try {
            $this->service->deleteComment($quotationId, $commentId);

            return response()->json([
                'status'  => true,
                'message' => 'Comment deleted successfully',
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function sendMail($id, Request $request)
    {
        $data = $request->validate([
            'to'      => 'required|array',
            'to.*'    => 'email',
            'cc'      => 'nullable|array',
            'cc.*'    => 'email',
            'bcc'     => 'nullable|array',
            'bcc.*'   => 'email',
            'subject' => 'required|string|max:255',
            'body'    => 'nullable|string',
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

    public function generateSharedLink($id, QuotationInvitationLinkRequest $request)
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
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:quotation_invitation_links,id',
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

    public function signQuotation($id, Request $request)
    {
        $data = $request->validate([
            'signature_file_id' => 'required|integer|exists:files,id',
            'signature_label'   => 'required|string|max:255',
        ]);

        try {
            $this->service->signQuotation($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'Quotation signed successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function signClientQuotation($id, Request $request)
    {
        $data = $request->validate([
            'signature_file_id' => 'required|integer|exists:files,id',
            'signature_label'   => 'required|string|max:255',
        ]);

        try {
            $this->service->signClientQuotation($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'Quotation signed successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateClientQuotation($id, Request $request)
    {
        $data = $request->validate([
            'action'         => 'required|string|in:accepted,declined',
            'decline_reason' => 'nullable|required_if:action,declined|string|max:1000',
        ]);

        try {
            $this->service->updateClientQuotation($id, $data);

            return response()->json([
                'status'  => true,
                'message' => 'Quotation ' . ($data['action'] === 'accepted' ? 'accepted' : 'declined') . ' successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getQuotationPublicDetails(string $id, Request $request)
    {
        try {
            $quotation = $this->service->getQuotationPublicDetails($id, $request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'quotation' => new QuotationDetailsResource($quotation),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getStatuses()
    {
        try {
            $statuses = collect(QuotationStatus::cases())->map(function ($status) {
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

<?php
namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Http\Requests\PaymentForSpecificInvoiceRequest;
use Modules\CRM\App\Http\Requests\ReceivedPaymentRequest;
use Modules\CRM\App\Http\Requests\RefundPaymentRequest;
use Modules\CRM\App\resources\ReceivedPaymentResource;
use Modules\CRM\App\Services\ReceivedPaymentService;

class ReceivedPaymentController extends Controller
{
    protected $service;

    public function __construct(ReceivedPaymentService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $payments = $this->service->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id'        => Module::where('name', 'payment_received')->first()?->id,
                'table_view_id'    => TableView::fromName('payment_received'),
                'payment_received' => $payments,
            ],
        ], 200);
    }

    public function getContactsWithUnpaidInvoices(Request $request)
    {
        $contacts = $this->service->getContactsWithUnpaidInvoices($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'contacts' => $contacts,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReceivedPaymentRequest $request)
    {
        $request->validated();
        try {
            $payment_received = $this->service->create($request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'payment_received' => new ReceivedPaymentResource($payment_received),
                ],
                'message' => 'Successfully saved',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function recordPaymentForSpecificInvoice(PaymentForSpecificInvoiceRequest $request, $invoiceId)
    {
        $request->merge([
            'invoice_id' => $invoiceId,
        ]);

        $this->service->recordPaymentForSpecificInvoice($request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
            ],
            'message' => 'Successfully saved',
        ], 201);

    }

    public function updatePaymentForSpecificInvoice(PaymentForSpecificInvoiceRequest $request, $invoiceId, $paymentId)
    {
        $request->merge([
            'invoice_id'         => $invoiceId,
            'invoice_payment_id' => $paymentId,
        ]);

        $this->service->updatePaymentForSpecificInvoice($request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
            ],
            'message' => 'Successfully updated',
        ], 200);

    }

    public function refundPaymentForSpecificInvoice(RefundPaymentRequest $request, $invoiceId, $paymentId)
    {
        $request->merge([
            'invoice_id' => $invoiceId,
            'payment_id' => $paymentId,
        ]);

        $this->service->refundPaymentForSpecificInvoice($request->all());

        return response()->json([
            'status'  => true,
            'data'    => [
            ],
            'message' => 'Successfully saved',
        ], 200);

    }

    public function deletePaymentForSpecificInvoice($invoiceId, $paymentId)
    {
        $this->service->recordPaymentForSpecificInvoice($paymentId);

        return response()->json([], 204);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment_received = $this->service->get($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'payment_received' => new ReceivedPaymentResource($payment_received),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReceivedPaymentRequest $request, $id)
    {
        $request->validated();
        try {
            $invoice = $this->service->update($id, $request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    'invoice' => new ReceivedPaymentResource($invoice),
                ],
                'message' => 'Successfully updated',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function refund(RefundPaymentRequest $request, $id)
    {
        $request->validated();
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

    public function void(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        try {
            $invoice = $this->service->refund($id, $request->all());

            return response()->json([
                'status'  => true,
                'data'    => [
                    // 'invoice' => new ReceivedPaymentResource($invoice),
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

    public function attachFile($id, Request $request)
    {
        $data = $request->validate([
            'file_id' => 'required|exists:files,id',
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

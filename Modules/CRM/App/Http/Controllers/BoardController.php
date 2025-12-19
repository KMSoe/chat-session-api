<?php
namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\CRM\App\Http\Requests\BoardStatusChangeRequest;
use Modules\CRM\App\Services\BoardService;

class BoardController extends Controller
{
    protected $service;

    public function __construct(BoardService $service)
    {
        $this->service = $service;
    }

    public function boardStatusChange(BoardStatusChangeRequest $request)
    {
        $data = $request->validated();

        try {
            $this->service->boardStatusChange($data);

            return response()->json([
                'status'  => true,
                'message' => 'Quotation status updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }   
}

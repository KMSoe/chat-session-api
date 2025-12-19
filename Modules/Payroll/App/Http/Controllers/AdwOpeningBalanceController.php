<?php
namespace Modules\Payroll\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\App\Exports\AdwOpeningBalanceExport;
use Modules\Payroll\App\Http\Requests\AdwOpeningBalanceRequest;
use Modules\Payroll\App\Imports\AdwOpeningBalanceImport;
use Modules\Payroll\App\resources\AdwOpeningBalanceResource;
use Modules\Payroll\App\Services\AdwOpeningBalanceService;

class AdwOpeningBalanceController extends Controller
{
    protected $adwOpeningBalanceService;

    public function __construct(AdwOpeningBalanceService $adwOpeningBalanceService)
    {
        $this->adwOpeningBalanceService = $adwOpeningBalanceService;
    }

    public function index(Request $request)
    {
        $adw_opening_balances = $this->adwOpeningBalanceService->findbyParams($request->all());

        if ($request->export) {
            $format = strtolower($request->format) ?? 'excel';
            switch ($format) {
                case 'excel':
                    return Excel::download(new AdwOpeningBalanceExport($adw_opening_balances), 'adw_opening_balances.xlsx');
                    break;
                case 'csv':
                    return Excel::download(new AdwOpeningBalanceExport($adw_opening_balances), 'adw_opening_balances.csv');
                    break;
                default:
                    return Excel::download(new AdwOpeningBalanceExport($adw_opening_balances), 'adw_opening_balances.xlsx');
                    break;
            }
        }

        return response()->json([
            'status'  => true,
            'data'    => [
                'table_view_id'        => TableView::ADW_OPENING_BALANCE->value,
                'adw_opening_balances' => $adw_opening_balances,
            ],
            'message' => '',
        ], 200);
    }

    public function show($id)
    {
        $adw_opening_balance = $this->adwOpeningBalanceService->findById($id);

        return response()->json([
            'status'  => true,
            'data'    => [
                'adw_opening_balance' => new AdwOpeningBalanceResource($adw_opening_balance),
            ],
            'message' => '',
        ], 200);
    }

    public function store(AdwOpeningBalanceRequest $request)
    {
        $adw_opening_balance = $this->adwOpeningBalanceService->create($request->validated());

        return response()->json([
            'status'  => true,
            'data'    => [
                'adw_opening_balance' =>  new AdwOpeningBalanceResource($this->adwOpeningBalanceService->findById($adw_opening_balance->id)),
            ],
            'message' => 'Saved',
        ], 201);
    }

    public function update(AdwOpeningBalanceRequest $request, $id)
    {
        $this->adwOpeningBalanceService->update($id, $request->validated());

        return response()->json([
            'status'  => true,
            'data'    => [
                'adw_opening_balance' =>  new AdwOpeningBalanceResource($this->adwOpeningBalanceService->findById($id)),
            ],
            'message' => 'Updated',
        ], 201);
    }

    public function destroy($id)
    {
        $this->adwOpeningBalanceService->delete($id);
        return response()->json([], 204);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:adw_opening_balances,id',
        ]);

        $this->adwOpeningBalanceService->bulkDelete($request->ids);

        return response()->json([], 204);
    }

     public function import(Request $request)
    {
        // $request->validate([
        //     'file' => 'required|mimes:xlsx,csv',
        // ], [
        //     'file' => "The file is required with excel(xlsx) or csv format",
        // ]);

        $import = new AdwOpeningBalanceImport($this->adwOpeningBalanceService);
        Excel::import($import, $request->file('file'));

        $failures = $import->failures();

        if ($failures->isNotEmpty()) {
            $field_messages = [];

            foreach ($failures as $failure) {
                $row       = $failure->row();
                $attribute = $failure->attribute();
                $messages  = $failure->errors();
                $value     = $failure->values()[$attribute] ?? '[unknown]';

                foreach ($messages as $msg) {
                    $key = $msg;
                    if (! isset($field_messages[$attribute][$key])) {
                        $field_messages[$attribute][$key] = [];
                    }
                    $field_messages[$attribute][$key][] = "$value of row $row";
                }
            }

            $error_messages = [];

            foreach ($field_messages as $attribute => $message_group) {
                foreach ($message_group as $base_message => $entries) {
                    $entries = array_unique($entries);

                    if (count($entries) > 1) {
                        $last   = array_pop($entries);
                        $joined = implode(', ', $entries) . ' and ' . $last;
                    } else {
                        $joined = $entries[0];
                    }

                    $error_messages[$attribute][] = "[$joined] — $base_message";
                }
            }

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $error_messages,
            ], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => "Successfully imported",
        ], 200);
    }

    public function downloadSampleExcelFile()
    {
        $file = public_path('sample_import_data/adw_opening_balance_import_sample.xlsx');

        return response()->download($file);
    }
}

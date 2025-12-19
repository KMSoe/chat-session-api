<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\App\Exports\CompanyExport;
use Modules\CRM\App\Http\Requests\CompanyFormRequest;
use Modules\CRM\App\Http\Resources\CompanyCommentResource;
use Modules\CRM\App\Http\Resources\CompanyResource;
use Modules\CRM\App\Imports\CompanyImport;
use Modules\CRM\App\Services\CompanyService;

class CompanyController extends Controller
{
    protected $service;

    public function __construct(CompanyService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companies = $this->service->paginate($request->all());

        if ($request->export) {
            $format = strtolower($request->format) ?? 'excel';
            switch ($format) {
                case 'excel':
                    return Excel::download(new CompanyExport($companies), 'companies.xlsx');
                    break;
                case 'csv':
                    return Excel::download(new CompanyExport($companies), 'companies.csv');
                    break;
                default:
                    return Excel::download(new CompanyExport($companies), 'companies.xlsx');
                    break;
            }
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id' => Module::where('name', 'company')->first()?->id,
                'table_view_id' => TableView::fromName('company'),
                'companies'      => $companies,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyFormRequest $request)
    {
        $request->validated();
        try {
            $company = $this->service->create($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'company' => new CompanyResource($company),
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
        $company = $this->service->get($id);
        return response()->json([
            'status' => true,
            'data'   => [
                'company' => new CompanyResource($company),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompanyFormRequest $request, $id)
    {
        $request->validated();
        try {
            $company = $this->service->update($id, $request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'company' => new CompanyResource($company),
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

    public function bulkDelete(Request $request) {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:companies,id'
        ]);
        try {
            $this->service->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function import(Request $request)
    {
        // $request->validate([
        //     'file' => 'required|mimes:xlsx,csv',
        // ], [
        //     'file' => "The file is required with excel(xlsx) or csv format",
        // ]);

        $import = new CompanyImport($this->service);
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
        $file = public_path('sample_import_data/company_import_sample.xlsx');

        return response()->download($file);
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
                    'comment' => new CompanyCommentResource($comment),
                ],
                'message' => 'Comment added successfully',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateComment($companyId, $commentId, Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        try {
            $comment = $this->service->updateComment($companyId, $commentId, $data);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'comment' => new CompanyCommentResource($comment),
                ],
                'message' => 'Comment updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteComment($companyId, $commentId)
    {
        try {
            $this->service->deleteComment($companyId, $commentId);

            return response()->json([
                'status'  => true,
                'message' => 'Comment deleted successfully',
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateLogo($id, Request $request)
    {
        $data = $request->validate([
            'logo_file_id' => 'required|exists:files,id',
        ]);

        try {
            $company = $this->service->update($id, $data);

            return response()->json([
                'status' => true,
                'data'   => [
                    'company' => new CompanyResource($company),
                ],
                'message' => 'Logo updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

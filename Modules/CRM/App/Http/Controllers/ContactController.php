<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Enums\TableView;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\App\Exports\ContactExport;
use Modules\CRM\App\Http\Requests\ContactFormRequest;
use Modules\CRM\App\Http\Resources\ContactResource;
use Modules\CRM\App\Imports\ContactImport;
use Modules\CRM\App\Services\ContactService;

class ContactController extends Controller
{
    protected $service;

    public function __construct(ContactService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $contacts = $this->service->paginate($request->all());

        if ($request->export) {
            $format = strtolower($request->format) ?? 'excel';
            switch ($format) {
                case 'excel':
                    return Excel::download(new ContactExport($contacts), 'contacts.xlsx');
                    break;
                case 'csv':
                    return Excel::download(new ContactExport($contacts), 'contacts.csv');
                    break;
                default:
                    return Excel::download(new ContactExport($contacts), 'contacts.xlsx');
                    break;
            }
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'module_id' => Module::where('name', 'contact')->first()?->id,
                'table_view_id' => TableView::fromName('contact'),
                'contacts'      => $contacts,
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactFormRequest $request)
    {
        $request->validated();
        try {
            $contact = $this->service->create($request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'contact' => new ContactResource($contact),
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
        $contact = $this->service->get($id);
        return response()->json([
            'status' => true,
            'data'   => [
                'contact' => new ContactResource($contact),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactFormRequest $request, $id)
    {
        $request->validated();
        try {
            $contact = $this->service->update($id, $request->all());

            return response()->json([
                'status' => true,
                'data'   => [
                    'contact' => new ContactResource($contact),
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
            'ids.*' => 'exists:contacts,id'
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

        $import = new ContactImport($this->service);
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
        $file = public_path('sample_import_data/contact_import_sample.xlsx');

        return response()->download($file);
    }

    public function updatePassword(Request $request, $id)
    {
        $data = $request->validate([
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);

        try {
            $company = $this->service->updatePassword($id, $data);

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

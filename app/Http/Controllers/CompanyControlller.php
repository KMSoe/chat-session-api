<?php
namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Modules\Storage\App\Models\File;

class CompanyControlller extends Controller
{
    public function show()
    {
        $company = Company::with(['logoFile'])->first();

        return response()->json([
            'status'  => true,
            'data'    => [
                'company' => new CompanyResource($company),
            ],
            'message' => '',
        ], 200);
    }

    public function storeOrUpdate(CompanyRequest $request, $id = null)
    {
        $company = Company::first();

        if (! $company) {
            $company = Company::create($request->validated());
        }

        if ($request->logo_file_id != $company->logo_file_id) {
            File::where('id', $company->logo_file_id)->delete();
        }

        Company::first()->update(
            $request->validated()
        );

        return response()->json([
            'status'  => true,
            'data'    => [
                'company' => new CompanyResource(Company::with(['logoFile'])->first()),
            ],
            'message' => 'Success',
        ], 200);
    }
}

<?php
namespace App\Http\Controllers;

use App\Enums\CacheKeys;
use App\Enums\TableView;
use App\Http\Requests\ModuleAssignRequest;
use App\Http\Requests\ModuleFormRequest;
use App\Http\Resources\ModuleResource;
use App\Http\Services\ApproverService;
use App\Models\Module;
use App\Repositories\ModuleRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\Currency;
use Nnjeim\World\Models\State;

class ModuleController extends Controller
{
    protected $moduleRepo;

    public function __construct(ModuleRepo $moduleRepo)
    {
        $this->moduleRepo = $moduleRepo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->moduleRepo->paginate($request->all());

        return response()->json([
            'status' => true,
            'data'   => [
                'table_view_id' => TableView::fromName('module'),
                'modules'       => $modules,
            ],
        ], 200);
    }

    public function getModulesWithPermissions()
    {
        return response()->json([
            'status'  => true,
            'data'    => [
                'modules' => Module::with(['children.allPermissions'])->whereNull('parent_module_id')->get(),
            ],
            'message' => '',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ModuleFormRequest $request)
    {
        $request->validated();
        try {
            $module = $this->moduleRepo->create($request->all());
            return response()->json([
                'status' => true,
                'data'   => [
                    'module' => new ModuleResource($module),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $module = $this->moduleRepo->get($id);
        return response()->json([
            'status' => true,
            'data'   => [
                'module' => new ModuleResource($module),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ModuleFormRequest $request, $id)
    {
        $request->validated();
        try {
            $module = $this->moduleRepo->update($id, $request->all());
            return response()->json([
                'status' => true,
                'data'   => [
                    'module' => new ModuleResource($module),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->moduleRepo->delete($id);
            return response()->json([
                'status'  => true,
                'message' => "Successfully deleted",
            ], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function getAssociatedAttributes($module)
    {
        $attributes = $this->moduleRepo->getAssociatedAttributes($module);
        return response()->json([
            'status' => true,
            'data'   => [
                'attributes' => $attributes,
            ],
        ], 200);
    }

    public function assignAssociatedAttributes($module, ModuleAssignRequest $request)
    {
        try {
            $this->moduleRepo->assignAssociatedAttributes($module, $request->input('attribute_ids'));
            return response()->json([
                'status'  => true,
                'message' => "Attributes assigned successfully",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:modules,id',
        ]);
        try {
            $this->moduleRepo->bulkDelete($request->ids);
            return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }

    public function getApprovalModules()
    {
        $modules = app(ApproverService::class)->getApprovalModules();

        return response()->json([
            'status' => true,
            'data'   => [
                'modules' => $modules,
            ],
        ], 200);
    }

    public function getAllCountries()
    {
        $countries = Cache::remember(CacheKeys::COUNTRIES->name, 3600, function () {
            return Country::all();
        });

        return response()->json([
            'status' => true,
            'data'   => [
                'countries' => $countries,
            ],
        ], 200);
    }

    public function getAllCities(Request $request)
    {
        $cities = City::query();

        if (isset($request->state_id)) {
            $cities->where('state_id', $request->state_id);
        }

        if (isset($request->country_id)) {
            $cities->where('country_id', $request->country_id);
        }

        $cities = $cities->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'cities' => $cities,
            ],
        ], 200);
    }

    public function getAllStates(Request $request)
    {
        $states = State::query();

        if (isset($request->country_id)) {
            $states->where('country_id', $request->country_id);
        }

        $states = $states->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'states' => $states,
            ],
        ], 200);
    }

    public function getAllCurrencies()
    {
        $currencies = Cache::remember(CacheKeys::CURRENCIES->name, 3600, function () {
            return Currency::all();
        });

        return response()->json([
            'status' => true,
            'data'   => [
                'currencies' => $currencies,
            ],
        ], 200);
    }

    public function getCodePrefixes($module)
    {
        $codePrefix = $this->moduleRepo->getCodePrefix($module);

        return response()->json([
            'status' => true,
            'data'   => [
                'code_prefix' => $codePrefix,
            ],
        ], 200);
    }

    public function updateCodePrefix(Request $request, $module)
    {
        try {
            $this->moduleRepo->updateCodePrefix($module, $request->all());
            return response()->json([
                'status'  => true,
                'message' => "Code prefix updated successfully",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false], Response::HTTP_OK);
        }
    }
}

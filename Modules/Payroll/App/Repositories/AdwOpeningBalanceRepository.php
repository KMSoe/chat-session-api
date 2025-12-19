<?php
namespace Modules\Payroll\App\Repositories;

use Illuminate\Support\Facades\Auth;
use Modules\Payroll\App\Models\AdwOpeningBalance;
use Modules\Payroll\App\resources\AdwOpeningBalanceResource;

class AdwOpeningBalanceRepository
{
    public function findByParams(array $params)
    {
        $perPage = $request['per_page'] ?? 20;

        $data = AdwOpeningBalance::with(['employee:id,name', 'createdBy:id,name'])
            ->whereHas('employee', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->where(function ($query) use ($params) {
                if (isset($params['search'])) {
                    $query->whereHas('employee', function ($q) use ($params) {
                        $q->where('name', 'like', '%' . $params['search'] . '%');
                    });
                }
            });

        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $params['only_this_page'] == 1
                ? $data->skip(($params['page'] - 1) * $perPage)->take($perPage)->get()
                : $data->get();

            return $items;
        }

        $data = $data->paginate($perPage);

        $items = $data->getCollection()->map(function ($item) {
            return new AdwOpeningBalanceResource($item);
        });

        return $data->setCollection($items);

    }

    public function findById(int $id)
    {
        return AdwOpeningBalance::with(['employee:id,name', 'createdBy:id,name'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id;

        return AdwOpeningBalance::updateOrCreate([
            'employee_id' => $data['employee_id'],
        ], $data);
    }

    public function update($id, array $data): bool
    {
        $data['updated_by'] = Auth::guard('api')->user()->id;

        $adwOpeningBalance = AdwOpeningBalance::findOrFail($id);
        return $adwOpeningBalance->update($data);
    }

    public function delete($id): ?bool
    {
        $adwOpeningBalance = AdwOpeningBalance::findOrFail($id);
        return $adwOpeningBalance->delete();
    }

    public function bulkDelete(array $ids)
    {
        return AdwOpeningBalance::whereIn('id', $ids)->delete();
    }
}

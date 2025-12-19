<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Payroll\App\Models\Benefit\MPFScheme;
use Modules\Payroll\App\resources\MPFSchemeResource;

class MPFSchemeRepository
{
    public function findByParams($params)
    {
        $per_page = isset($params['per_page']) ? intval($params['per_page']) : 20;

        $query = MPFScheme::with(['trustee', 'payrollComponents']);

        if (isset($params['is_active'])) {
            $query->where('is_active', filter_var($params['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
            });
        }

        $data = $query
            ->orderByDesc('created_at')
            ->paginate($per_page);

        $items = $data->getCollection();

        $items = collect($items)->map(function ($item) {
            return new MPFSchemeResource($item);
        });

        $data = $data->setCollection($items);

        return $data;
    }

    public function findById($id)
    {
        return MPFScheme::with(['trustee', 'payrollComponents'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $scheme = MPFScheme::create($data);

        if (! empty($data['payroll_component_ids'])) {
            $scheme->payrollComponents()->sync($data['payroll_component_ids']);
        }

        return $scheme;
    }

    public function update($id, array $data)
    {
        $scheme = MPFScheme::findOrFail($id);
        $scheme->update($data);

        if (isset($data['payroll_component_ids'])) {
            $scheme->payrollComponents()->sync($data['payroll_component_ids']);
        }

        return $scheme;
    }

    public function delete($id)
    {
        $scheme = MPFScheme::findOrFail($id);
        return $scheme->delete();
    }

    public function bulkDelete(array $ids)
    {
        return MPFScheme::whereIn('id', $ids)->delete();
    }
}

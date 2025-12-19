<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Payroll\App\Models\PayrollPolicy;
use Modules\Payroll\App\resources\PayrollPolicyResource;

class PayrollPolicyRepository
{
    public function findByParams($params)
    {
        $query    = PayrollPolicy::query();
        $per_page = isset($params['per_page']) ? intval($params['per_page']) : 20;

        // if (! empty($params['pay_frequency']) && strtolower($params['pay_frequency']) !== 'all') {
        //     $query->where('pay_frequency', $params['pay_frequency']);
        // }

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
            return new PayrollPolicyResource($item);
        });

        $data = $data->setCollection($items);

        return $data;
    }

    public function findById(int $id): ?PayrollPolicy
    {
        return PayrollPolicy::findOrFail($id);
    }

    /**
     * Create a new policy.
     */
    public function create(array $data): PayrollPolicy
    {
        return PayrollPolicy::create($data);
    }

    /**
     * Update existing policy.
     */
    public function update(int $id, array $data): ?PayrollPolicy
    {
        $policy = $this->findById($id);
        if ($policy) {
            $policy->update($data);
        }

        return $policy;
    }

    /**
     * Delete policy.
     */
    public function delete(int $id): bool
    {
        $policy = $this->findById($id);
        return $policy ? (bool) $policy->delete() : false;
    }

    public function bulkDelete(array $ids)
    {
        return PayrollPolicy::whereIn('id', $ids)->delete();
    }
}

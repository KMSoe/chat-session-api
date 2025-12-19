<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Payroll\App\Models\Benefit\MPFTrustee;

class MPFTrusteeRepository
{
    public function findByParams(array $params)
    {
        $per_page = isset($params['per_page']) ? intval($params['per_page']) : 20;

        $query = MPFTrustee::query();

        if (isset($params['is_active'])) {
            $query->where('is_active', filter_var($params['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        return $query->paginate($per_page);
    }

    public function findById(int $id): ?MPFTrustee
    {
        return MPFTrustee::findOrFail($id);
    }

    public function create(array $data): MPFTrustee
    {
        return MPFTrustee::create($data);
    }

    public function update(int $id, array $data): ?MPFTrustee
    {
        $trustee = $this->findById($id);
        if ($trustee) {
            $trustee->update($data);
        }
        return $trustee;
    }

    public function delete(int $id): bool
    {
        $trustee = $this->findById($id);
        return $trustee ? (bool) $trustee->delete() : false;
    }
}

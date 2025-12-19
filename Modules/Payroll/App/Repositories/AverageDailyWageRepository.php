<?php
namespace Modules\Payroll\App\Repositories;

use Modules\Payroll\App\Models\AverageDailyWage;

class AverageDailyWageRepository
{
    public function findFirst(): ?AverageDailyWage
    {
        return AverageDailyWage::with(['payrollComponents', 'excludedLeaveTypes'])
            ->first();
    }

    public function createOrUpdate(array $data): AverageDailyWage
    {
        $averageDailyWage = AverageDailyWage::first() ?? new AverageDailyWage();
        $averageDailyWage->fill($data);
        $averageDailyWage->save();

        if (isset($data['payroll_components'])) {
            $averageDailyWage->payrollComponents()->sync($data['payroll_components']);
        }

        if (isset($data['exclude_leave_types'])) {
            $averageDailyWage->excludedLeaveTypes()->sync($data['exclude_leave_types']);
        }

        if(isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0 )
        {
            $this->createApplicableTo($averageDailyWage, $data);
        }

        return $averageDailyWage;
    }

    protected function createApplicableTo(AverageDailyWage $averageDailyWage, array $data): void
    {
        $averageDailyWage->applicableTos()->delete();

        foreach ($data['applicable_to'] as $applicable) {
            $averageDailyWage->applicableTos()->create([
                'scope' => $applicable['scope'],
                'target_id' => $applicable['target_id'],
                'include_children' => $applicable['include_children'] ?? true,
            ]);
        }
    }
}

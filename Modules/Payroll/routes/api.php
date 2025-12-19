<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\App\Http\Controllers\AdwOpeningBalanceController;
use Modules\Payroll\App\Http\Controllers\AverageDailyWageController;
use Modules\Payroll\App\Http\Controllers\EmployeeEnrollmentController;
use Modules\Payroll\App\Http\Controllers\EmployeePayrollComponentController;
use Modules\Payroll\App\Http\Controllers\EmployeeResidenceController;
use Modules\Payroll\App\Http\Controllers\EmployeeTaxCalculationController;
use Modules\Payroll\App\Http\Controllers\EmployeeTaxFileController;
use Modules\Payroll\App\Http\Controllers\MPFSchemeController;
use Modules\Payroll\App\Http\Controllers\MPFTrusteeController;
use Modules\Payroll\App\Http\Controllers\ORSOSchemaController;
use Modules\Payroll\App\Http\Controllers\PayrollComponentController;
use Modules\Payroll\App\Http\Controllers\PayrollController;
use Modules\Payroll\App\Http\Controllers\PayrollPolicyController;
use Modules\Payroll\App\Http\Controllers\PayrollSlipTemplateController;
use Modules\Payroll\App\Http\Controllers\TaxCalculationController;
use Modules\Payroll\App\Http\Controllers\TaxFormController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::prefix('/v1')->middleware(['auth:api'])->group(function () {
    Route::resource('payroll-components', PayrollComponentController::class);
    Route::get('payroll-components-page-data', [PayrollComponentController::class, 'getPageData'])->name('payroll-components.page-data');

    Route::get('employee-payroll-components', [EmployeePayrollComponentController::class, 'index']);
    Route::put('employee-payroll-components', [EmployeePayrollComponentController::class, 'updateEmployeePayrollComponents']);

    Route::prefix('tax')->group(function () {
        Route::get('forms', [TaxFormController::class, 'index'])->name('forms.index');
        Route::get('forms/{id}', [TaxFormController::class, 'show'])->name('forms.show');
        Route::patch('forms/{id}/status', [TaxFormController::class, 'updateStatus'])->name('forms.updateStatus');
        Route::get('forms/{id}/income-categories/{income_category_id}', [TaxFormController::class, 'getIncomeCategoryById'])->name('forms.income_categories.show');
        Route::put('forms/{id}/income-categories/{income_category_id}', [TaxFormController::class, 'updateCategoryComponents'])->name('forms.income_categories.update');

        Route::get('employee-files', [EmployeeTaxFileController::class, 'index'])->name('employee-files.index');
        Route::get('employee-files/{employee_id}', [EmployeeTaxFileController::class, 'show'])->name('employee-files.show');
        Route::get('employee-files/{employee_id}/tax-filing-records', [EmployeeTaxFileController::class, 'getFilingRecords'])->name('employee-files.filing-records');
        Route::post('employee-files/{employee_id}', [EmployeeTaxFileController::class, 'store'])->name('employee-files.store');
        Route::resource('employee-files/{employee_id}/residences', EmployeeResidenceController::class);

        Route::resource('calculations', TaxCalculationController::class);
        Route::patch('calculations/{id}/status', [TaxCalculationController::class, 'updateStatus'])->name('calculations.status.update');
        Route::post('calculations/{id}/recalculate', [TaxCalculationController::class, 'recalculate'])->name('calculations.recalculate');
        Route::resource('calculations/{id}/employees', EmployeeTaxCalculationController::class);
        Route::delete('calculations/{id}/employee/bulk-delete', [EmployeeTaxCalculationController::class, 'bulkDelete']);
        Route::patch('calculations/{tax_calculation_id}/employees/{employee_id}/lock-status', [EmployeeTaxCalculationController::class, 'updateLockStatus'])->name('calculationss.employees.lock');
        Route::get('calculations/{tax_calculation_id}/employees/{employee_id}/income-details', [EmployeeTaxCalculationController::class, 'getIncomeDetails'])->name('calculationss.employees.income-details');
        Route::put('calculations/{tax_calculation_id}/employees/{employee_id}/income-details', [EmployeeTaxCalculationController::class, 'updateIncomeDetails'])->name('calculationss.employees.income-details.update');
        Route::get('calculations/{tax_calculation_id}/employees/{employee_id}/residential-address-details', [EmployeeTaxCalculationController::class, 'getResidentialDetails'])->name('calculationss.employees.residentail-address-details');
        Route::get('calculations/{tax_calculation_id}/employees/{employee_id}/other-details', [EmployeeTaxCalculationController::class, 'getOtherDetails'])->name('calculationss.employees.other-details');
        Route::put('calculations/{tax_calculation_id}/employees/{employee_id}/other-details', [EmployeeTaxCalculationController::class, 'updateOtherDetails'])->name('calculationss.employees.other-details.update');
    });

    Route::resource('benefit/mpf-trustee', MPFTrusteeController::class);
    Route::resource('benefit/mpf-schemes', MPFSchemeController::class);
    Route::delete('benefit/mpf-scheme/bulk-delete', [MPFSchemeController::class, 'bulkDelete']);
    Route::resource('benefit/orso-schemes', ORSOSchemaController::class);
    Route::delete('benefit/orso-scheme/bulk-delete', [ORSOSchemaController::class, 'bulkDelete']);
    Route::get('benefit/employee-enrollments', [EmployeeEnrollmentController::class, 'index'])->name('benefit/employee-enrollments.index');
    Route::get('benefit/employee-enrollments/{employee_id}', [EmployeeEnrollmentController::class, 'show'])->name('benefit/employee-enrollments.show');
    Route::post('benefit/employee-enrollments/{employee_id}', [EmployeeEnrollmentController::class, 'store'])->name('benefit/employee-enrollments.store');

    Route::resource('payroll-settings/payroll-policies', PayrollPolicyController::class);
    Route::delete('payroll-settings/payroll-policy/bulk-delete', [PayrollPolicyController::class, 'bulkDelete']);
    Route::resource('payroll-settings/payrollslip-templates', PayrollSlipTemplateController::class);
    Route::get('payroll-settings/payrollslip-templates-form-data', [PayrollSlipTemplateController::class, 'getFormData']);
    Route::delete('payroll-settings/payrollslip-template/bulk-delete', [PayrollSlipTemplateController::class, 'bulkDelete']);

    Route::get('payroll-settings/average-daily-wage', [AverageDailyWageController::class, 'getFirst'])->name('payroll-settings.average_daily_wage.get');
    Route::post('payroll-settings/average-daily-wage', [AverageDailyWageController::class, 'store'])->name('payroll-settings.average_daily_wage.store');
    Route::resource('payroll-settings/adw-opening-balances', AdwOpeningBalanceController::class);
    Route::delete('payroll-settings/adw-opening-balance/bulk-delete', [AdwOpeningBalanceController::class, 'bulkDelete']);
    Route::post('payroll-settings/adw-opening-balance/import', [AdwOpeningBalanceController::class, 'import'])->name('payroll-settings.adw-opening-balances.import');
    Route::get('payroll-settings/adw-opening-balances-sample-download', [AdwOpeningBalanceController::class, 'downloadSampleExcelFile']);

    Route::get('payrolls-form-data', [PayrollController::class, 'getPageData']);
    Route::prefix('payrolls')->group(function () {
        Route::get('/', [PayrollController::class, 'index']);
        Route::get('/{id}', [PayrollController::class, 'show']);
        Route::post('/', [PayrollController::class, 'store']);
        Route::patch('{id}/lock', [PayrollController::class, 'lock']);
        Route::patch('{id}/unlock', [PayrollController::class, 'unlock']);
        Route::post('{id}/generate', [PayrollController::class, 'generateSlip']);
        Route::get('{id}/preview', [PayrollController::class, 'previewSlip']);
        Route::get('{id}/download', [PayrollController::class, 'downloadSlip']);
        Route::post('{id}/send', [PayrollController::class, 'sendSlip']);
    });

    Route::post('recalculate-payrolls', [PayrollController::class, 'recalculateMultiplePayrolls']);
    Route::post('lock-payrolls', [PayrollController::class, 'lockMultiplePayrolls']);
    Route::post('unlock-payrolls', [PayrollController::class, 'unlockMultiplePayrolls']);
    Route::post('generate-payrolls', [PayrollController::class, 'generateMultiplePayrolls']);
    Route::post('send-payrolls', [PayrollController::class, 'sendMultiplePayrolls']);

    // testing
    Route::post('testGorssSalary', [PayrollController::class, 'testGorssSalary']);
    Route::post('testBasicSalary', [PayrollController::class, 'testBasicSalary']);
});

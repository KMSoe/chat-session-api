<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\WebAuthnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

Route::get('/test', function () {
    return "API SERVER STARTED";
});
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});
require __DIR__ . '/auth.php';

Route::get('/webauth', function () {
    return view('registration');
});

use Modules\Storage\App\Classes\ObjectStorage;

// Registration routes
Route::get('/webauthn/register/options', [WebAuthnController::class, 'registerOptions'])->name('webauthn.register.options');
Route::post('/webauthn/register', [WebAuthnController::class, 'register'])->name('webauthn.register');

// Authentication routes
Route::get('/webauthn/login/options', [WebAuthnController::class, 'loginOptions'])->name('webauthn.login.options');
Route::post('/webauthn/login', [WebAuthnController::class, 'login'])->name('webauthn.login');

Route::get('uploads/{any}', function ($any) {
    $storage = new ObjectStorage();
    $path    = $any;

    if ($path && $storage->checkFileExists($path)) {
        return $storage->getFileAsResponse($path);
    }
})->where('any', '.*');

// Route::get('payrolls', [PayrollController::class, 'index']);
// Route::get('employee-payrolls', [EmployeePayrollComponentController::class, 'index']);
// Route::get('employee-tax-files', [EmployeeTaxFileController::class, 'index']);

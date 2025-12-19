<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\GeneralSettingController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeSetController;
use App\Http\Controllers\CompanyControlller;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('/v1')->name('api.auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Forgot Pasword
    Route::post('password/forgot', [AuthController::class, 'forgotPassword']);

    // Reset Password
    // Route::get('password/reset/{token}', [AuthController::class, 'checkResetPasswordToken']);
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
});

Route::middleware(['auth:api', 'enable'])->prefix('/v1')->group(function () {
    Route::get('/me', [AuthController::class, 'getAuthUser']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::apiResource('permissions', PermissionController::class);
    Route::post('permissions/all', [PermissionController::class, 'getAll']);
    Route::post('permissions/{id}/change-status', [PermissionController::class, 'changeStatus']);

    Route::post('test-permissions', [AuthController::class, 'testPermission']);

    Route::apiResource('roles', RoleController::class);
    Route::get('module-permissions', [ModuleController::class, 'getModulesWithPermissions']);
    Route::get('me/permissions', [RoleController::class, 'getMyPermssions']);
    Route::post('roles/all', [RoleController::class, 'getAll']);
    Route::delete('role/bulk-delete', [RoleController::class, 'bulkDelete']);

    Route::apiResource('users', UserController::class);
    Route::post('users/all', [UserController::class, 'getAll']);
    Route::delete('user/bulk-delete', [UserController::class, 'bulkDelete']);

    Route::apiResource('modules', ModuleController::class);
    Route::delete('module/bulk-delete', [ModuleController::class, 'bulkDelete']);
    Route::get('modules/{module}/attributes', [ModuleController::class, 'getAssociatedAttributes']);
    Route::post('modules/{module}/attributes', [ModuleController::class, 'assignAssociatedAttributes']);
    Route::apiResource('attributes', AttributeController::class);
    Route::delete('attribute/bulk-delete', [AttributeController::class, 'bulkDelete']);
    Route::get('attribute-types', [AttributeController::class, 'attributeTypes']);
    Route::get('custom-properties/{module_name}', [AttributeController::class, 'getByModule']);
    Route::post('attributes/{id}/change-status', [AttributeController::class, 'changeStatus']);
    Route::apiResource('attribute-sets', AttributeSetController::class);
    Route::get('attribute-set/{module_name}', [AttributeSetController::class, 'getByModule']);

    Route::get('code-prefixes/{module}', [ModuleController::class, 'getCodePrefixes']);
    Route::patch('code-prefixes/{module}', [ModuleController::class, 'updateCodePrefix']);

    Route::post('get-original-file', [AttributeController::class, 'getOriginalFile']);
    Route::post('get-original-html', [AttributeController::class, 'getOriginalHTML']);
    Route::get('approval-modules', [ModuleController::class, 'getApprovalModules']);
    Route::get('countries', [ModuleController::class, 'getAllCountries']);
    Route::get('cities', [ModuleController::class, 'getAllCities']);
    Route::get('states', [ModuleController::class, 'getAllStates']);
    Route::get('currencies', [ModuleController::class, 'getAllCurrencies']);

    Route::post('/upload', [FileUploadController::class, 'upload']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/company', [CompanyControlller::class, 'show']);
    Route::post('/company', [CompanyControlller::class, 'storeOrUpdate']);
});

Route::prefix('/v1')->group(function () {
    Route::get('payment-status', [ConfigController::class, 'getPaymentStatus']);
    Route::get('order-status', [ConfigController::class, 'getOrderStatus']);
    Route::get('purchase-order-status', [ConfigController::class, 'getPurchaseOrderStatus']);
    Route::get('purchase-received-status', [ConfigController::class, 'getPurchaseReceivedStatus']);
    Route::get('payment-methods', [ConfigController::class, 'getPaymentMethod']);
    Route::get('sale-return-status', [ConfigController::class, 'getSaleReturnStatus']);
    Route::get('promotion-types', [ConfigController::class, 'getPromotionTypes']);
    Route::get('promotion-status', [ConfigController::class, 'getPromotionStatus']);
    Route::get('quality-status', [ConfigController::class, 'getQualityStatus']);

    Route::get('setting/{module}', [GeneralSettingController::class, 'show']);
    Route::put('setting/{module}', [GeneralSettingController::class, 'update']);
});


// Testing
Route::get('/v1/redis-test', [App\Http\Controllers\Api\RedisTestController::class, 'test']);
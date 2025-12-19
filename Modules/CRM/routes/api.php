<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\App\Http\Controllers\BoardController;
use Modules\CRM\App\Http\Controllers\CompanyController;
use Modules\CRM\App\Http\Controllers\ContactController;
use Modules\CRM\App\Http\Controllers\CreditNoteController;
use Modules\CRM\App\Http\Controllers\InvoiceController;
use Modules\CRM\App\Http\Controllers\ItemController;
use Modules\CRM\App\Http\Controllers\ItemTemplateController;
use Modules\CRM\App\Http\Controllers\ItemTypeController;
use Modules\CRM\App\Http\Controllers\PaymentTermController;
use Modules\CRM\App\Http\Controllers\ProjectController;
use Modules\CRM\App\Http\Controllers\QuotationController;
use Modules\CRM\App\Http\Controllers\ReceivedPaymentController;
use Modules\CRM\App\Http\Controllers\RecurringInvoiceController;
use Modules\CRM\App\Http\Controllers\StatusController;
use Modules\CRM\App\Http\Controllers\TaskController;
use Modules\CRM\App\Http\Controllers\TaxController;
use Modules\Storage\App\Http\Controllers\FileControllder;

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

Route::prefix('/v1/crm')->name('api.')->middleware(['auth:api', 'enable'])->group(function () {
    Route::resource('contacts', ContactController::class);
    Route::delete('contact/bulk-delete', [ContactController::class, 'bulkDelete']);
    Route::post('contact/import', [ContactController::class, 'import'])->name('contacts.import');
    Route::get('contacts-sample-download', [ContactController::class, 'downloadSampleExcelFile']);
    Route::patch('contact/{id}/update-password', [ContactController::class, 'updatePassword'])->name('contacts.update-password');

    Route::resource('companies', CompanyController::class);
    Route::patch('company/{id}/update-logo', [CompanyController::class, 'updateLogo'])->name('companies.update-logo');
    Route::delete('company/bulk-delete', [CompanyController::class, 'bulkDelete']);
    Route::post('company/import', [CompanyController::class, 'import'])->name('companies.import');
    Route::get('companies-sample-download', [CompanyController::class, 'downloadSampleExcelFile']);
    Route::get('company/{id}/comments', [CompanyController::class, 'getComments'])->name('companies.comments');
    Route::post('company/{id}/comments', [CompanyController::class, 'addComment'])->name('companies.comments.add');
    Route::patch('company/{id}/comments/{commentId}', [CompanyController::class, 'updateComment'])->name('companies.comments.update');
    Route::delete('company/{id}/comments/{commentId}', [CompanyController::class, 'deleteComment'])->name('companies.comments.delete');

    Route::resource('taxes', TaxController::class);
    Route::delete('tax/bulk-delete', [TaxController::class, 'bulkDelete']);

    Route::resource('payment-terms', PaymentTermController::class);
    Route::delete('payment-term/bulk-delete', [PaymentTermController::class, 'bulkDelete']);

    Route::resource('item-templates', ItemTemplateController::class);
    Route::delete('item-template/bulk-delete', [ItemTemplateController::class, 'bulkDelete']);

    Route::resource('projects', ProjectController::class);
    Route::delete('project/bulk-delete', [ProjectController::class, 'bulkDelete']);
    Route::get('project/{id}/board', [ProjectController::class, 'getProjectBoard'])->name('projects.board');
    Route::post('project/import', [ProjectController::class, 'import'])->name('projects.import');
    Route::get('projects-sample-download', [ProjectController::class, 'downloadSampleExcelFile']);
    Route::post('project/{id}/duplicate', [ProjectController::class, 'duplicateProject'])->name('projects.duplicate');
    Route::post('status-reorder', [ProjectController::class, 'updateStatusOrders'])->name('status-orders.update');

    Route::resource('tasks', TaskController::class);
    Route::delete('task/bulk-delete', [TaskController::class, 'bulkDelete']);
    Route::post('task/import', [TaskController::class, 'import'])->name('tasks.import');
    Route::get('tasks-sample-download', [TaskController::class, 'downloadSampleExcelFile']);
    Route::post('task/{id}/duplicate', [TaskController::class, 'duplicateTask'])->name('tasks.duplicate');
    Route::patch('task/{id}/update-task-position', [TaskController::class, 'updateTaskPosition'])->name('tasks.update-task-position');
    Route::get('task/{id}/activity-logs', [TaskController::class, 'getActivityLogs'])->name('tasks.activity-logs');
    Route::post('task/{id}/add-comment', [TaskController::class, 'addComment'])->name('tasks.comments.add');
    Route::patch('task/{id}/comment/{commentId}', [TaskController::class, 'updateComment'])->name('tasks.comments.update');
    Route::delete('task/{id}/comment/{commentId}', [TaskController::class, 'deleteComment'])->name('tasks.comments.delete');

    Route::get('project/{id}/statuses', [StatusController::class, 'index']);
    Route::post('project/{id}/statuses', [StatusController::class, 'store']);
    Route::patch('project/{id}/statuses/{statusId}', [StatusController::class, 'update']);
    Route::delete('project/{id}/status/bulk-delete', [StatusController::class, 'bulkDelete']);

    Route::get('project/{id}/task-statuses', [StatusController::class, 'getTaskStatuses']);
    Route::post('project/{id}/task-statuses', [StatusController::class, 'storeTaskStatus']);
    Route::patch('project/{id}/task-statuses/{taskStatusId}', [StatusController::class, 'updateTaskStatus']);
    Route::delete('project/{id}/task-statuses/bulk-delete', [StatusController::class, 'bulkDeleteTaskStatuses']);

    Route::get('status-settings', [StatusController::class, 'getStatusSettings']);
    Route::post('status-settings', [StatusController::class, 'storeStatusSetting']);
    Route::patch('status-settings/{id}', [StatusController::class, 'updateStatusSetting']);
    Route::delete('status-setting/bulk-delete', [StatusController::class, 'bulkDeleteStatusSettings']);

    Route::get('task/{id}/timer-status', [TaskController::class, 'getTimerStatus'])->name('tasks.timer-status');
    Route::post('task/{id}/start-end-timer', [TaskController::class, 'startEndTimer'])->name('tasks.start-end-timer');
    Route::get('task/{id}/time-logs', [TaskController::class, 'getTimeLogs'])->name('tasks.time-logs');
    Route::get('my-assigned-tasks', [TaskController::class, 'getMyAssignedTasks'])->name('tasks.my-assigned-tasks');
    Route::get('today-tasks', [TaskController::class, 'getTodayTasks'])->name('tasks.today-tasks');
    Route::post('daily-report', [TaskController::class, 'submitDailyReport'])->name('tasks.daily-report');
    Route::get('daily-reports', [TaskController::class, 'getDailyReports'])->name('tasks.daily-reports');
    Route::get('active-time-reports', [TaskController::class, 'getActiveTimeReports'])->name('tasks.active-time-reports');

    Route::resource('item-types', ItemTypeController::class);
    Route::delete('item-type/bulk-delete', [ItemTypeController::class, 'bulkDelete']);

    Route::resource('items', ItemController::class);
    Route::get('items-page-data', [ItemController::class, 'getPageData']);
    Route::delete('item/bulk-delete', [ItemController::class, 'bulkDelete']);

    Route::resource('quotations', QuotationController::class);
    Route::get('quotation-board', [QuotationController::class, 'getQuotationBoard'])->name('quotations.board');
    Route::post('quotations-approval-status-update', [QuotationController::class, 'approvalStatusUpdate']);
    Route::post('quotation/{id}/status-update', [QuotationController::class, 'quotationStatusUpdate']);
    Route::get('get-initial-quotation-data', [QuotationController::class, 'getInitialData']);
    Route::get('quotation/{id}/activity-logs', [QuotationController::class, 'getActivityLogs'])->name('quotations.activity-logs');
    Route::post('quotation/{id}/send', [QuotationController::class, 'sendMail'])->name('quotations.send');
    Route::get('quotation/{id}/comments', [QuotationController::class, 'getComments'])->name('quotations.comments');
    Route::post('quotation/{id}/add-comment', [QuotationController::class, 'addComment'])->name('quotations.comments.add');
    Route::patch('quotation/{id}/comment/{commentId}', [QuotationController::class, 'updateComment'])->name('quotations.comments.update');
    Route::delete('quotation/{id}/comment/{commentId}', [QuotationController::class, 'deleteComment'])->name('quotations.comments.delete');
    Route::get('quotation/{id}/get-shared-links', [QuotationController::class, 'getSharedLinks'])->name('quotations.get-shared-links');
    Route::post('quotation/{id}/generate-shared-link', [QuotationController::class, 'generateSharedLink'])->name('quotations.generate-shared-link');
    Route::patch('quotation/{id}/disable-shared-link', [QuotationController::class, 'disableSharedLink'])->name('quotations.disable-shared-link');
    Route::post('quotation/{id}/attach-file', [QuotationController::class, 'attachFile'])->name('quotations.attach-file');
    Route::delete('quotation/{id}/attach-file/{fileId}', [QuotationController::class, 'deleteAttachedFile'])->name('quotations.delete-attached-file');
    Route::post('quotation/{id}/sign-quotation', [QuotationController::class, 'signQuotation'])->name('quotations.sign-quotation');
    Route::get('quotation/{id}/download-pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.download-pdf');

    Route::resource('recurring-invoices', RecurringInvoiceController::class);
    Route::get('recurring-invoice/{id}/activity-logs', [RecurringInvoiceController::class, 'getActivityLogs'])->name('recurring-invoices.activity-logs');
    Route::get('recurring-invoice/{id}/get-generated-invoices', [RecurringInvoiceController::class, 'getGeneratedInvoices'])->name('recurring-invoices.get-generated-invoices');

    Route::get('quotation-status', [QuotationController::class, 'getStatuses'])->name('quotations.status');
    Route::get('invoice-status', [InvoiceController::class, 'getStatuses'])->name('invoices.status');

    Route::resource('invoices', InvoiceController::class);
    Route::get('invoice-board', [InvoiceController::class, 'getInvoiceBoard'])->name('invoices.board');
    Route::post('invoices-approval-status-update', [InvoiceController::class, 'approvalStatusUpdate']);
    Route::get('get-initial-invoice-data', [InvoiceController::class, 'getInitialData']);
    Route::get('invoice/{id}/activity-logs', [InvoiceController::class, 'getActivityLogs'])->name('invoices.activity-logs');
    Route::get('invoice/{id}/comments', [InvoiceController::class, 'getComments'])->name('invoices.comments');
    Route::post('invoice/{id}/add-comment', [InvoiceController::class, 'addComment'])->name('invoices.comments.add');
    Route::patch('invoice/{id}/comment/{commentId}', [InvoiceController::class, 'updateComment'])->name('quotations.comments.update');
    Route::delete('invoice/{id}/comment/{commentId}', [InvoiceController::class, 'deleteComment'])->name('quotations.comments.delete');
    Route::get('invoice/{id}/get-shared-links', [InvoiceController::class, 'getSharedLinks'])->name('quotations.get-shared-links');
    Route::post('invoice/{id}/generate-shared-link', [InvoiceController::class, 'generateSharedLink'])->name('quotations.generate-shared-link');
    Route::patch('invoice/{id}/disable-shared-link', [InvoiceController::class, 'disableSharedLink'])->name('quotations.disable-shared-link');
    Route::post('invoice/{id}/attach-file', [InvoiceController::class, 'attachFile'])->name('invoices.attach-file');
    Route::delete('invoice/{id}/attach-file/{fileId}', [InvoiceController::class, 'deleteAttachedFile'])->name('invoices.delete-attached-file');
    Route::post('invoice/{id}/sign-invoice', [InvoiceController::class, 'signInvoice'])->name('invoices.sign-invoice');
    Route::post('invoice/{id}/add-expected-payment-date', [InvoiceController::class, 'addExpectedPaymentDate'])->name('invoices.add-expected-payment-date');
    Route::post('invoice/{id}/mark-as-void', [InvoiceController::class, 'markAsVoid'])->name('invoices.mark-as-void');
    Route::post('invoice/{id}/mark-as-write-off', [InvoiceController::class, 'markAsWriteOff'])->name('invoices.mark-as-write-off');
    Route::post('invoice/{id}/cancel-write-off', [InvoiceController::class, 'cancelWriteOff'])->name('invoices.cancel-write-off');
    Route::post('invoice/{id}/make-recurring', [InvoiceController::class, 'makeRecurring'])->name('invoices.make-recurring');
    Route::get('invoice/{id}/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf');

    Route::post('board/status-change', [BoardController::class, 'boardStatusChange'])->name('board.status-change');

    /** Record Payment */
    Route::post('invoice/{id}/payments', [ReceivedPaymentController::class, 'recordPaymentForSpecificInvoice'])->name('invoices.payments.store');
    Route::put('invoice/{id}/payments/{payment_id}', [ReceivedPaymentController::class, 'updatePaymentForSpecificInvoice'])->name('invoices.payments.update');
    Route::put('invoice/{id}/payments/{payment_id}/refund', [ReceivedPaymentController::class, 'refundPaymentForSpecificInvoice'])->name('invoices.payments.refund');
    Route::delete('invoice/{id}/payments/{payment_id}', [ReceivedPaymentController::class, 'deletePaymentForSpecificInvoice'])->name('invoices.payments.destroy');

    // Apply Credit Note to Invoices
    Route::post('invoice/{id}/credit-notes', [CreditNoteController::class, 'applyForSpecificInvoice'])->name('invoices.credit-notes.apply');
    Route::delete('invoice/{id}/credit-notes/{credit_note_application_id}', [CreditNoteController::class, 'deleteCreditNoteApplication'])->name('invoices.credit-notes.delete');

    Route::get('contacts-with-unpaid-invoices', [ReceivedPaymentController::class, 'getContactsWithUnpaidInvoices']);
    Route::resource('payment-received', ReceivedPaymentController::class);
    Route::get('get-initial-payment-received-data', [ReceivedPaymentController::class, 'getInitialData']);
    Route::post('payment-received/{id}/refund', [ReceivedPaymentController::class, 'refund'])->name('payment-received.refund');
    Route::post('payment-received/{id}/void', [ReceivedPaymentController::class, 'void'])->name('payment-received.void');
    Route::post('payment-received/{id}/attach-file', [ReceivedPaymentController::class, 'attachFile'])->name('payment-received.attach-file');
    Route::delete('payment-received/{id}/attach-file/{fileId}', [ReceivedPaymentController::class, 'deleteAttachedFile'])->name('payment-received.delete-attached-file');

    Route::resource('credit-notes', CreditNoteController::class);
    Route::get('get-initial-credit-notes-data', [CreditNoteController::class, 'getInitialData']);
    Route::get('credit-notes/{id}/activity-logs', [CreditNoteController::class, 'getActivityLogs'])->name('credit-notes.activity-logs');
    Route::get('credit-notes/{id}/comments', [CreditNoteController::class, 'getComments'])->name('credit-notes.comments');
    Route::post('credit-notes/{id}/add-comment', [CreditNoteController::class, 'addComment'])->name('credit-notes.comments.add');
    Route::patch('credit-notes/{id}/comment/{commentId}', [CreditNoteController::class, 'updateComment'])->name('credit-notes.comments.update');
    Route::delete('credit-notes/{id}/comment/{commentId}', [CreditNoteController::class, 'deleteComment'])->name('credit-notes.comments.delete');
    Route::post('credit-notes/{id}/attach-file', [CreditNoteController::class, 'attachFile'])->name('credit-notes.attach-file');
    Route::delete('credit-notes/{id}/attach-file/{fileId}', [CreditNoteController::class, 'deleteAttachedFile'])->name('credit-notes.delete-attached-file');

    Route::post('credit-notes/{id}/apply-to-invoices', [CreditNoteController::class, 'applyToInvoices'])->name('credit-notes.apply-to-invoices');
    Route::post('credit-notes/{id}/refund', [CreditNoteController::class, 'refund'])->name('credit-notes.refund');
    Route::put('credit-notes/{id}/refund-histories/{refund_history_id}', [CreditNoteController::class, 'updateRefundHistory'])->name('credit-notes.refund-histories.update');
    Route::delete('credit-notes/{id}/refund-histories/{refund_history_id}', [CreditNoteController::class, 'deleteRefundHistory'])->name('credit-notes.refund-histories.delete');
});

Route::prefix('/v1/crm')->name('api.')->group(function () {
    Route::post('signature-file-upload', [FileControllder::class, 'store'])->name('signature-file.upload');
    Route::post('quotations/{id}/public-details', [QuotationController::class, 'getQuotationPublicDetails'])->name('quotations.public-details');
    Route::post('quotation/{id}/sign-client-quotation', [QuotationController::class, 'signClientQuotation'])->name('quotations.sign-client-quotation');
    Route::post('quotation/{id}/update-client-quotation', [QuotationController::class, 'updateClientQuotation'])->name('quotations.update-client-quotation');

    Route::post('invoice/{id}/public-details', [InvoiceController::class, 'getInvoicePublicDetails'])->name('invoices.public-details');
    Route::post('invoice/{id}/sign-client-invoice', [InvoiceController::class, 'signClientInvoice'])->name('invoices.sign-client-invoice');
});

<?php

namespace Modules\CRM\App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\CRM\App\Models\Quotation;
use Modules\Sale\App\Models\Invoice;

class MarkOverdueInvoice extends Command
{
    protected $signature = 'invoice:mark-overdue';
    protected $description = 'Mark invoices as overdue if their due date has passed';

    public function handle()
    {
        $this->info('Starting to mark overdue invoices...');

        $invoices = Invoice::whereNotNull('due_date')
            ->whereNotIn('invoice_status', ['paid', 'void', 'write_off', 'overdue'])
            ->where('due_date', '<', Carbon::now()->toDateString())
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->invoice_status = 'overdue';
            $invoice->save();

            Log::info("Invoice ID {$invoice->id} marked as overdue.");
            $this->info("Invoice ID {$invoice->id} marked as overdue.");
        }

        $this->info('Finished marking overdue invoices.');
    }
}

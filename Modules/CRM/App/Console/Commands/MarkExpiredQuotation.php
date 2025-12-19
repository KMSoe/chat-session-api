<?php

namespace Modules\CRM\App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\CRM\App\Models\Quotation;

class MarkExpiredQuotation extends Command
{
    protected $signature = 'quotation:mark-expired';
    protected $description = 'Mark quotations as expired if their expiry date has passed';

    public function handle()
    {
        $this->info('Starting to mark expired quotations...');

        $quotations = Quotation::whereNotNull('expiry_date')
            ->whereNotIn('quotation_status', ['accepted', 'invoiced', 'declined', 'expired'])
            ->where('expiry_date', '<', Carbon::now()->toDateString())
            ->get();

        foreach ($quotations as $quotation) {
            $quotation->quotation_status = 'expired';
            $quotation->save();

            Log::info("Quotation ID {$quotation->id} marked as expired.");
            $this->info("Quotation ID {$quotation->id} marked as expired.");
        }
        
        $this->info('Finished marking expired quotations.');
    }
}

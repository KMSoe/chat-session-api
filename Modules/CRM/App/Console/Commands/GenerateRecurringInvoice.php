<?php

namespace Modules\CRM\App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\CRM\App\Models\Invoice;
use Modules\CRM\App\Models\PaymentTerm;
use Modules\CRM\App\Models\RecurringInvoice;
use Modules\CRM\App\Repositories\InvoiceRepo;
use Modules\Notification\App\Repositories\NotificationRepository;
use Modules\Notification\App\Services\Impl\NotificationServiceImpl;

class GenerateRecurringInvoice extends Command
{
    protected $signature = 'invoice:generate-recurring';
    protected $description = 'Generate invoices for recurring invoices due today';
    protected $repo;

    public function __construct(InvoiceRepo $repo)
    {
        parent::__construct();
        $this->repo = $repo;
    }

    /**
     * Safe info output that checks if running in console
     */
    protected function safeInfo($message)
    {
        if ($this->output) {
            $this->info($message);
        }
        Log::info($message);
    }

    /**
     * Safe error output that checks if running in console
     */
    protected function safeError($message)
    {
        if ($this->output) {
            $this->error($message);
        }
        Log::error($message);
    }

    public function handle()
    {
        $this->info('Starting recurring invoice generation process...');
        Log::info('Starting recurring invoice generation process...');
        
        $today = Carbon::today();
        
        $recurringInvoices = RecurringInvoice::where(function ($query) use ($today) {
                $query->where('recurring_level', 'invoice')
                    ->where(function ($q) use ($today) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $today);
                    })
                    ->where('start_date', '<=', $today);
            })
            ->orWhere(function ($query) use ($today) {
                $query->where('recurring_level', 'item')
                    ->whereHas('items', function ($q) use ($today) {
                        $q->where('start_date', '<=', $today)
                        ->where(function ($subQ) use ($today) {
                            $subQ->whereNull('end_date')
                                ->orWhere('end_date', '>=', $today);
                        });
                    });
                })
                ->with('items')
                ->get();

        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($recurringInvoices as $recurringInvoice) {
            $this->info("Processing recurring invoice ID: {$recurringInvoice->id}");
            
            try {
                $result = $this->generateInvoice($recurringInvoice);
                
                if ($result) {
                    $generatedCount++;
                    $this->info("✓ Generated invoice #{$result->invoice_number} (ID: {$result->id})");
                    Log::info("Generated invoice ID {$result->id} for recurring invoice ID {$recurringInvoice->id}");
                } else {
                    $skippedCount++;
                    $this->info("  Skipped - not due for generation");
                }
            } catch (\Exception $e) {
                $this->error("✗ Failed to generate invoice for recurring invoice ID {$recurringInvoice->id}");
                $this->error("  Error: " . $e->getMessage());
                Log::error("Failed to generate invoice for recurring invoice ID {$recurringInvoice->id}: " . $e->getMessage());
            }
        }

        $this->info("\nSummary:");
        $this->info("Generated: {$generatedCount}");
        $this->info("Skipped: {$skippedCount}");
        
        Log::info("Recurring invoice generation completed. Generated: {$generatedCount}, Skipped: {$skippedCount}");
    }

    /**
     * Generate invoice based on recurring level (invoice or item)
     * 
     * @param RecurringInvoice $recurringInvoice
     * @param bool $forceGenerate When true, generates immediately regardless of start_date (flexible override)
     *                            When false, follows proper schedule (checks start_date and recurrence rules)
     * @return Invoice|null
     */
    public function generateInvoice(RecurringInvoice $recurringInvoice, $forceGenerate = false)
    {
        $today = Carbon::today();

        // Determine which items should be included in today's invoice
        $itemsToGenerate = $this->getItemsDueForGeneration($recurringInvoice, $today, $forceGenerate);

        // If no items are due, skip invoice generation
        if (empty($itemsToGenerate)) {
            $this->safeInfo("No items due for generation");
            return null;
        }

        $this->safeInfo("Generating invoice with " . count($itemsToGenerate) . " items");

        // Create the invoice
        $invoice = $this->createInvoice($recurringInvoice, $itemsToGenerate);

        // Log generation for invoice-level recurrence
        if ($recurringInvoice->recurring_level === 'invoice') {
            $recurringInvoice->logs()->create([
                'recurring_invoice_id' => $recurringInvoice->id,
                'invoice_id' => $invoice->id,
                'generated_on' => Carbon::now(),
            ]);
        }

        // Log generation for each item
        foreach ($itemsToGenerate as $itemData) {
            $recurringInvoice->logs()->create([
                'recurring_invoice_id' => $recurringInvoice->id,
                'recurring_invoice_item_id' => $itemData['item']->id,
                'invoice_id' => $invoice->id,
                'generated_on' => Carbon::now(),
            ]);
        }

        // Update last_generated_on
        $recurringInvoice->update([
            'last_generated_on' => Carbon::now(),
        ]);

        // Send email if preference is set
        if ($recurringInvoice->preference == 'create_and_send') {
            // TODO: Implement email sending logic
            // Mail::to($invoice->contact->email)->send(new InvoiceMail($invoice));
        }

        return $invoice;
    }

    /**
     * Get items that are due for generation today
     */
    protected function getItemsDueForGeneration(RecurringInvoice $recurringInvoice, Carbon $today, $forceGenerate = false)
    {
        $itemsToGenerate = [];

        if ($recurringInvoice->recurring_level === 'invoice') {
            // Invoice-level recurrence
            if ($forceGenerate) {
                // Force generate: include all items immediately regardless of start_date
                foreach ($recurringInvoice->items as $item) {
                    $itemsToGenerate[] = ['item' => $item];
                }
            } elseif ($this->isInvoiceDueForGeneration($recurringInvoice, $today)) {
                // Normal flow: check if invoice is due based on schedule
                foreach ($recurringInvoice->items as $item) {
                    $itemsToGenerate[] = ['item' => $item];
                }
            }
        } else {
            // Item-level recurrence: check each item individually
            foreach ($recurringInvoice->items as $item) {
                if ($forceGenerate) {
                    // Force generate: include all items immediately regardless of start_date
                    $itemsToGenerate[] = ['item' => $item];
                } elseif ($this->isItemDueForGeneration($item, $today)) {
                    // Normal flow: check if item is due based on its schedule
                    $itemsToGenerate[] = ['item' => $item];
                }
            }
        }

        return $itemsToGenerate;
    }

    /**
     * Check if invoice-level recurrence is due for generation
     */
    protected function isInvoiceDueForGeneration(RecurringInvoice $recurringInvoice, Carbon $today): bool
    {
        // Check if already generated today (invoice-level)
        $generatedToday = $recurringInvoice->logs()
            ->whereNull('recurring_invoice_item_id')
            ->whereDate('generated_on', $today)
            ->exists();

        if ($generatedToday) {
            $this->safeInfo("  Already generated today (invoice-level)");
            return false;
        }

        // Handle one-time recurrence (interval = 0)
        if ($recurringInvoice->recurring_interval == 0) {
            $hasBeenGenerated = $recurringInvoice->logs()
                ->whereNull('recurring_invoice_item_id')
                ->exists();
            
            if ($hasBeenGenerated) {
                $this->safeInfo("  One-time recurrence already generated");
                return false;
            }
            
            $startDate = Carbon::parse($recurringInvoice->start_date);
            return $today->greaterThanOrEqualTo($startDate);
        }

        // Get last generation log for invoice-level
        $lastLog = $recurringInvoice->logs()
            ->whereNull('recurring_invoice_item_id')
            ->orderBy('generated_on', 'desc')
            ->first();

        if (!$lastLog) {
            // First generation
            $startDate = Carbon::parse($recurringInvoice->start_date);
            $this->safeInfo("  First generation - Start date: {$startDate->toDateString()}");
            return $today->greaterThanOrEqualTo($startDate);
        }

        // Calculate next due date
        $lastGeneratedDate = Carbon::parse($lastLog->generated_on);
        $nextDueDate = $this->calculateNextDueDate(
            $lastGeneratedDate,
            $recurringInvoice->recurrence_frequency,
            $recurringInvoice->recurring_interval
        );

        $this->safeInfo("  Last generated: {$lastGeneratedDate->toDateString()}");
        $this->safeInfo("  Next due date: {$nextDueDate->toDateString()}");

        return $today->greaterThanOrEqualTo($nextDueDate);
    }

    /**
     * Check if item-level recurrence is due for generation
     */
    protected function isItemDueForGeneration($item, Carbon $today): bool
    {
        // Check if already generated today (item-level)
        $generatedToday = $item->recurringInvoice->logs()
            ->where('recurring_invoice_item_id', $item->id)
            ->whereDate('generated_on', $today)
            ->exists();

        if ($generatedToday) {
            $this->safeInfo("    Item {$item->id}: Already generated today");
            return false;
        }

        // Check if item has passed its end date
        if ($item->end_date && Carbon::parse($item->end_date)->lessThan($today)) {
            $this->safeInfo("    Item {$item->id}: Past end date");
            return false;
        }

        // Handle one-time recurrence (interval = 0)
        if ($item->recurring_interval == 0) {
            $hasBeenGenerated = $item->recurringInvoice->logs()
                ->where('recurring_invoice_item_id', $item->id)
                ->exists();
            
            if ($hasBeenGenerated) {
                $this->safeInfo("Item {$item->id}: One-time recurrence already generated");
                return false;
            }
            
            $startDate = Carbon::parse($item->start_date);
            $this->safeInfo("    Item {$item->id}: One-time - Start: {$startDate->toDateString()}, Due: " . ($today->greaterThanOrEqualTo($startDate) ? 'YES' : 'NO'));
            return $today->greaterThanOrEqualTo($startDate);
        }

        // Get last generation log for this item
        $lastLog = $item->recurringInvoice->logs()
            ->where('recurring_invoice_item_id', $item->id)
            ->orderBy('generated_on', 'desc')
            ->first();

        if (!$lastLog) {
            // First generation
            $startDate = Carbon::parse($item->start_date);
            $this->safeInfo("    Item {$item->id}: First generation - Start: {$startDate->toDateString()}, Due: " . ($today->greaterThanOrEqualTo($startDate) ? 'YES' : 'NO'));
            return $today->greaterThanOrEqualTo($startDate);
        }

        // Calculate next due date based on item's own schedule
        $lastGeneratedDate = Carbon::parse($lastLog->generated_on);
        $nextDueDate = $this->calculateNextDueDate(
            $lastGeneratedDate,
            $item->recurrence_frequency,
            $item->recurring_interval
        );

        $this->safeInfo("    Item {$item->id}: Last: {$lastGeneratedDate->toDateString()}, Next: {$nextDueDate->toDateString()}, Due: " . ($today->greaterThanOrEqualTo($nextDueDate) ? 'YES' : 'NO'));

        return $today->greaterThanOrEqualTo($nextDueDate);
    }

    /**
     * Calculate the next due date based on frequency and interval
     */
    protected function calculateNextDueDate(Carbon $lastDate, string $frequency, int $interval): Carbon
    {
        $nextDate = $lastDate->copy();

        switch ($frequency) {
            case 'day':
                $nextDate->addDays($interval);
                break;
            
            case 'week':
                $nextDate->addWeeks($interval);
                break;
            
            case 'month':
                $nextDate->addMonths($interval);
                break;
            
            case 'year':
                $nextDate->addYears($interval);
                break;
            
            default:
                $nextDate->addMonths($interval ?: 1);
                break;
        }

        return $nextDate;
    }

    /**
     * Create the actual invoice with selected items
     */
    protected function createInvoice(RecurringInvoice $recurringInvoice, array $itemsToGenerate)
    {
        $notify_signer = [];

        $invoiceNumber = $this->repo->generateInvoiceNumber() ?? rand(0000000000, 99999999999);
        
        $invoiceDate = Carbon::today();
        $dueDate = $this->calculateDueDate($invoiceDate, $recurringInvoice->payment_term_id);

        // Calculate totals based on items to generate
        $subtotal = 0;
        $totalTaxAmount = 0;
        $totalDiscount = 0;

        foreach ($itemsToGenerate as $itemData) {
            $item = $itemData['item'];
            $itemAmount = $item->amount;
            
            // Apply item-level discount if applicable
            if ($recurringInvoice->discount_level === 'item' && $item->discount_value > 0) {
                if ($item->discount_type === 'percentage') {
                    $totalDiscount += ($itemAmount * $item->discount_value / 100);
                } else {
                    $totalDiscount += $item->discount_value;
                }
            }
            
            $subtotal += $itemAmount;
            $totalTaxAmount += $item->tax_amount ?? 0;
        }

        // Apply invoice-level discount if applicable
        if ($recurringInvoice->discount_level === 'invoice' && $recurringInvoice->discount_value > 0) {
            if ($recurringInvoice->discount_type === 'percentage') {
                $totalDiscount = $subtotal * $recurringInvoice->discount_value / 100;
            } else {
                $totalDiscount = $recurringInvoice->discount_value;
            }
        }

        // Calculate grand total
        $grandTotal = $subtotal - $totalDiscount + $totalTaxAmount;

        $invoice = Invoice::create([
            'quotation_id' => null,
            'recurring_invoice_id' => $recurringInvoice->id,
            'company_id' => $recurringInvoice->company_id,
            'contact_id' => $recurringInvoice->contact_id,
            'invoice_number' => $invoiceNumber,
            'reference_number' => $recurringInvoice->reference_number,
            'invoice_date' => $invoiceDate,
            'currency_id' => $recurringInvoice->currency_id,
            'payment_term_id' => $recurringInvoice->payment_term_id,
            'due_date' => $dueDate,
            'project_id' => $recurringInvoice->project_id,
            'sale_person_id' => $recurringInvoice->sale_person_id,
            'item_template_id' => $recurringInvoice->item_template_id,
            'customer_note' => $recurringInvoice->customer_note,
            'terms_and_conditions' => $recurringInvoice->terms_and_conditions,
            'subtotal' => $subtotal,
            'tax_id' => $recurringInvoice->tax_id,
            'tax_amount' => $totalTaxAmount,
            'discount_type' => $recurringInvoice->discount_type,
            'discount_value' => $recurringInvoice->discount_level === 'invoice' ? $recurringInvoice->discount_value : 0,
            'grand_total' => $grandTotal,
            'balance_due' => $grandTotal,
            'enable_organization_signature' => $recurringInvoice->enable_organization_signature,
            'enable_customer_signature' => $recurringInvoice->enable_customer_signature,
            'invoice_status' => $recurringInvoice->preference == 'draft' ? 'draft' : 'sent',
            'status' => 'pending',
            'taxation_level' => $recurringInvoice->taxation_level,
            'discount_level' => $recurringInvoice->discount_level,
            'created_by' => 0,
            'updated_by' => 0,
        ]);

        // Copy only the items that are due
        foreach ($itemsToGenerate as $itemData) {
            $item = $itemData['item'];
            
            $invoice->items()->create([
                'item_id' => $item->item_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'rate' => $item->rate,
                'tax_id' => $item->tax_id,
                'tax_amount' => $item->tax_amount,
                'amount' => $item->amount,
                'is_same_as_item' => $item->is_same_as_item,
                'discount_type' => $item->discount_type,
                'discount_value' => $item->discount_value,
            ]);
        }

        // Copy organization signatures
        foreach ($recurringInvoice->organizationSignatures as $signature) {
            $invoice->organizationSignatures()->create([
                'type' => 'organization',
                'label' => $signature->label,
                'assigned_signer_id' => $signature->assigned_signer_id,
                'notify_signer' => $signature->notify_signer,
            ]);
            
            if ($signature->notify_signer && $signature->assigned_signer_id) {
                $notify_signer[] = $signature->assigned_signer_id;
            }
        }

        // Copy customer signatures
        foreach ($recurringInvoice->customerSignatures as $signature) {
            $invoice->customerSignatures()->create([
                'type' => 'customer',
                'label' => $signature->label,
            ]);
        }

        // Copy files
        foreach ($recurringInvoice->files as $file) {
            $invoice->files()->create([
                'file_id' => $file->file_id,
            ]);
        }

        // Copy email communications
        foreach ($recurringInvoice->emailCommunications as $communication) {
            $invoice->emailCommunications()->create([
                'contact_id' => $communication->contact_id,
            ]);
        }

        // Send notifications to signers
        if (!empty($notify_signer)) {
            $this->sentNotification(
                $notify_signer,
                [
                    'title' => 'New Invoice Assigned for Signature',
                    'description' => "You have been assigned to sign invoice #{$invoice->invoice_number}.",
                    'type' => 'invoice',
                    'direction' => 'in_app',
                    'subject' => $invoice,
                ]
            );
        }

        // Create activity log
        $invoice->activityLogs()->create([
            'description' => "Invoice created from recurring invoice #{$recurringInvoice->id}.",
            'created_by' => 0,
        ]);

        return $invoice;
    }

    public function calculateDueDate(Carbon $invoiceDate, $paymentTermId): Carbon
    {
        $paymentTerm = PaymentTerm::find($paymentTermId);
        
        if ($paymentTerm && $paymentTerm->days) {
            return $invoiceDate->copy()->addDays($paymentTerm->days);
        }
        
        return $invoiceDate->copy()->addDays(30);
    }

    protected function sentNotification($employeeIds, $message)
    {
        $notificationService = new NotificationServiceImpl(new NotificationRepository());
        
        $data = [
            'title'       => $message['title'],
            'description' => $message['description'],
            'type'        => $message['type'],
            'direction'   => $message['direction'],
            'event'       => 'created',
            'subject'     => $message['subject'],
            'causer'      => null,
            'send_to'     => 'custom',
            'employee_ids' => is_array($employeeIds) ? $employeeIds : [$employeeIds],
        ];
        
        $notificationService->save($data);
    }
}
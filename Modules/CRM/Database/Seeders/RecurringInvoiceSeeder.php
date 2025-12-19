<?php

namespace Modules\CRM\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\CRM\App\Models\Company;
use Modules\CRM\App\Models\Contact;
use Modules\CRM\App\Models\Item;
use Modules\CRM\App\Models\RecurringInvoice;
use Modules\CRM\App\Models\Tax;

class RecurringInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('recurring_invoices')->truncate();
        DB::table('recurring_invoice_items')->truncate();
        DB::table('recurring_invoice_signatures')->truncate();
        DB::table('recurring_invoice_files')->truncate();
        DB::table('recurring_invoice_email_communications')->truncate();
        DB::table('recurring_invoice_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::beginTransaction();

        try {
            $companies = Company::with('contacts')->limit(5)->get();
            $items = Item::limit(8)->get();
            $taxes = Tax::limit(5)->get();

            $this->createRecurringInvoices($companies, $items, $taxes);

            DB::commit();
            $this->command->info('Recurring invoice seeder completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Recurring invoice seeder failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function createRecurringInvoices($companies, $items, $taxes)
    {
        $recurringInvoiceData = [
            // 1. Item-level recurring - Mixed frequencies
            [
                'company_id' => $companies[0]->id,
                'contact_id' => $companies[0]->contacts[0]->id ?? null,
                'recurring_level' => 'item', // Item-level
                'start_date' => null,
                'end_date' => null,
                'recurring_interval' => 0, // Not used at invoice level
                'recurrence_frequency' => null, // Not used at invoice level
                'reference_number' => 'REC-INV-001',
                'currency_id' => 1,
                'payment_term_id' => 1,
                'project_id' => 1,
                'sale_person_id' => 1,
                'preference' => 'create_and_send',
                'item_template_id' => 1,
                'customer_note' => 'Monthly web development retainer with daily hosting monitoring.',
                'terms_and_conditions' => 'Payment due within 30 days. Items billed according to their individual recurrence schedules.',
                'subtotal' => 5000.00,
                'tax_id' => null,
                'tax_amount' => 0.00,
                'discount_type' => 'percentage',
                'discount_value' => 5.00,
                'discount_level' => 'invoice',
                'grand_total' => 5237.50,
                'enable_organization_signature' => true,
                'enable_customer_signature' => false,
                'taxation_level' => 'item',
                'status' => 'active',
                'items' => [
                    [
                        'item_id' => $items[0]->id,
                        'description' => 'Web Development - Monthly Retainer (30 hours)',
                        'quantity' => 30,
                        'rate' => 150.00,
                        'amount' => 4500.00,
                        'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                        'end_date' => Carbon::now()->addMonths(9)->format('Y-m-d'),
                        'recurring_interval' => 1, // Every 1 month
                        'recurrence_frequency' => 'month',
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[0]->id,
                        'tax_amount' => 641.25,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[4]->id,
                        'description' => 'Cloud Hosting & Daily Monitoring',
                        'quantity' => 1,
                        'rate' => 500.00,
                        'amount' => 500.00,
                        'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                        'end_date' => Carbon::now()->addMonths(9)->format('Y-m-d'),
                        'recurring_interval' => 1, // Every 1 day
                        'recurrence_frequency' => 'day',
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[0]->id,
                        'tax_amount' => 71.25,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Account Manager',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                ],
                'customer_signatures' => [],
                'files' => [],
                'communications' => [$companies[0]->contacts[0]->id ?? null],
            ],

            // 2. Invoice-level recurring - Weekly
            [
                'company_id' => $companies[1]->id,
                'contact_id' => $companies[1]->contacts[0]->id ?? null,
                'recurring_level' => 'invoice', // Invoice-level
                'start_date' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                'end_date' => null,
                'recurring_interval' => 1, // Every 1 week
                'recurrence_frequency' => 'week',
                'reference_number' => 'REC-INV-002',
                'currency_id' => 1,
                'payment_term_id' => 2,
                'project_id' => 1,
                'sale_person_id' => 1,
                'preference' => 'draft',
                'item_template_id' => 2,
                'customer_note' => 'Weekly digital marketing services including social media management.',
                'terms_and_conditions' => 'Weekly billing. Payment due within 15 days of invoice date.',
                'subtotal' => 1200.00,
                'tax_id' => $taxes[1]->id,
                'tax_amount' => 216.00,
                'discount_type' => null,
                'discount_value' => 0.00,
                'discount_level' => 'invoice',
                'grand_total' => 1416.00,
                'enable_organization_signature' => false,
                'enable_customer_signature' => false,
                'taxation_level' => 'invoice',
                'status' => 'active',
                'items' => [
                    [
                        'item_id' => $items[7]->id,
                        'description' => 'Digital Marketing - Weekly Package',
                        'quantity' => 10,
                        'rate' => 120.00,
                        'amount' => 1200.00,
                        'start_date' => null,
                        'end_date' => null,
                        'recurring_interval' => 0, // One-time item (invoice controls recurrence)
                        'recurrence_frequency' => null,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [],
                'customer_signatures' => [],
                'files' => [],
                'communications' => [$companies[1]->contacts[0]->id ?? null],
            ],

            // 3. Item-level recurring - Quarterly + Bi-Monthly (every 2 months)
            [
                'company_id' => $companies[2]->id,
                'contact_id' => $companies[2]->contacts[0]->id ?? null,
                'recurring_level' => 'item',
                'start_date' => null,
                'end_date' => null,
                'recurring_interval' => 0,
                'recurrence_frequency' => null,
                'reference_number' => 'REC-INV-003',
                'currency_id' => 1,
                'payment_term_id' => 1,
                'project_id' => 1,
                'sale_person_id' => 1,
                'preference' => 'create_and_send',
                'item_template_id' => 3,
                'customer_note' => 'IT consulting with quarterly strategic review and bi-monthly infrastructure management.',
                'terms_and_conditions' => 'Items billed according to their recurrence schedules. Payment due within 30 days.',
                'subtotal' => 15000.00,
                'tax_id' => null,
                'tax_amount' => 0.00,
                'discount_type' => 'fixed',
                'discount_value' => 1000.00,
                'discount_level' => 'invoice',
                'grand_total' => 16100.00,
                'enable_organization_signature' => true,
                'enable_customer_signature' => true,
                'taxation_level' => 'item',
                'status' => 'active',
                'items' => [
                    [
                        'item_id' => $items[5]->id,
                        'description' => 'IT Consulting - Quarterly Strategic Review',
                        'quantity' => 60,
                        'rate' => 200.00,
                        'amount' => 12000.00,
                        'start_date' => Carbon::now()->startOfQuarter()->format('Y-m-d'),
                        'end_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                        'recurring_interval' => 3, // Every 3 months (quarterly)
                        'recurrence_frequency' => 'month',
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[2]->id,
                        'tax_amount' => 1100.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[4]->id,
                        'description' => 'Cloud Infrastructure Management - Bi-Monthly',
                        'quantity' => 60,
                        'rate' => 50.00,
                        'amount' => 3000.00,
                        'start_date' => Carbon::now()->startOfQuarter()->format('Y-m-d'),
                        'end_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                        'recurring_interval' => 2, // Every 2 months
                        'recurrence_frequency' => 'month',
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[2]->id,
                        'tax_amount' => 200.00,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Technical Director',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                ],
                'customer_signatures' => [
                    [
                        'label' => 'CTO',
                    ],
                ],
                'files' => [],
                'communications' => [$companies[2]->contacts[0]->id ?? null],
            ],

            // 4. Item-level recurring - Mixed frequencies (bi-weekly, monthly, weekly)
            [
                'company_id' => $companies[3]->id,
                'contact_id' => $companies[3]->contacts[0]->id ?? null,
                'recurring_level' => 'item',
                'start_date' => null,
                'end_date' => null,
                'recurring_interval' => 0,
                'recurrence_frequency' => null,
                'reference_number' => 'REC-INV-004',
                'currency_id' => 1,
                'payment_term_id' => 2,
                'project_id' => 1,
                'sale_person_id' => 1,
                'preference' => 'create_and_send',
                'item_template_id' => 4,
                'customer_note' => 'Content creation bi-weekly with monthly SEO optimization and weekly social media.',
                'terms_and_conditions' => 'Items billed at different frequencies. Payment due within 15 days.',
                'subtotal' => 2200.00,
                'tax_id' => null,
                'tax_amount' => 0.00,
                'discount_type' => null,
                'discount_value' => 0.00,
                'discount_level' => 'item',
                'grand_total' => 2191.00,
                'enable_organization_signature' => true,
                'enable_customer_signature' => false,
                'taxation_level' => 'item',
                'status' => 'active',
                'items' => [
                    [
                        'item_id' => $items[6]->id,
                        'description' => 'Content Writing - Bi-Weekly (20 Articles)',
                        'quantity' => 20,
                        'rate' => 60.00,
                        'amount' => 1200.00,
                        'start_date' => Carbon::now()->format('Y-m-d'),
                        'end_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                        'recurring_interval' => 2, // Every 2 weeks
                        'recurrence_frequency' => 'week',
                        'discount_type' => 'percentage',
                        'discount_value' => 10.00,
                        'tax_id' => $taxes[4]->id,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[3]->id,
                        'description' => 'SEO Optimization - Monthly',
                        'quantity' => 10,
                        'rate' => 80.00,
                        'amount' => 800.00,
                        'start_date' => Carbon::now()->format('Y-m-d'),
                        'end_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                        'recurring_interval' => 1, // Every 1 month
                        'recurrence_frequency' => 'month',
                        'discount_type' => 'fixed',
                        'discount_value' => 50.00,
                        'tax_id' => $taxes[2]->id,
                        'tax_amount' => 75.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[7]->id,
                        'description' => 'Social Media Management - Weekly',
                        'quantity' => 2,
                        'rate' => 100.00,
                        'amount' => 200.00,
                        'start_date' => Carbon::now()->format('Y-m-d'),
                        'end_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                        'recurring_interval' => 1, // Every 1 week
                        'recurrence_frequency' => 'week',
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[4]->id,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Content Manager',
                        'assigned_signer_id' => 1,
                        'notify_signer' => false,
                    ],
                ],
                'customer_signatures' => [],
                'files' => [],
                'communications' => [$companies[3]->contacts[0]->id ?? null],
            ],

            // 5. Item-level recurring - Annual + Semi-Annual + Monthly + One-time
            [
                'company_id' => $companies[4]->id,
                'contact_id' => $companies[4]->contacts[0]->id ?? null,
                'recurring_level' => 'item',
                'start_date' => null,
                'end_date' => null,
                'recurring_interval' => 0,
                'recurrence_frequency' => null,
                'reference_number' => 'REC-INV-005',
                'currency_id' => 1,
                'payment_term_id' => 1,
                'project_id' => 1,
                'sale_person_id' => 1,
                'preference' => 'draft',
                'item_template_id' => 1,
                'customer_note' => 'Annual app maintenance with semi-annual major updates, monthly support, and one-time setup.',
                'terms_and_conditions' => 'Items billed at different frequencies. Payment due within 30 days.',
                'subtotal' => 25000.00,
                'tax_id' => $taxes[0]->id,
                'tax_amount' => 3375.00,
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'discount_level' => 'invoice',
                'grand_total' => 25875.00,
                'enable_organization_signature' => true,
                'enable_customer_signature' => true,
                'taxation_level' => 'invoice',
                'status' => 'active',
                'items' => [
                    [
                        'item_id' => $items[1]->id,
                        'description' => 'Mobile App Maintenance - Annual License',
                        'quantity' => 100,
                        'rate' => 175.00,
                        'amount' => 17500.00,
                        'start_date' => Carbon::now()->startOfYear()->format('Y-m-d'),
                        'end_date' => null,
                        'recurring_interval' => 1, // Every 1 year
                        'recurrence_frequency' => 'year',
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[2]->id,
                        'description' => 'UI/UX Major Updates - Semi-Annual',
                        'quantity' => 50,
                        'rate' => 100.00,
                        'amount' => 5000.00,
                        'start_date' => Carbon::now()->startOfYear()->format('Y-m-d'),
                        'end_date' => null,
                        'recurring_interval' => 6, // Every 6 months
                        'recurrence_frequency' => 'month',
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[4]->id,
                        'description' => 'Cloud Hosting - Monthly Billing',
                        'quantity' => 12,
                        'rate' => 50.00,
                        'amount' => 600.00,
                        'start_date' => Carbon::now()->startOfYear()->format('Y-m-d'),
                        'end_date' => null,
                        'recurring_interval' => 1, // Every 1 month
                        'recurrence_frequency' => 'month',
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[5]->id,
                        'description' => '24/7 Support & Monitoring - Monthly',
                        'quantity' => 12,
                        'rate' => 150.00,
                        'amount' => 1800.00,
                        'start_date' => Carbon::now()->startOfYear()->format('Y-m-d'),
                        'end_date' => null,
                        'recurring_interval' => 1, // Every 1 month
                        'recurrence_frequency' => 'month',
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[0]->id,
                        'description' => 'Initial App Setup & Configuration - One Time',
                        'quantity' => 10,
                        'rate' => 150.00,
                        'amount' => 1500.00,
                        'start_date' => Carbon::now()->startOfYear()->format('Y-m-d'),
                        'end_date' => Carbon::now()->startOfYear()->format('Y-m-d'),
                        'recurring_interval' => 0, // One-time item (no recurrence)
                        'recurrence_frequency' => null,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Technical Lead',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                    [
                        'label' => 'Account Director',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                ],
                'customer_signatures' => [
                    [
                        'label' => 'Client Director',
                    ],
                ],
                'files' => [],
                'communications' => [$companies[4]->contacts[0]->id ?? null],
            ],
        ];

        foreach ($recurringInvoiceData as $data) {
            $itemsData = $data['items'] ?? [];
            $orgSignatures = $data['organization_signatures'] ?? [];
            $custSignatures = $data['customer_signatures'] ?? [];
            $communications = $data['communications'] ?? [];
            $files = $data['files'] ?? [];
            
            unset($data['items'], $data['organization_signatures'], $data['customer_signatures'], $data['communications'], $data['files']);
            
            $data['created_by'] = 0;
            $recurringInvoice = RecurringInvoice::create($data);
            
            foreach ($itemsData as $itemData) {
                $itemData['recurring_invoice_id'] = $recurringInvoice->id;
                $itemData['created_at'] = now();
                $itemData['updated_at'] = now();
                DB::table('recurring_invoice_items')->insert($itemData);
            }
            
            foreach ($orgSignatures as $signature) {
                $signature['recurring_invoice_id'] = $recurringInvoice->id;
                $signature['type'] = 'organization';
                $signature['created_at'] = now();
                $signature['updated_at'] = now();
                DB::table('recurring_invoice_signatures')->insert($signature);
            }
            
            foreach ($custSignatures as $signature) {
                $signature['recurring_invoice_id'] = $recurringInvoice->id;
                $signature['type'] = 'customer';
                $signature['created_at'] = now();
                $signature['updated_at'] = now();
                DB::table('recurring_invoice_signatures')->insert($signature);
            }
            
            if (!empty($communications)) {
                foreach ($communications as $contactId) {
                    if ($contactId) {
                        $recurringInvoice->emailCommunications()->create(['contact_id' => $contactId]);
                    }
                }
            }
            
            if (!empty($files)) {
                foreach ($files as $fileId) {
                    $recurringInvoice->files()->create(['file_id' => $fileId]);
                }
            }
        }

        $this->command->info('Created ' . count($recurringInvoiceData) . ' recurring invoices');
    }
}
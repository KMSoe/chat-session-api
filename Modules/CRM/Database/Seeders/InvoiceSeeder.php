<?php

namespace Modules\CRM\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\CRM\App\Models\Company;
use Modules\CRM\App\Models\Contact;
use Modules\CRM\App\Models\Invoice;
use Modules\CRM\App\Models\Item;
use Modules\CRM\App\Models\Tax;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('invoices')->truncate();
        DB::table('invoice_items')->truncate();
        DB::table('invoice_signatures')->truncate();
        DB::table('invoice_files')->truncate();
        DB::table('invoice_email_communications')->truncate();
        DB::table('invoice_activity_logs')->truncate();
        DB::table('invoice_comments')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::beginTransaction();

        try {
            $companies = Company::with('contacts')->limit(5)->get();
            $items = Item::limit(8)->get();
            $taxes = Tax::limit(5)->get();

            if ($companies->isEmpty() || $items->isEmpty() || $taxes->isEmpty()) {
                $this->command->error('Please run QuotationSeeder first to create companies, items, and taxes.');
                return;
            }

            $this->createInvoices($companies, $items, $taxes);

            DB::commit();
            $this->command->info('Invoice seeder completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Invoice seeder failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function createInvoices($companies, $items, $taxes)
    {
        $invoiceData = [
            // 1. Draft Invoice - Web Development
            [
                'company_id' => $companies[0]->id,
                'contact_id' => $companies[0]->contacts[0]->id ?? null,
                'invoice_number' => 'INV-000001',
                'reference_number' => 'WEB-DEV-2025-001',
                'invoice_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'currency_id' => 1,
                'payment_term_id' => 1, // Net 30
                'due_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => 1,
                'customer_note' => 'Thank you for your business. Web development services completed as per agreement.',
                'terms_and_conditions' => 'Payment due within 30 days. Late payment subject to 1.5% monthly interest. All disputes subject to local jurisdiction.',
                'subtotal' => 8000.00,
                'tax_id' => null, // Item-level taxation
                'tax_amount' => 0.00,
                'discount_type' => 'percentage',
                'discount_value' => 5.00,
                'discount_level' => 'invoice',
                'grand_total' => 8340.00, // (8000 - 5%) + item taxes
                'balance_due' => 8340.00,
                'enable_organization_signature' => true,
                'enable_customer_signature' => true,
                'taxation_level' => 'item',
                'invoice_status' => 'draft',
                'status' => 'pending',
                'items' => [
                    [
                        'item_id' => $items[0]->id,
                        'description' => 'Web Development - Frontend & Backend (50 hours)',
                        'quantity' => 50,
                        'rate' => 150.00,
                        'amount' => 7500.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[0]->id, // 15%
                        'tax_amount' => 1068.75, // (7500 * 0.95) * 0.15
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[4]->id,
                        'description' => 'Cloud Hosting Setup',
                        'quantity' => 1,
                        'rate' => 500.00,
                        'amount' => 500.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[0]->id,
                        'tax_amount' => 71.25, // (500 * 0.95) * 0.15
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Project Manager',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                ],
                'customer_signatures' => [
                    [
                        'label' => 'Client Approver',
                    ],
                ],
                'files' => [],
                'communications' => [$companies[0]->contacts[0]->id ?? null],
            ],

            // 2. Sent Invoice - Marketing Services (Invoice-level taxation)
            [
                'company_id' => $companies[1]->id,
                'contact_id' => $companies[1]->contacts[0]->id ?? null,
                'invoice_number' => 'INV-000002',
                'reference_number' => 'MKTG-2025-002',
                'invoice_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'currency_id' => 1,
                'payment_term_id' => 2, // Net 15
                'due_date' => Carbon::now()->format('Y-m-d'), // Due today
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => 2,
                'customer_note' => 'Digital marketing campaign for Q4 2025. Includes social media management and content creation.',
                'terms_and_conditions' => 'Payment due within 15 days. Services provided on monthly retainer basis.',
                'subtotal' => 3400.00,
                'tax_id' => $taxes[1]->id, // Invoice-level: 18%
                'tax_amount' => 540.00, // (3400 - 400) * 0.18
                'discount_type' => 'fixed',
                'discount_value' => 400.00,
                'discount_level' => 'invoice',
                'grand_total' => 3540.00,
                'balance_due' => 3540.00,
                'enable_organization_signature' => false,
                'enable_customer_signature' => false,
                'taxation_level' => 'invoice',
                'invoice_status' => 'sent',
                'status' => 'pending',
                'items' => [
                    [
                        'item_id' => $items[7]->id,
                        'description' => 'Digital Marketing Campaign Management',
                        'quantity' => 20,
                        'rate' => 120.00,
                        'amount' => 2400.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null, // Invoice-level taxation
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[6]->id,
                        'description' => 'Content Writing - 20 Blog Posts',
                        'quantity' => 20,
                        'rate' => 50.00,
                        'amount' => 1000.00,
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

            // 3. Pending Approval - IT Consulting (Item-level discounts)
            [
                'company_id' => $companies[2]->id,
                'contact_id' => $companies[2]->contacts[0]->id ?? null,
                'invoice_number' => 'INV-000003',
                'reference_number' => 'IT-CONSULT-003',
                'invoice_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'currency_id' => 1,
                'payment_term_id' => 1,
                'due_date' => Carbon::now()->addDays(27)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => 3,
                'customer_note' => 'Strategic IT consulting and infrastructure planning services for Q4 implementation.',
                'terms_and_conditions' => 'Payment terms: Net 30 days. Consulting services billed monthly.',
                'subtotal' => 14500.00,
                'tax_id' => null,
                'tax_amount' => 0.00,
                'discount_type' => null,
                'discount_value' => 0.00,
                'discount_level' => 'item', // Item-level discounts
                'grand_total' => 15205.00,
                'balance_due' => 15205.00,
                'enable_organization_signature' => true,
                'enable_customer_signature' => true,
                'taxation_level' => 'item',
                'invoice_status' => 'pending_approval',
                'status' => 'pending',
                'items' => [
                    [
                        'item_id' => $items[5]->id,
                        'description' => 'IT Consulting - Strategic Planning (50 hours)',
                        'quantity' => 50,
                        'rate' => 200.00,
                        'amount' => 10000.00,
                        'discount_type' => 'percentage',
                        'discount_value' => 10.00, // 10% discount
                        'tax_id' => $taxes[2]->id, // 10%
                        'tax_amount' => 900.00, // (10000 - 10%) * 0.10
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[4]->id,
                        'description' => 'Cloud Infrastructure Assessment',
                        'quantity' => 30,
                        'rate' => 150.00,
                        'amount' => 4500.00,
                        'discount_type' => 'fixed',
                        'discount_value' => 300.00, // $300 discount
                        'tax_id' => $taxes[2]->id,
                        'tax_amount' => 420.00, // (4500 - 300) * 0.10
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Technical Director',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                    [
                        'label' => 'Finance Manager',
                        'assigned_signer_id' => 1,
                        'notify_signer' => false,
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

            // 4. Overdue Invoice - Mobile App Development
            [
                'company_id' => $companies[3]->id,
                'contact_id' => $companies[3]->contacts[0]->id ?? null,
                'invoice_number' => 'INV-000004',
                'reference_number' => 'MOBILE-APP-004',
                'invoice_date' => Carbon::now()->subDays(45)->format('Y-m-d'),
                'currency_id' => 1,
                'payment_term_id' => 1,
                'due_date' => Carbon::now()->subDays(15)->format('Y-m-d'), // Overdue
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => 1,
                'customer_note' => 'Mobile app development - iOS and Android platforms. Phase 1 completed.',
                'terms_and_conditions' => 'Payment due within 30 days of invoice date. Late fees apply after due date.',
                'subtotal' => 18000.00,
                'tax_id' => $taxes[0]->id, // Invoice-level: 15%
                'tax_amount' => 2430.00, // (18000 - 1000) * 0.15
                'discount_type' => 'fixed',
                'discount_value' => 1000.00,
                'discount_level' => 'invoice',
                'grand_total' => 19430.00,
                'balance_due' => 19430.00,
                'enable_organization_signature' => true,
                'enable_customer_signature' => false,
                'taxation_level' => 'invoice',
                'invoice_status' => 'overdue',
                'status' => 'approved',
                'items' => [
                    [
                        'item_id' => $items[1]->id,
                        'description' => 'Mobile App Development - iOS (80 hours)',
                        'quantity' => 80,
                        'rate' => 175.00,
                        'amount' => 14000.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null, // Invoice-level taxation
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[2]->id,
                        'description' => 'UI/UX Design for Mobile',
                        'quantity' => 40,
                        'rate' => 100.00,
                        'amount' => 4000.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Development Lead',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                ],
                'customer_signatures' => [],
                'files' => [],
                'communications' => [$companies[3]->contacts[0]->id ?? null],
            ],

            // 5. Paid Invoice - SEO & Content Services
            [
                'company_id' => $companies[4]->id,
                'contact_id' => $companies[4]->contacts[0]->id ?? null,
                'invoice_number' => 'INV-000005',
                'reference_number' => 'SEO-CONTENT-005',
                'invoice_date' => Carbon::now()->subDays(60)->format('Y-m-d'),
                'currency_id' => 1,
                'payment_term_id' => 2, // Net 15
                'due_date' => Carbon::now()->subDays(45)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => 4,
                'customer_note' => 'SEO optimization and content strategy implementation. Thank you for your prompt payment!',
                'terms_and_conditions' => 'Payment terms: Net 15 days. Full payment received - Thank you!',
                'subtotal' => 5200.00,
                'tax_id' => null,
                'tax_amount' => 0.00,
                'discount_type' => 'percentage',
                'discount_value' => 8.00,
                'discount_level' => 'invoice',
                'grand_total' => 5264.80, // (5200 * 0.92) + item taxes
                'balance_due' => 0.00,
                'enable_organization_signature' => false,
                'enable_customer_signature' => false,
                'taxation_level' => 'item',
                'invoice_status' => 'paid',
                'status' => 'approved',
                'items' => [
                    [
                        'item_id' => $items[3]->id,
                        'description' => 'SEO Optimization - On-Page & Technical',
                        'quantity' => 30,
                        'rate' => 80.00,
                        'amount' => 2400.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[2]->id, // 10%
                        'tax_amount' => 220.80, // (2400 * 0.92) * 0.10
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[6]->id,
                        'description' => 'Content Writing - SEO Optimized Articles',
                        'quantity' => 40,
                        'rate' => 60.00,
                        'amount' => 2400.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[4]->id, // No tax
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[7]->id,
                        'description' => 'Content Strategy Consulting',
                        'quantity' => 4,
                        'rate' => 100.00,
                        'amount' => 400.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[4]->id,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [],
                'customer_signatures' => [],
                'files' => [],
                'communications' => [$companies[4]->contacts[0]->id ?? null],
            ],
        ];

        foreach ($invoiceData as $data) {
            // Extract nested data
            $itemsData = $data['items'] ?? [];
            $orgSignatures = $data['organization_signatures'] ?? [];
            $custSignatures = $data['customer_signatures'] ?? [];
            $communications = $data['communications'] ?? [];
            $files = $data['files'] ?? [];
            
            unset($data['items'], $data['organization_signatures'], $data['customer_signatures'], $data['communications'], $data['files']);
            
            // Create invoice
            $data['created_by'] = 0;
            $invoice = Invoice::create($data);
            
            // Create items
            foreach ($itemsData as $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                $itemData['created_at'] = now();
                $itemData['updated_at'] = now();
                DB::table('invoice_items')->insert($itemData);
            }
            
            // Create organization signatures
            foreach ($orgSignatures as $signature) {
                $signature['invoice_id'] = $invoice->id;
                $signature['type'] = 'organization';
                $signature['created_at'] = now();
                $signature['updated_at'] = now();
                DB::table('invoice_signatures')->insert($signature);
            }
            
            // Create customer signatures
            foreach ($custSignatures as $signature) {
                $signature['invoice_id'] = $invoice->id;
                $signature['type'] = 'customer';
                $signature['created_at'] = now();
                $signature['updated_at'] = now();
                DB::table('invoice_signatures')->insert($signature);
            }
            
            // Sync communications
            if (!empty($communications)) {
                foreach ($communications as $contactId) {
                    if ($contactId) {
                        $invoice->emailCommunications()->create(['contact_id' => $contactId]);
                    }
                }
            }
            
            // Sync files
            if (!empty($files)) {
                foreach ($files as $fileId) {
                    $invoice->files()->create(['file_id' => $fileId]);
                }
            }

            // Create activity log
            $invoice->activityLogs()->create([
                'description' => 'Invoice created',
                'action_by' => 0,
            ]);
        }

        $this->command->info('Created ' . count($invoiceData) . ' invoices');
    }
}
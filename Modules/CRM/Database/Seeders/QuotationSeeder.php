<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\App\Models\Tax;
use Modules\CRM\App\Models\Company;
use Modules\CRM\App\Models\Contact;
use Modules\CRM\App\Models\Item;
use Modules\CRM\App\Models\ItemTemplate;
use Modules\CRM\App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class QuotationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('crm_companies')->truncate();
        DB::table('contacts')->truncate();
        DB::table('items')->truncate();
        DB::table('item_templates')->truncate();
        DB::table('item_template_items')->truncate();
        DB::table('quotations')->truncate();
        DB::table('quotation_invitation_links')->truncate();
        DB::table('taxes')->truncate();
        DB::table('quotation_items')->truncate();
        DB::table('quotation_signatures')->truncate();
        DB::table('quotation_files')->truncate();
        DB::table('quotation_email_communications')->truncate();

        DB::beginTransaction();

        try {
            // Create Taxes
            $taxes = $this->createTaxes();
            
            // Create Companies with Contacts
            $companiesData = $this->createCompanies();
            
            // Create Items
            $items = $this->createItems();
            
            // Create Item Templates
            $itemTemplates = $this->createItemTemplates($items);
            
            // Create Quotations
            $this->createQuotations($companiesData, $items, $taxes, $itemTemplates);

            DB::commit();
            
            $this->command->info('Quotation seeder completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Quotation seeder failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function createTaxes()
    {
        $taxes = [];
        $taxData = [
            ['name' => 'VAT 15%', 'rate' => 15.00],
            ['name' => 'GST 18%', 'rate' => 18.00],
            ['name' => 'Sales Tax 10%', 'rate' => 10.00],
            ['name' => 'Service Tax 5%', 'rate' => 5.00],
            ['name' => 'No Tax', 'rate' => 0.00],
        ];

        foreach ($taxData as $data) {
            $data['created_by'] = 0;
            $taxes[] = Tax::create($data);
        }

        $this->command->info('Created ' . count($taxes) . ' taxes');
        return $taxes;
    }

    protected function createCompanies()
    {
        $companiesData = [];
        $companyData = [
            [
                'company_code' => 'COMP-001',
                'name' => 'Tech Solutions Ltd.',
                'domain' => 'techsolutions.com',
                'email' => 'info@techsolutions.com',
                'phone_dial_code' => '+1',
                'phone_number' => '5550101',
                'tax_id' => 1,
                'currency_id' => 1,
                'billing_country_id' => 1,
                'billing_state_id' => 5,
                'billing_district' => 'Silicon Valley',
                'billing_zip_code' => '94102',
                'billing_address_line_1' => '123 Tech Street',
                'billing_address_line_2' => 'Suite 100',
                'billing_phone_dial_code' => '+1',
                'billing_phone_number' => '5550102',
                'shipping_same_as_billing' => false,
                'shipping_country_id' => 1,
                'shipping_state_id' => 5,
                'shipping_district' => 'Downtown',
                'shipping_zip_code' => '94103',
                'shipping_address_line_1' => '456 Shipping Ave',
                'shipping_address_line_2' => 'Floor 2',
                'shipping_phone_dial_code' => '+1',
                'shipping_phone_number' => '5550103',
                'status' => true,
                'contacts' => [
                    [
                        'contact_code' => 'CONT-001',
                        'first_name' => 'John',
                        'last_name' => 'Smith',
                        'email' => 'john.smith@techsolutions.com',
                        'work_phone_dial_code' => '+1',
                        'work_phone_number' => '5551001',
                        'mobile_phone_dial_code' => '+1',
                        'mobile_phone_number' => '5551002',
                        'is_customer' => true,
                        'is_primary' => true,
                        'password' => 'password123',
                    ],
                    [
                        'contact_code' => 'CONT-002',
                        'first_name' => 'Sarah',
                        'last_name' => 'Johnson',
                        'email' => 'sarah.johnson@techsolutions.com',
                        'work_phone_dial_code' => '+1',
                        'work_phone_number' => '5551003',
                        'mobile_phone_dial_code' => '+1',
                        'mobile_phone_number' => '5551004',
                        'is_customer' => true,
                        'is_primary' => false,
                        'password' => 'password123',
                    ],
                ],
            ],
            [
                'company_code' => 'COMP-002',
                'name' => 'Global Marketing Agency',
                'domain' => 'globalmarketing.com',
                'email' => 'contact@globalmarketing.com',
                'phone_dial_code' => '+1',
                'phone_number' => '5550202',
                'tax_id' => 1,
                'currency_id' => 1,
                'billing_country_id' => 1,
                'billing_state_id' => 33,
                'billing_district' => 'Manhattan',
                'billing_zip_code' => '10001',
                'billing_address_line_1' => '456 Marketing Ave',
                'billing_address_line_2' => 'Building A',
                'billing_phone_dial_code' => '+1',
                'billing_phone_number' => '5550203',
                'shipping_same_as_billing' => true,
                'status' => true,
                'contacts' => [
                    [
                        'contact_code' => 'CONT-003',
                        'first_name' => 'Michael',
                        'last_name' => 'Brown',
                        'email' => 'michael.brown@globalmarketing.com',
                        'work_phone_dial_code' => '+1',
                        'work_phone_number' => '5552001',
                        'mobile_phone_dial_code' => '+1',
                        'mobile_phone_number' => '5552002',
                        'is_customer' => true,
                        'is_primary' => true,
                        'password' => 'password123',
                    ],
                ],
            ],
            [
                'company_code' => 'COMP-003',
                'name' => 'Innovative Design Studio',
                'domain' => 'innovativedesign.com',
                'email' => 'hello@innovativedesign.com',
                'phone_dial_code' => '+1',
                'phone_number' => '5550303',
                'tax_id' => 2,
                'currency_id' => 1,
                'billing_country_id' => 1,
                'billing_state_id' => 5,
                'billing_district' => 'Beverly Hills',
                'billing_zip_code' => '90001',
                'billing_address_line_1' => '789 Design Boulevard',
                'billing_address_line_2' => 'Studio 5',
                'billing_phone_dial_code' => '+1',
                'billing_phone_number' => '5550304',
                'shipping_same_as_billing' => false,
                'shipping_country_id' => 1,
                'shipping_state_id' => 5,
                'shipping_district' => 'Hollywood',
                'shipping_zip_code' => '90002',
                'shipping_address_line_1' => '321 Creative Lane',
                'shipping_address_line_2' => '',
                'shipping_phone_dial_code' => '+1',
                'shipping_phone_number' => '5550305',
                'status' => true,
                'contacts' => [
                    [
                        'contact_code' => 'CONT-004',
                        'first_name' => 'Emily',
                        'last_name' => 'Davis',
                        'email' => 'emily.davis@innovativedesign.com',
                        'work_phone_dial_code' => '+1',
                        'work_phone_number' => '5553001',
                        'mobile_phone_dial_code' => '+1',
                        'mobile_phone_number' => '5553002',
                        'is_customer' => true,
                        'is_primary' => true,
                        'password' => 'password123',
                    ],
                ],
            ],
            [
                'company_code' => 'COMP-004',
                'name' => 'Enterprise Solutions Ltd',
                'domain' => 'enterprisesolutions.com',
                'email' => 'support@enterprisesolutions.com',
                'phone_dial_code' => '+1',
                'phone_number' => '5550404',
                'tax_id' => 1,
                'currency_id' => 1,
                'billing_country_id' => 1,
                'billing_state_id' => 14,
                'billing_district' => 'Loop',
                'billing_zip_code' => '60601',
                'billing_address_line_1' => '321 Enterprise Way',
                'billing_address_line_2' => 'Tower B, Floor 10',
                'billing_phone_dial_code' => '+1',
                'billing_phone_number' => '5550405',
                'shipping_same_as_billing' => true,
                'status' => true,
                'contacts' => [
                    [
                        'contact_code' => 'CONT-005',
                        'first_name' => 'David',
                        'last_name' => 'Wilson',
                        'email' => 'david.wilson@enterprisesolutions.com',
                        'work_phone_dial_code' => '+1',
                        'work_phone_number' => '5554001',
                        'mobile_phone_dial_code' => '+1',
                        'mobile_phone_number' => '5554002',
                        'is_customer' => true,
                        'is_primary' => true,
                        'password' => 'password123',
                    ],
                ],
            ],
            [
                'company_code' => 'COMP-005',
                'name' => 'Creative Media Group',
                'domain' => 'creativemedia.com',
                'email' => 'info@creativemedia.com',
                'phone_dial_code' => '+1',
                'phone_number' => '5550505',
                'tax_id' => 2,
                'currency_id' => 1,
                'billing_country_id' => 1,
                'billing_state_id' => 10,
                'billing_district' => 'South Beach',
                'billing_zip_code' => '33101',
                'billing_address_line_1' => '654 Media Plaza',
                'billing_address_line_2' => 'Level 3',
                'billing_phone_dial_code' => '+1',
                'billing_phone_number' => '5550506',
                'shipping_same_as_billing' => false,
                'shipping_country_id' => 1,
                'shipping_state_id' => 10,
                'shipping_district' => 'Downtown Miami',
                'shipping_zip_code' => '33102',
                'shipping_address_line_1' => '987 Distribution St',
                'shipping_address_line_2' => 'Warehouse 7',
                'shipping_phone_dial_code' => '+1',
                'shipping_phone_number' => '5550507',
                'status' => true,
                'contacts' => [
                    [
                        'contact_code' => 'CONT-006',
                        'first_name' => 'Lisa',
                        'last_name' => 'Martinez',
                        'email' => 'lisa.martinez@creativemedia.com',
                        'work_phone_dial_code' => '+1',
                        'work_phone_number' => '5555001',
                        'mobile_phone_dial_code' => '+1',
                        'mobile_phone_number' => '5555002',
                        'is_customer' => true,
                        'is_primary' => true,
                        'password' => 'password123',
                    ],
                ],
            ],
        ];

        foreach ($companyData as $data) {
            // Extract contacts data
            $contactsData = $data['contacts'] ?? [];
            unset($data['contacts']);
            
            // Create company
            $data['created_by'] = 0;
            $company = Company::create($data);
            
            // Create contacts
            $contacts = [];
            foreach ($contactsData as $contactData) {
                $contactData['company_id'] = $company->id;
                $contactData['created_by'] = 0;
                $contactData['password'] = encrypt($contactData['password']);
                $contacts[] = Contact::create($contactData);
            }
            
            $companiesData[] = [
                'company' => $company,
                'contacts' => $contacts
            ];
        }

        $this->command->info('Created ' . count($companiesData) . ' companies with contacts');
        return $companiesData;
    }

    protected function createItems()
    {
        $items = [];
        $itemData = [
            [
                'title' => 'Web Development Service',
                'description' => 'Full-stack web development services',
                'amount' => 150.00,
                'item_type_id' => null,
            ],
            [
                'title' => 'Mobile App Development',
                'description' => 'iOS and Android app development',
                'amount' => 175.00,
                'item_type_id' => null,
            ],
            [
                'title' => 'UI/UX Design',
                'description' => 'User interface and experience design',
                'amount' => 100.00,
                'item_type_id' => null,
            ],
            [
                'title' => 'SEO Optimization',
                'description' => 'Search engine optimization services',
                'amount' => 80.00,
                'item_type_id' => null,
            ],
            [
                'title' => 'Cloud Hosting',
                'description' => 'Cloud hosting and maintenance',
                'amount' => 50.00,
                'item_type_id' => null,
            ],
            [
                'title' => 'Consulting Services',
                'description' => 'IT consulting and strategy',
                'amount' => 200.00,
                'item_type_id' => null,
            ],
            [
                'title' => 'Content Writing',
                'description' => 'Professional content writing services',
                'amount' => 60.00,
                'item_type_id' => null,
            ],
            [
                'title' => 'Digital Marketing',
                'description' => 'Social media and digital marketing',
                'amount' => 120.00,
                'item_type_id' => null,
            ],
        ];

        foreach ($itemData as $data) {
            $data['created_by'] = 0;
            $items[] = Item::create($data);
        }

        $this->command->info('Created ' . count($items) . ' items');
        return $items;
    }

    protected function createItemTemplates($items)
    {
        $templates = [];
        $templateData = [
            [
                'name' => 'Standard Web Package',
                'description' => 'Complete web development package',
                'items' => [$items[0]->id, $items[2]->id],
            ],
            [
                'name' => 'Mobile App Package',
                'description' => 'Complete mobile app development',
                'items' => [$items[1]->id, $items[2]->id],
            ],
            [
                'name' => 'Marketing Bundle',
                'description' => 'Digital marketing package',
                'items' => [$items[3]->id, $items[6]->id, $items[7]->id],
            ],
            [
                'name' => 'Enterprise Suite',
                'description' => 'Complete enterprise solution',
                'items' => [$items[0]->id, $items[4]->id, $items[5]->id],
            ],
            [
                'name' => 'Content & SEO Package',
                'description' => 'Content creation and SEO optimization',
                'items' => [$items[3]->id, $items[6]->id],
            ],
        ];

        foreach ($templateData as $data) {
            $itemIds = $data['items'];
            unset($data['items']);
            
            $data['created_by'] = 0;
            $template = ItemTemplate::create($data);
            
            // Sync items
            $template->items()->sync($itemIds);
            
            $templates[] = $template;
        }

        $this->command->info('Created ' . count($templates) . ' item templates');
        return $templates;
    }

    protected function getCompanyContactIds($companiesData, $companyIndex)
    {
        $contactIds = [];
        if (isset($companiesData[$companyIndex]['contacts'])) {
            foreach ($companiesData[$companyIndex]['contacts'] as $contact) {
                $contactIds[] = $contact->id;
            }
        }
        return $contactIds;
    }

    protected function createQuotations($companiesData, $items, $taxes, $itemTemplates)
    {
        $quotationData = [
            [
                'company_id' => $companiesData[0]['company']->id,
                'contact_id' => $companiesData[0]['contacts'][0]->id ?? null,
                'quotation_number' => 'QT-2025-001',
                'reference_number' => 'REF-001',
                'quotation_date' => now()->subDays(30)->format('Y-m-d'),
                'expiry_date' => now()->addDays(30)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => $itemTemplates[0]->id,
                'customer_note' => 'Thank you for choosing our web development services.',
                'terms_and_conditions' => 'Payment terms: 50% upfront, 50% on completion. Net 30 days.',
                'subtotal' => 8000.00,
                'tax_id' => null, // taxation_level = 'item', so quote-level tax is null
                'tax_amount' => 0.00,
                'discount_type' => 'percentage',
                'discount_value' => 5.00,
                'discount_level' => 'quote', // Quote-level discount
                'grand_total' => 8740.00, // 8000 - 5% (400) = 7600 + item taxes (1200) = 8800
                'enable_organization_signature' => true,
                'enable_customer_signature' => true,
                'taxation_level' => 'item', // Tax at item level
                'quotation_status' => 'draft',
                'items' => [
                    [
                        'item_id' => $items[0]->id,
                        'description' => 'Web Development - 40 hours',
                        'quantity' => 40,
                        'rate' => 150.00,
                        'amount' => 6000.00,
                        'discount_type' => null, // discount_level is 'quote', so item discount is null
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[0]->id, // taxation_level = 'item', so tax_id required
                        'tax_amount' => 900.00, // 6000 * 15%
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[2]->id,
                        'description' => 'UI/UX Design - 20 hours',
                        'quantity' => 20,
                        'rate' => 100.00,
                        'amount' => 2000.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[0]->id,
                        'tax_amount' => 300.00, // 2000 * 15%
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Sales Manager',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                    [
                        'label' => 'CEO',
                        'assigned_signer_id' => 1,
                        'notify_signer' => false,
                    ],
                ],
                'customer_signatures' => [
                    [
                        'label' => 'Customer Representative',
                    ],
                ],
                'files' => [],
                'communications' => $this->getCompanyContactIds($companiesData, 0),
            ],
            [
                'company_id' => $companiesData[1]['company']->id,
                'contact_id' => $companiesData[1]['contacts'][0]->id ?? null,
                'quotation_number' => 'QT-2025-002',
                'reference_number' => 'REF-002',
                'quotation_date' => now()->subDays(10)->format('Y-m-d'),
                'expiry_date' => now()->addDays(20)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => $itemTemplates[2]->id,
                'customer_note' => 'Comprehensive digital marketing strategy for your business growth.',
                'terms_and_conditions' => 'Monthly subscription model. Payment due on the 1st of each month.',
                'subtotal' => 3400.00,
                'tax_id' => $taxes[1]->id, // taxation_level = 'quote', so quote-level tax required
                'tax_amount' => 594.00, // (3400 - 200) * 18% = 576
                'discount_type' => 'fixed',
                'discount_value' => 200.00,
                'discount_level' => 'quote', // Quote-level discount
                'grand_total' => 3794.00, // 3400 - 200 = 3200 + 594 tax
                'enable_organization_signature' => true,
                'enable_customer_signature' => false,
                'taxation_level' => 'quote', // Tax at quote level
                'quotation_status' => 'pending_approval',
                'items' => [
                    [
                        'item_id' => $items[3]->id,
                        'description' => 'SEO Optimization',
                        'quantity' => 20,
                        'rate' => 80.00,
                        'amount' => 1600.00,
                        'discount_type' => null, // discount_level is 'quote'
                        'discount_value' => 0.00,
                        'tax_id' => null, // taxation_level = 'quote', so item tax is null
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[7]->id,
                        'description' => 'Digital Marketing',
                        'quantity' => 15,
                        'rate' => 120.00,
                        'amount' => 1800.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Marketing Director',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                ],
                'customer_signatures' => [],
                'files' => [],
                'communications' => $this->getCompanyContactIds($companiesData, 1),
            ],
            [
                'company_id' => $companiesData[2]['company']->id,
                'contact_id' => $companiesData[2]['contacts'][0]->id ?? null,
                'quotation_number' => 'QT-2025-003',
                'reference_number' => 'REF-003',
                'quotation_date' => now()->subDays(15)->format('Y-m-d'),
                'expiry_date' => now()->addDays(45)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => $itemTemplates[1]->id,
                'customer_note' => 'Native iOS and Android app development with modern UI/UX.',
                'terms_and_conditions' => 'Milestone-based payment schedule. 30% upfront, 40% mid-project, 30% completion.',
                'subtotal' => 13500.00,
                'tax_id' => null, // taxation_level = 'item'
                'tax_amount' => 0.00,
                'discount_type' => null,
                'discount_value' => 0.00,
                'discount_level' => 'item', // Item-level discount
                'grand_total' => 14535.00, // Items with individual discounts and taxes
                'enable_organization_signature' => true,
                'enable_customer_signature' => true,
                'taxation_level' => 'item',
                'quotation_status' => 'save_and_send',
                'items' => [
                    [
                        'item_id' => $items[1]->id,
                        'description' => 'Mobile App Development',
                        'quantity' => 60,
                        'rate' => 175.00,
                        'amount' => 10500.00,
                        'discount_type' => 'percentage', // discount_level = 'item'
                        'discount_value' => 10.00, // 10% discount
                        'tax_id' => $taxes[1]->id, // taxation_level = 'item'
                        'tax_amount' => 1701.00, // (10500 - 10%) * 18%
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[2]->id,
                        'description' => 'UI/UX Design',
                        'quantity' => 30,
                        'rate' => 100.00,
                        'amount' => 3000.00,
                        'discount_type' => 'fixed',
                        'discount_value' => 150.00, // Fixed $150 discount
                        'tax_id' => $taxes[0]->id,
                        'tax_amount' => 427.50, // (3000 - 150) * 15%
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
                'communications' => $this->getCompanyContactIds($companiesData, 2),
            ],
            [
                'company_id' => $companiesData[3]['company']->id,
                'contact_id' => $companiesData[3]['contacts'][0]->id ?? null,
                'quotation_number' => 'QT-2025-004',
                'reference_number' => 'REF-004',
                'quotation_date' => now()->format('Y-m-d'),
                'expiry_date' => now()->addDays(30)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => $itemTemplates[3]->id,
                'customer_note' => 'Strategic IT consulting and cloud infrastructure implementation.',
                'terms_and_conditions' => 'Retainer agreement - monthly billing. Auto-renewal unless cancelled 30 days prior.',
                'subtotal' => 5600.00,
                'tax_id' => $taxes[0]->id, // taxation_level = 'quote'
                'tax_amount' => 840.00, // 5600 * 15%
                'discount_type' => null,
                'discount_value' => 0.00,
                'discount_level' => 'quote',
                'grand_total' => 6440.00, // 5600 + 840
                'enable_organization_signature' => false,
                'enable_customer_signature' => false,
                'taxation_level' => 'quote', // Tax at quote level
                'quotation_status' => 'draft',
                'items' => [
                    [
                        'item_id' => $items[5]->id,
                        'description' => 'IT Consulting',
                        'quantity' => 25,
                        'rate' => 200.00,
                        'amount' => 5000.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null, // taxation_level = 'quote'
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[4]->id,
                        'description' => 'Cloud Hosting Setup',
                        'quantity' => 12,
                        'rate' => 50.00,
                        'amount' => 600.00,
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
                'communications' => $this->getCompanyContactIds($companiesData, 3),
            ],
            [
                'company_id' => $companiesData[4]['company']->id,
                'contact_id' => $companiesData[4]['contacts'][0]->id ?? null,
                'quotation_number' => 'QT-2025-005',
                'reference_number' => 'REF-005',
                'quotation_date' => now()->subDays(20)->format('Y-m-d'),
                'expiry_date' => now()->subDays(5)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => $itemTemplates[4]->id,
                'customer_note' => 'Professional content creation and SEO optimization services.',
                'terms_and_conditions' => 'Payment on delivery. All content rights transferred upon full payment.',
                'subtotal' => 3600.00,
                'tax_id' => null, // taxation_level = 'item'
                'tax_amount' => 0.00,
                'discount_type' => null,
                'discount_value' => 0.00,
                'discount_level' => 'item', // Item-level discount
                'grand_total' => 3500.00, // Items with individual discounts and taxes
                'enable_organization_signature' => true,
                'enable_customer_signature' => true,
                'taxation_level' => 'item',
                'quotation_status' => 'draft',
                'items' => [
                    [
                        'item_id' => $items[6]->id,
                        'description' => 'Content Writing',
                        'quantity' => 40,
                        'rate' => 60.00,
                        'amount' => 2400.00,
                        'discount_type' => 'percentage',
                        'discount_value' => 5.00, // 5% discount
                        'tax_id' => $taxes[4]->id, // No Tax (0%)
                        'tax_amount' => 0.00, // (2400 - 5%) * 0%
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[3]->id,
                        'description' => 'SEO Optimization',
                        'quantity' => 15,
                        'rate' => 80.00,
                        'amount' => 1200.00,
                        'discount_type' => 'fixed',
                        'discount_value' => 100.00, // $100 discount
                        'tax_id' => $taxes[2]->id, // 10% tax
                        'tax_amount' => 110.00, // (1200 - 100) * 10%
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
                'customer_signatures' => [
                    [
                        'label' => 'Client Approver',
                    ],
                ],
                'files' => [],
                'communications' => $this->getCompanyContactIds($companiesData, 4),
            ],
            [
                'company_id' => $companiesData[0]['company']->id,
                'contact_id' => $companiesData[0]['contacts'][1]->id ?? $companiesData[0]['contacts'][0]->id,
                'quotation_number' => 'QT-2025-006',
                'reference_number' => 'REF-006',
                'quotation_date' => now()->subDays(5)->format('Y-m-d'),
                'expiry_date' => now()->addDays(25)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => $itemTemplates[3]->id,
                'customer_note' => 'Scalable enterprise web platform with cloud infrastructure.',
                'terms_and_conditions' => 'Net 30 payment terms. Support and maintenance included for 6 months.',
                'subtotal' => 14000.00,
                'tax_id' => $taxes[0]->id, // taxation_level = 'quote'
                'tax_amount' => 1491.00, // (14000 - 7.5%) * 15%
                'discount_type' => 'percentage',
                'discount_value' => 7.50,
                'discount_level' => 'quote', // Quote-level discount
                'grand_total' => 14441.00, // 14000 - 7.5% + tax
                'enable_organization_signature' => true,
                'enable_customer_signature' => true,
                'taxation_level' => 'quote', // Tax at quote level
                'quotation_status' => 'pending_approval',
                'items' => [
                    [
                        'item_id' => $items[0]->id,
                        'description' => 'Web Development',
                        'quantity' => 80,
                        'rate' => 150.00,
                        'amount' => 12000.00,
                        'discount_type' => null, // discount_level = 'quote'
                        'discount_value' => 0.00,
                        'tax_id' => null, // taxation_level = 'quote'
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[5]->id,
                        'description' => 'Consulting Services',
                        'quantity' => 10,
                        'rate' => 200.00,
                        'amount' => 2000.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Solutions Architect',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                ],
                'customer_signatures' => [
                    [
                        'label' => 'IT Manager',
                    ],
                ],
                'files' => [],
                'communications' => $this->getCompanyContactIds($companiesData, 0),
            ],
            [
                'company_id' => $companiesData[1]['company']->id,
                'contact_id' => $companiesData[1]['contacts'][0]->id ?? null,
                'quotation_number' => 'QT-2025-007',
                'reference_number' => 'REF-007',
                'quotation_date' => now()->subDays(25)->format('Y-m-d'),
                'expiry_date' => now()->addDays(35)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => $itemTemplates[2]->id,
                'customer_note' => 'Complete marketing solution with content and SEO.',
                'terms_and_conditions' => 'Quarterly billing cycle. Performance metrics review monthly.',
                'subtotal' => 8600.00,
                'tax_id' => null, // taxation_level = 'item'
                'tax_amount' => 0.00,
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'discount_level' => 'quote', // Quote-level discount
                'grand_total' => 8234.00, // Items after quote discount + item taxes
                'enable_organization_signature' => true,
                'enable_customer_signature' => true,
                'taxation_level' => 'item', // Tax at item level
                'quotation_status' => 'save_and_send',
                'items' => [
                    [
                        'item_id' => $items[7]->id,
                        'description' => 'Digital Marketing',
                        'quantity' => 30,
                        'rate' => 120.00,
                        'amount' => 3600.00,
                        'discount_type' => null, // discount_level = 'quote'
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[1]->id, // taxation_level = 'item'
                        'tax_amount' => 583.20, // Applied after quote discount
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[6]->id,
                        'description' => 'Content Writing',
                        'quantity' => 50,
                        'rate' => 60.00,
                        'amount' => 3000.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[4]->id, // No tax
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[3]->id,
                        'description' => 'SEO Services',
                        'quantity' => 25,
                        'rate' => 80.00,
                        'amount' => 2000.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => $taxes[2]->id,
                        'tax_amount' => 180.00, // 10% tax
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Marketing Head',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                    [
                        'label' => 'Account Manager',
                        'assigned_signer_id' => 1,
                        'notify_signer' => false,
                    ],
                ],
                'customer_signatures' => [
                    [
                        'label' => 'Marketing Director',
                    ],
                ],
                'files' => [],
                'communications' => $this->getCompanyContactIds($companiesData, 1),
            ],
            [
                'company_id' => $companiesData[3]['company']->id,
                'contact_id' => $companiesData[3]['contacts'][0]->id ?? null,
                'quotation_number' => 'QT-2025-008',
                'reference_number' => 'REF-008',
                'quotation_date' => now()->subDays(8)->format('Y-m-d'),
                'expiry_date' => now()->addDays(22)->format('Y-m-d'),
                'project_id' => 1,
                'sale_person_id' => 1,
                'item_template_id' => null,
                'customer_note' => 'Enterprise cloud migration and infrastructure setup.',
                'terms_and_conditions' => 'Phased payment plan: 40% start, 30% migration, 30% completion.',
                'subtotal' => 9200.00,
                'tax_id' => $taxes[0]->id, // taxation_level = 'quote'
                'tax_amount' => 1426.50, // (9200 - 15%) * 15%
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'discount_level' => 'quote',
                'grand_total' => 9246.50, // 9200 - 15% + tax
                'enable_organization_signature' => true,
                'enable_customer_signature' => false,
                'taxation_level' => 'quote', // Tax at quote level
                'quotation_status' => 'draft',
                'items' => [
                    [
                        'item_id' => $items[5]->id,
                        'description' => 'Migration Consulting',
                        'quantity' => 40,
                        'rate' => 200.00,
                        'amount' => 8000.00,
                        'discount_type' => null, // discount_level = 'quote'
                        'discount_value' => 0.00,
                        'tax_id' => null, // taxation_level = 'quote'
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                    [
                        'item_id' => $items[4]->id,
                        'description' => 'Cloud Setup & Hosting',
                        'quantity' => 24,
                        'rate' => 50.00,
                        'amount' => 1200.00,
                        'discount_type' => null,
                        'discount_value' => 0.00,
                        'tax_id' => null,
                        'tax_amount' => 0.00,
                        'is_same_as_item' => true,
                    ],
                ],
                'organization_signatures' => [
                    [
                        'label' => 'Cloud Architect',
                        'assigned_signer_id' => 1,
                        'notify_signer' => true,
                    ],
                ],
                'customer_signatures' => [],
                'files' => [],
                'communications' => $this->getCompanyContactIds($companiesData, 3),
            ],
        ];

        foreach ($quotationData as $data) {
            // Extract nested data
            $itemsData = $data['items'] ?? [];
            $orgSignatures = $data['organization_signatures'] ?? [];
            $custSignatures = $data['customer_signatures'] ?? [];
            $communications = $data['communications'] ?? [];
            $files = $data['files'] ?? [];
            
            unset($data['items'], $data['organization_signatures'], $data['customer_signatures'], $data['communications'], $data['files']);
            
            // Create quotation
            $data['created_by'] = 0;
            $quotation = Quotation::create($data);
            
            // Create quotation items
            foreach ($itemsData as $itemData) {
                $itemData['quotation_id'] = $quotation->id;
                $itemData['created_at'] = now();
                $itemData['updated_at'] = now();
                DB::table('quotation_items')->insert($itemData);
            }
            
            // Create organization signatures
            foreach ($orgSignatures as $signature) {
                $signature['quotation_id'] = $quotation->id;
                $signature['type'] = 'organization';
                $signature['created_at'] = now();
                $signature['updated_at'] = now();
                DB::table('quotation_signatures')->insert($signature);
            }
            
            // Create customer signatures
            foreach ($custSignatures as $signature) {
                $signature['quotation_id'] = $quotation->id;
                $signature['type'] = 'customer';
                $signature['created_at'] = now();
                $signature['updated_at'] = now();
                DB::table('quotation_signatures')->insert($signature);
            }
            
            // Sync communications (contacts)
            if (!empty($communications)) {
                foreach ($communications as $contactId) {
                    $quotation->emailCommunications()->create(['contact_id' => $contactId]);
                }
            }
            
            // Sync files if needed
            if (!empty($files)) {
                $quotation->files()->sync($files);
            }
        }

        $this->command->info('Created ' . count($quotationData) . ' quotations');
    }
}
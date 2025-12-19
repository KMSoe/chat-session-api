<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payroll\App\Models\Benefit\MPFTrustee;

class MPFTrusteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trustees = [
            "AIA Company (Trustee) Limited",
            "Bank Consortium Trust Company Limited",
            "Bank of Communications Trustee Limited",
            "Bank of East Asia (Trustees) Limited",
            "BOCI-Prudential Trustee Limited",
            "China Life Trustees Limited",
            "Cititrust Limited",
            "HSBC Institutional Trust Services (Asia) Limited",
            "HSBC Provident Fund Trustee (Hong Kong) Limited",
            "Manulife Provident Funds Trust Company Limited",
            "Principal Trust Company (Asia) Limited",
            "Standard Chartered Trustee (Hong Kong) Limited",
            "Sun Life Pension Trust Limited",
            "Sun Life Trustee Company Limited",
            "YF Life Trustees Limited",
        ];

        foreach ($trustees as $name) {
            MPFTrustee::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Enums\CoaAccountType;
use App\Enums\FundCategory;
use App\Models\CoaAccount;
use Illuminate\Database\Seeder;

class CoaAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Aset / Kas (1-xxxx)
            [
                'code' => '1-1001',
                'name' => 'Kas Payment Gateway',
                'type' => CoaAccountType::Asset,
                'fund_category' => FundCategory::Amil,
                'description' => 'Rekening kliring / penampungan transaksi payment gateway masuk.',
            ],
            [
                'code' => '1-1002',
                'name' => 'Kas / Bank Operasional',
                'type' => CoaAccountType::Asset,
                'fund_category' => FundCategory::Amil,
                'description' => 'Rekening utama lembaga untuk penyaluran dan operasional.',
            ],
            [
                'code' => '1-1003',
                'name' => 'Bank Penampungan Zakat',
                'type' => CoaAccountType::Asset,
                'fund_category' => FundCategory::Zakat,
                'description' => 'Rekening khusus penampungan dana zakat.',
            ],
            [
                'code' => '1-1004',
                'name' => 'Bank Penampungan Infaq',
                'type' => CoaAccountType::Asset,
                'fund_category' => FundCategory::InfaqBebas,
                'description' => 'Rekening penampungan dana infaq/sedekah.',
            ],
            [
                'code' => '1-1005',
                'name' => 'Bank Penampungan Wakaf Uang',
                'type' => CoaAccountType::Asset,
                'fund_category' => FundCategory::Wakaf,
                'description' => 'Rekening penampungan dana wakaf uang.',
            ],

            // Pendapatan (4-xxxx)
            [
                'code' => '4-1001',
                'name' => 'Pendapatan Zakat',
                'type' => CoaAccountType::Revenue,
                'fund_category' => FundCategory::Zakat,
                'description' => 'Penerimaan dana zakat maal, fitrah, dan profesi (PSAK 109).',
            ],
            [
                'code' => '4-2001',
                'name' => 'Pendapatan Infaq / Sedekah Tidak Terikat',
                'type' => CoaAccountType::Revenue,
                'fund_category' => FundCategory::InfaqBebas,
                'description' => 'Penerimaan donasi infaq umum tanpa pembatasan program.',
            ],
            [
                'code' => '4-2002',
                'name' => 'Pendapatan Infaq / Sedekah Terikat',
                'type' => CoaAccountType::Revenue,
                'fund_category' => FundCategory::InfaqTerikat,
                'description' => 'Penerimaan donasi untuk kampanye spesifik yang terikat peruntukannya.',
            ],
            [
                'code' => '4-2003',
                'name' => 'Pendapatan Wakaf Uang',
                'type' => CoaAccountType::Revenue,
                'fund_category' => FundCategory::Wakaf,
                'description' => 'Penerimaan sertifikat wakaf uang.',
            ],
            [
                'code' => '4-3001',
                'name' => 'Bagian Amil dari Zakat',
                'type' => CoaAccountType::Revenue,
                'fund_category' => FundCategory::Amil,
                'description' => 'Hak amil dari penerimaan zakat (maksimal 12.5% / 1/8 sesuai PSAK 109).',
            ],

            // Beban / Penyaluran (5-xxxx)
            [
                'code' => '5-1001',
                'name' => 'Beban Penyaluran Dana Zakat',
                'type' => CoaAccountType::Expense,
                'fund_category' => FundCategory::Zakat,
                'description' => 'Penyaluran dana zakat kepada 8 asnaf (PSAK 109).',
            ],
            [
                'code' => '5-2001',
                'name' => 'Beban Penyaluran Dana Infaq / Sedekah',
                'type' => CoaAccountType::Expense,
                'fund_category' => FundCategory::InfaqBebas,
                'description' => 'Penyaluran bantuan sosial kemanusiaan dan infaq.',
            ],
            [
                'code' => '5-2002',
                'name' => 'Beban Penyaluran Infaq Terikat',
                'type' => CoaAccountType::Expense,
                'fund_category' => FundCategory::InfaqTerikat,
                'description' => 'Penyaluran dana kampanye infaq terikat program.',
            ],
            [
                'code' => '5-3001',
                'name' => 'Beban Operasional Amil',
                'type' => CoaAccountType::Expense,
                'fund_category' => FundCategory::Amil,
                'description' => 'Biaya operasional pengelolaan lembaga amil zakat.',
            ],
        ];

        foreach ($accounts as $acc) {
            CoaAccount::updateOrCreate(['code' => $acc['code']], $acc);
        }
    }
}

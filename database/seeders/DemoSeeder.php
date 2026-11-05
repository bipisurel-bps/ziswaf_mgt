<?php

namespace Database\Seeders;

use App\Enums\ApprovalStatus;
use App\Enums\AsnafCategory;
use App\Enums\CampaignCategory;
use App\Enums\CampaignStatus;
use App\Enums\DisbursementRequestStatus;
use App\Enums\PaymentStatus;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\DisbursementRequest;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Fundraiser;
use App\Models\Mustahik;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $accounting = app(AccountingService::class);

        // 1. Admin User
        $admin = config('ziswaf.seed_admin');
        $user = User::updateOrCreate(
            ['email' => $admin['email']],
            ['name' => $admin['name'], 'password' => $admin['password']]
        );

        // 2. Fundraisers (Relawan / Mitra)
        $fundraisers = [
            [
                'name' => 'Ustadz Ahmad Al-Faruq (Da\'i Pelosok)',
                'phone_number' => '6281288990001',
                'referral_code' => 'DAI-AHMAD',
                'total_collected' => 28_500_000,
            ],
            [
                'name' => 'Komunitas Pemuda Peduli Dhuafa',
                'phone_number' => '6281399887766',
                'referral_code' => 'PEMUDA-PEDULI',
                'total_collected' => 45_200_000,
            ],
            [
                'name' => 'dr. Sarah Nurhaliza (Relawan Medis)',
                'phone_number' => '6281765432100',
                'referral_code' => 'SARAH-MEDIK',
                'total_collected' => 38_750_000,
            ],
        ];

        $fundraiserModels = [];
        foreach ($fundraisers as $f) {
            $fundraiserModels[$f['referral_code']] = Fundraiser::updateOrCreate(
                ['referral_code' => $f['referral_code']],
                $f
            );
        }

        // 3. Realistic Campaigns with High-Quality Charity Imagery
        $campaignData = [
            [
                'title' => 'Zakat Maal: Berdayakan 500 Keluarga Asnaf Mandiri & Berdikari',
                'slug' => 'zakat-maal-500-keluarga-asnaf',
                'category' => CampaignCategory::Zakat,
                'target_amount' => 150_000_000,
                'collected_amount' => 68_500_000,
                'cover_image' => 'https://images.unsplash.com/photo-1542810634-71277d95dcbb?auto=format&fit=crop&w=1200&q=80',
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(),
                'status' => CampaignStatus::Active,
                'description' => "## Mengapa Zakat Anda Begitu Berarti?\n\nSetiap rupiah zakat yang Anda tunaikan melalui program ini langsung dialokasikan untuk membiayai kebutuhan pangan darurat, modal kerja produktif, serta biaya pendidikan anak-anak dhuafa dari 8 golongan asnaf yang sah sesuai syariat Islam.\n\n### Rencana Alokasi Program:\n1. **Paket Sembako Bergizi**: 500 paket untuk lansia sebatang kara dan keluarga janda dhuafa.\n2. **Bantuan Modal Usaha Mikro**: Gerobak dagang dan modal bibit sayur/ternak mandiri.\n3. **Beasiswa Cendekia Asnaf**: Menjamin SPP dan seragam santri berprestasi.\n\n*Penyaluran diawasi langsung oleh Dewan Pengawas Syariah dan dilaporkan secara berkala.*",
            ],
            [
                'title' => 'Sedekah Pangan Rutin untuk Santri Yatim & Dhuafa Penghafal Al-Qur\'an',
                'slug' => 'sedekah-pangan-santri-penghafal-quran',
                'category' => CampaignCategory::Infaq,
                'target_amount' => 75_000_000,
                'collected_amount' => 42_750_000,
                'cover_image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1200&q=80',
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonths(5)->toDateString(),
                'status' => CampaignStatus::Active,
                'description' => "Rasulullah SAW bersabda: *\"Aku dan orang yang mengasuh anak yatim akan berada di surga seperti ini\"* (beliau mengisyaratkan jari telunjuk dan jari tengahnya).\n\nProgram ini menyuplai beras premium, daging segar, susu, dan kebutuhan gizi seimbang untuk 120 santri yatim dan dhuafa di Pesantren Tahfidz Baitul Qur'an. Mari hadirkan senyuman dan energi bagi mereka yang setia menjaga ayat-ayat suci Al-Qur'an.",
            ],
            [
                'title' => 'Wakaf Produktif: Pengeboran Sumur Air Bersih Desa Kekeringan NTT',
                'slug' => 'wakaf-sumur-air-bersih-desa-ntt',
                'category' => CampaignCategory::Wakaf,
                'target_amount' => 120_000_000,
                'collected_amount' => 95_000_000,
                'cover_image' => 'https://images.unsplash.com/photo-1541252260730-0412e8e2108e?auto=format&fit=crop&w=1200&q=80',
                'start_date' => now()->subMonths(3)->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
                'status' => CampaignStatus::Active,
                'description' => "Di Dusun Oelette, Timor Tengah Selatan, para ibu dan anak-anak harus berjalan kaki sejauh 4 kilometer menuruni tebing curam setiap pagi hanya untuk mendapatkan seember air keruh.\n\nMelalui Wakaf Sumur Bor Produktif ini, kami akan membangun:\n- Pengeboran sumur sedalam 90 meter hingga menemukan sumber air artesis murni.\n- Toren penampungan air kapasitas 10.000 liter.\n- Pipanisasi langsung ke 8 titik pemukiman warga dan tempat wudhu masjid desa.\n\n*Pahala mengalir abadi selama air bersih ini dimanfaatkan oleh ribuan warga.*",
            ],
            [
                'title' => 'Tanggap Darurat Medis & Bantuan Tanggap Bencana Gempa',
                'slug' => 'tanggap-darurat-medis-bencana-gempa',
                'category' => CampaignCategory::Kemanusiaan,
                'target_amount' => 200_000_000,
                'collected_amount' => 112_500_000,
                'cover_image' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1200&q=80',
                'start_date' => now()->subWeeks(3)->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
                'status' => CampaignStatus::Active,
                'description' => "Respon cepat tim medis dan evakuasi untuk warga terdampak bencana alam di wilayah rawan. Dana digunakan untuk pengadaan tenda darurat, dapur umum, obat-obatan esensial, serta trauma healing bagi anak-anak pengungsi.",
            ],
            [
                'title' => 'Paket Pendidikan & Seragam Lengkap 200 Anak Yatim Pesisir',
                'slug' => 'paket-pendidikan-anak-yatim-pesisir',
                'category' => CampaignCategory::Infaq,
                'target_amount' => 35_000_000,
                'collected_amount' => 21_000_000,
                'cover_image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80',
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
                'status' => CampaignStatus::Active,
                'description' => "Bantu anak-anak nelayan dan dhuafa di pesisir tetap bersekolah dengan menyediakan paket buku tulis, seragam, sepatu baru, dan tas sekolah menjelang tahun ajaran baru.",
            ],
            [
                'title' => 'Wakaf Pembangunan Klinik & Rumah Sehat Gratis Dhuafa',
                'slug' => 'wakaf-pembangunan-klinik-dhuafa',
                'category' => CampaignCategory::Wakaf,
                'target_amount' => null, // Open-ended
                'collected_amount' => 310_000_000,
                'cover_image' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=1200&q=80',
                'start_date' => now()->subMonths(6)->toDateString(),
                'end_date' => null,
                'status' => CampaignStatus::Active,
                'description' => "Fasilitas kesehatan gratis berbasis wakaf produktif yang melayani pasien dhuafa, persalinan ibu pra-sejahtera, serta poli umum dan gigi tanpa dipungut biaya sepeser pun.",
            ],
        ];

        $campaignModels = [];
        foreach ($campaignData as $c) {
            $campaignModels[$c['slug']] = Campaign::updateOrCreate(
                ['slug' => $c['slug']],
                $c
            );
        }

        // 4. Mustahiks with Rich Had Kifayah Data & Complete KK
        $mustahiksData = [
            [
                'name' => 'Pak Suparno',
                'nik' => '3201011503750002',
                'family_card_number' => '3201012304850001',
                'asnaf_category' => AsnafCategory::Fakir,
                'income_per_month' => 900_000,
                'dependents_count' => 5, // Kifayah = 3M + 5M = 8M. Ratio = 900k / 8M = ~11.25% (Fakir)
                'phone' => '081234110001',
                'address' => 'Kampung Gunung Geulis RT 02/RW 04, Megamendung, Bogor',
                'status_approval' => ApprovalStatus::Verified,
            ],
            [
                'name' => 'Ibu Aminah',
                'nik' => '3275024508820004',
                'family_card_number' => '3275021208920003',
                'asnaf_category' => AsnafCategory::Miskin,
                'income_per_month' => 2_400_000,
                'dependents_count' => 3, // Kifayah = 3M + 3M = 6M. Ratio = 2.4M / 6M = 40.0% (Miskin/Fakir)
                'phone' => '081234110002',
                'address' => 'Jl. Bintara Jaya No. 18, Bekasi Barat',
                'status_approval' => ApprovalStatus::Verified,
            ],
            [
                'name' => 'Ustadz Rahmat Hidayat',
                'nik' => '3302041205800006',
                'family_card_number' => '3302041905880005',
                'asnaf_category' => AsnafCategory::Fisabilillah,
                'income_per_month' => 1_500_000,
                'dependents_count' => 2, // Kifayah = 3M + 2M = 5M. Ratio = 30%
                'phone' => '081234110003',
                'address' => 'Pondok Pesantren Nurul Huda, Desa Karangpucung, Cilacap',
                'status_approval' => ApprovalStatus::Verified,
            ],
            [
                'name' => 'Kevin Pratama',
                'nik' => '3171032109930008',
                'family_card_number' => '3171031109950007',
                'asnaf_category' => AsnafCategory::Mualaf,
                'income_per_month' => 1_800_000,
                'dependents_count' => 1,
                'phone' => '081234110004',
                'address' => 'Komplek Cengkareng Indah Blok B3, Jakarta Barat',
                'status_approval' => ApprovalStatus::Verified,
            ],
            [
                'name' => 'Pak Wardiman',
                'nik' => '3578011403750010',
                'family_card_number' => '3578010403780009',
                'asnaf_category' => AsnafCategory::Gharim,
                'income_per_month' => 2_100_000,
                'dependents_count' => 4,
                'phone' => '081234110005',
                'address' => 'Kelurahan Dupak, Krembangan, Surabaya',
                'status_approval' => ApprovalStatus::Verified,
            ],
            [
                'name' => 'Salman Al-Farisi',
                'nik' => '3674012212980012',
                'family_card_number' => '3674011212990011',
                'asnaf_category' => AsnafCategory::IbnuSabil,
                'income_per_month' => 800_000,
                'dependents_count' => 0,
                'phone' => '081234110006',
                'address' => 'Wisma Musafir Dhuafa, Tangerang Selatan',
                'status_approval' => ApprovalStatus::Verified,
            ],
        ];

        $mustahikModels = [];
        foreach ($mustahiksData as $m) {
            $mustahikModels[] = Mustahik::updateOrCreate(
                ['nik_hash' => Mustahik::hashNik($m['nik'])],
                $m
            );
        }

        // 5. Donors and Paid Transactions with Sincere Prayers
        $donorsData = [
            ['name' => 'H. Sulaiman Bakrie', 'phone' => '081112233441', 'email' => 'sulaiman.b@example.com'],
            ['name' => 'dr. Annisa Maharani', 'phone' => '081223344552', 'email' => 'annisa.m@example.com'],
            ['name' => 'Keluarga Alm. H. Ramli', 'phone' => '081334455663', 'email' => 'fam.ramli@example.com'],
            ['name' => 'Rian Hidayat, S.T.', 'phone' => '081445566774', 'email' => 'rian.h@example.com'],
            ['name' => 'Ibu Siti Khadijah', 'phone' => '081556677885', 'email' => 'siti.k@example.com'],
            ['name' => 'Bambang Triyono', 'phone' => '081667788996', 'email' => 'bambang.t@example.com'],
            ['name' => 'Fatimah Az-Zahra', 'phone' => '081778899007', 'email' => 'fatimah.z@example.com'],
            ['name' => 'Muhammad Zaky Pratama', 'phone' => '081889900118', 'email' => 'zaky.p@example.com'],
        ];

        $donationsData = [
            [
                'donor_idx' => 0,
                'campaign_slug' => 'zakat-maal-500-keluarga-asnaf',
                'amount' => 12_500_000,
                'method' => 'bca_va',
                'type' => 'zakat',
                'prayer' => 'Bismillah, niat zakat maal atas perniagaan keluarga tahun ini. Semoga membersihkan harta dan membawa keberkahan bagi para mustahik.',
                'anonim' => false,
                'ref' => 'DAI-AHMAD',
            ],
            [
                'donor_idx' => 1,
                'campaign_slug' => 'wakaf-sumur-air-bersih-desa-ntt',
                'amount' => 5_000_000,
                'method' => 'qris',
                'type' => 'wakaf',
                'prayer' => 'Wakaf atas nama almarhum ayahanda dr. H. Soepomo. Semoga setiap tetes air yang mengalir menjadi penerang dan kelapangan kuburnya.',
                'anonim' => false,
                'ref' => 'SARAH-MEDIK',
            ],
            [
                'donor_idx' => 2,
                'campaign_slug' => 'sedekah-pangan-santri-penghafal-quran',
                'amount' => 2_500_000,
                'method' => 'bni_va',
                'type' => 'infaq',
                'prayer' => 'Sedekah subuh untuk adik-adik santri penghafal Quran. Mohon didoakan anak kami lulus ujian kedokteran.',
                'anonim' => false,
                'ref' => 'PEMUDA-PEDULI',
            ],
            [
                'donor_idx' => 3,
                'campaign_slug' => 'tanggap-darurat-medis-bencana-gempa',
                'amount' => 1_000_000,
                'method' => 'gopay',
                'type' => 'kemanusiaan',
                'prayer' => 'Semoga saudara-saudara kita yang di tenda pengungsian diberikan ketabahan dan kesehatan.',
                'anonim' => false,
                'ref' => null,
            ],
            [
                'donor_idx' => 4,
                'campaign_slug' => 'zakat-maal-500-keluarga-asnaf',
                'amount' => 4_500_000,
                'method' => 'qris',
                'type' => 'zakat',
                'prayer' => 'Zakat profesi bulanan atas rezeki yang Allah titipkan. Semoga berkah berlimpah.',
                'anonim' => true,
                'ref' => null,
            ],
            [
                'donor_idx' => 5,
                'campaign_slug' => 'paket-pendidikan-anak-yatim-pesisir',
                'amount' => 500_000,
                'method' => 'qris',
                'type' => 'infaq',
                'prayer' => 'Semoga adik-adik semangat terus belajarnya dan kelak menjadi generasi pembangun bangsa yang sholeh.',
                'anonim' => false,
                'ref' => null,
            ],
            [
                'donor_idx' => 6,
                'campaign_slug' => 'wakaf-pembangunan-klinik-dhuafa',
                'amount' => 10_000_000,
                'method' => 'bca_va',
                'type' => 'wakaf',
                'prayer' => 'Wakaf tunai pembangunan klinik dhuafa. Semoga menjadi amal jariyah yang tak pernah terputus hingga hari akhir.',
                'anonim' => false,
                'ref' => 'SARAH-MEDIK',
            ],
            [
                'donor_idx' => 7,
                'campaign_slug' => 'sedekah-pangan-santri-penghafal-quran',
                'amount' => 300_000,
                'method' => 'gopay',
                'type' => 'infaq',
                'prayer' => 'Lillahi Ta\'ala, semoga berkah bagi santri tahfidz.',
                'anonim' => true,
                'ref' => null,
            ],
        ];

        foreach ($donationsData as $idx => $d) {
            $donorInfo = $donorsData[$d['donor_idx']];
            $donor = Donor::firstOrCreate(
                ['phone_number' => preg_replace('/\D+/', '', $donorInfo['phone'])],
                [
                    'name' => $donorInfo['name'],
                    'email' => $donorInfo['email'],
                    'address' => 'Jakarta, Indonesia',
                    'is_verified' => true,
                ]
            );

            $campaign = $campaignModels[$d['campaign_slug']];
            $fundraiser = $d['ref'] ? ($fundraiserModels[$d['ref']] ?? null) : null;
            $invNumber = 'INV-' . now()->subDays($idx * 2)->format('Ymd') . '-' . strtoupper(Str::random(4));

            $donation = Donation::firstOrCreate(
                ['invoice_number' => $invNumber],
                [
                    'campaign_id' => $campaign->id,
                    'donor_id' => $donor->id,
                    'amount' => $d['amount'],
                    'donation_type' => $d['type'],
                    'unique_code' => 0,
                    'payment_method' => $d['method'],
                    'payment_gateway_ref' => 'MIDTRANS-' . strtoupper(Str::random(8)),
                    'payment_status' => PaymentStatus::Paid,
                    'paid_at' => now()->subDays($idx * 2),
                    'doa_harapan' => $d['prayer'],
                    'is_anonim' => $d['anonim'],
                    'fundraiser_id' => $fundraiser?->id,
                    'created_at' => now()->subDays($idx * 2),
                ]
            );

            // Auto-record PSAK 109 Journal Entry if not recorded yet
            try {
                $accounting->recordDonation($donation);
            } catch (\Throwable $e) {
                // Skip if already journaled
            }
        }

        // 6. Multi-tier Disbursement Requests (Demonstrating all statuses)
        $disbursementRequests = [
            // Status: Disbursed
            [
                'campaign' => $campaignModels['wakaf-sumur-air-bersih-desa-ntt'],
                'mustahik' => $mustahikModels[0], // Pak Suparno
                'amount' => 25_000_000,
                'purpose' => 'Pencairan Tahap 1: Pengadaan mesin bor artesis dan sewa alat berat di Desa Oelette NTT',
                'status' => DisbursementRequestStatus::Disbursed,
                'disbursed' => true,
            ],
            [
                'campaign' => $campaignModels['zakat-maal-500-keluarga-asnaf'],
                'mustahik' => $mustahikModels[1], // Ibu Aminah
                'amount' => 7_500_000,
                'purpose' => 'Penyaluran Zakat Maal: Modal usaha mikro gerobak kuliner berkah untuk 3 keluarga asnaf janda',
                'status' => DisbursementRequestStatus::Disbursed,
                'disbursed' => true,
            ],
            // Status: Approved (Ready for Admin to click "Cairkan Dana" in demo!)
            [
                'campaign' => $campaignModels['sedekah-pangan-santri-penghafal-quran'],
                'mustahik' => $mustahikModels[2], // Ustadz Rahmat
                'amount' => 10_000_000,
                'purpose' => 'Pengadaan sembako beras 500kg dan lauk pauk bergizi santri tahfidz Baitul Qur\'an',
                'status' => DisbursementRequestStatus::Approved,
                'disbursed' => false,
            ],
            // Status: Draft (Ready for Admin to review and click "Setujui" in demo!)
            [
                'campaign' => $campaignModels['tanggap-darurat-medis-bencana-gempa'],
                'mustahik' => $mustahikModels[4], // Pak Wardiman
                'amount' => 15_000_000,
                'purpose' => 'Bantuan santunan medis darurat operasi patah tulang korban gempa',
                'status' => DisbursementRequestStatus::Draft,
                'disbursed' => false,
            ],
            // Status: Rejected (Demonstrating compliance rejection)
            [
                'campaign' => $campaignModels['paket-pendidikan-anak-yatim-pesisir'],
                'mustahik' => $mustahikModels[5], // Salman
                'amount' => 18_000_000,
                'purpose' => 'Pengajuan seragam tanpa melampirkan data validasi ukuran dan sekolah mitra',
                'notes' => 'Alasan Ditolak: Rincian anggaran biaya (RAB) dan tanda tangan komite sekolah belum lengkap.',
                'status' => DisbursementRequestStatus::Rejected,
                'disbursed' => false,
            ],
        ];

        foreach ($disbursementRequests as $idx => $dr) {
            $reqNum = 'REQ-' . now()->format('Ymd') . '-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
            $existing = DisbursementRequest::where('request_number', $reqNum)->first();

            if (! $existing) {
                $req = DisbursementRequest::create([
                    'request_number' => $reqNum,
                    'campaign_id' => $dr['campaign']->id,
                    'mustahik_id' => $dr['mustahik']->id,
                    'amount' => $dr['amount'],
                    'purpose' => $dr['purpose'],
                    'notes' => $dr['notes'] ?? null,
                    'status' => $dr['status'],
                    'requested_by' => $user->id,
                    'approved_by' => in_array($dr['status'], [DisbursementRequestStatus::Approved, DisbursementRequestStatus::Disbursed], true) ? $user->id : null,
                    'approved_at' => in_array($dr['status'], [DisbursementRequestStatus::Approved, DisbursementRequestStatus::Disbursed], true) ? now()->subDays(2) : null,
                    'created_at' => now()->subDays(5),
                ]);

                if ($dr['disbursed']) {
                    // Create official disbursement
                    $disb = Disbursement::create([
                        'campaign_id' => $dr['campaign']->id,
                        'mustahik_id' => $dr['mustahik']->id,
                        'amount' => $dr['amount'],
                        'disbursement_date' => now()->subDay()->toDateString(),
                        'proof_document' => 'proofs/demo_berita_acara_' . ($idx + 1) . '.pdf',
                        'notes' => $dr['purpose'],
                        'created_by' => $user->id,
                    ]);

                    $req->update([
                        'disbursed_at' => now()->subDay(),
                    ]);

                    // Accounting auto-journal entry
                    try {
                        $accounting->recordDisbursement($disb);
                    } catch (\Throwable $e) {
                        // Skip if already journaled
                    }
                }
            }
        }
    }
}

<?php

return [

    /*
    | Identitas lembaga (tampil di kwitansi & halaman publik)
    */
    'org' => [
        'name' => env('ORG_NAME', 'Lembaga ZISWAF Contoh'),
        'address' => env('ORG_ADDRESS', 'Jl. Contoh No. 1, Jakarta'),
        'phone' => env('ORG_PHONE', '6281234567890'),
    ],

    /*
    | Nilai fallback harga emas per gram (Rupiah).
    | Nilai aktif diambil dari Admin > Pengaturan Zakat (tabel settings),
    | dan jatuh ke nilai ini bila belum diisi.
    */
    'gold_price_per_gram' => (int) env('GOLD_PRICE_PER_GRAM', 1500000),
    'nishab_gold_grams' => 85,
    'zakat_rate' => 0.025,

    /*
    | Nominal preset checkout & batas donasi
    */
    'preset_amounts' => [25000, 50000, 100000, 500000],
    'min_donation' => (int) env('MIN_DONATION', 10000),

    /*
    | Metode pembayaran yang ditampilkan di form (kode Midtrans enabled_payments)
    */
    'payment_methods' => [
        'qris' => 'QRIS',
        'bca_va' => 'Virtual Account BCA',
        'bni_va' => 'Virtual Account BNI',
        'bri_va' => 'Virtual Account BRI',
        'permata_va' => 'Virtual Account Permata',
        'gopay' => 'GoPay',
    ],

    'referral_cookie_days' => 30,

    /*
    | Midtrans Snap
    */
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        'expiry_hours' => (int) env('MIDTRANS_EXPIRY_HOURS', 24),
    ],

    /*
    | Notifikasi: driver 'log' (lokal/test) atau 'fonnte'
    */
    'notifications' => [
        'driver' => env('NOTIFICATION_DRIVER', 'log'),
        'fonnte' => [
            'token' => env('FONNTE_TOKEN'),
            'endpoint' => env('FONNTE_ENDPOINT', 'https://api.fonnte.com/send'),
        ],
    ],

    /*
    | Admin default untuk seeder (WAJIB diganti di produksi)
    */
    'seed_admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL', 'admin@ziswaf.test'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],
];

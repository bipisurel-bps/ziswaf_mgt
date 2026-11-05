<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 20px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1c1917; }
    .box { border: 2px solid #047857; padding: 16px; }
    h1 { font-size: 16px; margin: 0; color: #065f46; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    td { padding: 4px 0; vertical-align: top; }
    td.l { width: 28%; color: #57534e; }
    .amt { font-size: 18px; font-weight: bold; color: #065f46; }
    .muted { color: #78716c; font-size: 9px; }
</style>
</head>
<body>
<div class="box">
    <table><tr>
        <td><h1>{{ $org['name'] }}</h1><div class="muted">{{ $org['address'] }}</div></td>
        <td style="text-align:right"><b>KWITANSI DONASI</b><br>{{ $donation->invoice_number }}</td>
    </tr></table>
    <hr>
    <table>
        <tr><td class="l">Telah diterima dari</td><td>: {{ $donation->display_name }}</td></tr>
        <tr><td class="l">Akad</td><td>: {{ $akad }}</td></tr>
        <tr><td class="l">Peruntukan</td><td>: {{ $donation->campaign?->title ?? 'Donasi Umum' }}</td></tr>
        <tr><td class="l">Jumlah</td><td>: <span class="amt">Rp {{ number_format($donation->amount, 0, ',', '.') }}</span></td></tr>
        <tr><td class="l">Metode</td><td>: {{ strtoupper($donation->payment_method ?? '-') }}</td></tr>
        <tr><td class="l">Tanggal</td><td>: {{ $donation->paid_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</td></tr>
        @if ($donation->doa_harapan)<tr><td class="l">Doa / harapan</td><td>: {{ $donation->doa_harapan }}</td></tr>@endif
    </table>
    <table style="margin-top:14px"><tr>
        <td class="muted">Semoga Allah menerima amal Anda dan memberkahi harta yang tersisa.<br>Pindai QR untuk memverifikasi keaslian kwitansi.</td>
        <td style="text-align:right"><img src="{{ $qr }}" width="90" height="90" alt="QR"></td>
    </tr></table>
</div>
</body>
</html>

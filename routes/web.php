<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ZakatCalculatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CampaignController::class, 'index'])->name('home');
Route::get('/kampanye/{campaign:slug}', [CampaignController::class, 'show'])->name('campaigns.show');

Route::get('/zakat', ZakatCalculatorController::class)->name('zakat');

Route::get('/donasi', [DonationController::class, 'create'])->name('donation.create');
Route::post('/donasi', [DonationController::class, 'store'])->middleware('throttle:10,1')->name('donation.store');
Route::get('/donasi/{invoice}/bayar', [DonationController::class, 'pay'])->name('donation.pay');
Route::get('/donasi/{invoice}/terima-kasih', [DonationController::class, 'thanks'])->name('donation.thanks');

Route::get('/verifikasi/{invoice}', [ReceiptController::class, 'verify'])->middleware('signed')->name('receipt.verify');
Route::get('/kwitansi/{invoice}', [ReceiptController::class, 'download'])->middleware('signed')->name('receipt.download');

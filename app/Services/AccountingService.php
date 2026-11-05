<?php

namespace App\Services;

use App\Models\CoaAccount;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingService
{
    /**
     * Records a balanced double-entry journal under Islamic Fund Accounting (PSAK 109).
     *
     * @param  array{entry_date?:string,description:string,reference_type?:string,reference_id?:int}  $header
     * @param  array<int, array{coa_account_id:int,debit?:int,credit?:int}>  $items
     */
    public function createEntry(array $header, array $items): JournalEntry
    {
        return DB::transaction(function () use ($header, $items) {
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($items as $item) {
                $totalDebit += (int) ($item['debit'] ?? 0);
                $totalCredit += (int) ($item['credit'] ?? 0);
            }

            if ($totalDebit !== $totalCredit || $totalDebit <= 0) {
                throw new InvalidArgumentException(
                    "Jurnal tidak seimbang (Total Debit: {$totalDebit}, Total Kredit: {$totalCredit})."
                );
            }

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $header['entry_date'] ?? now()->toDateString(),
                'reference_type' => $header['reference_type'] ?? null,
                'reference_id' => $header['reference_id'] ?? null,
                'description' => $header['description'],
                'status' => 'posted',
            ]);

            foreach ($items as $item) {
                JournalEntryItem::create([
                    'journal_entry_id' => $entry->id,
                    'coa_account_id' => $item['coa_account_id'],
                    'debit' => (int) ($item['debit'] ?? 0),
                    'credit' => (int) ($item['credit'] ?? 0),
                ]);
            }

            return $entry;
        });
    }

    /**
     * Auto-journal for a settled donation:
     * - Debit: Kas Payment Gateway (1-1001)
     * - Credit: Corresponding Fund Revenue (4-1001 Zakat, 4-2001 Infaq, 4-2003 Wakaf)
     *   Optionally allocates Amil portion (12.5% max) if enabled in config.
     */
    public function recordDonation(Donation $donation): JournalEntry
    {
        $kasGateway = CoaAccount::where('code', '1-1001')->firstOrFail();
        $amount = (int) $donation->amount;

        $type = $donation->donation_type ?? ($donation->campaign?->category->value ?? 'infaq');
        $items = [
            [
                'coa_account_id' => $kasGateway->id,
                'debit' => $amount,
                'credit' => 0,
            ],
        ];

        // Determine revenue credit accounts based on PSAK 109 funds
        if ($type === 'zakat') {
            $zakatAccount = CoaAccount::where('code', '4-1001')->firstOrFail();
            $amilPercentage = (float) config('ziswaf.accounting.amil_zakat_percentage', config('ziswaf.accounting.amil_rate', 0.0));

            if ($amilPercentage > 0 && $amilPercentage <= 0.125) {
                $amilAccount = CoaAccount::where('code', '4-3001')->firstOrFail();
                $amilShare = (int) round($amount * $amilPercentage);
                $zakatShare = $amount - $amilShare;

                $items[] = ['coa_account_id' => $zakatAccount->id, 'debit' => 0, 'credit' => $zakatShare];
                $items[] = ['coa_account_id' => $amilAccount->id, 'debit' => 0, 'credit' => $amilShare];
            } else {
                $items[] = ['coa_account_id' => $zakatAccount->id, 'debit' => 0, 'credit' => $amount];
            }
        } elseif ($type === 'wakaf') {
            $wakafAccount = CoaAccount::where('code', '4-2003')->firstOrFail();
            $items[] = ['coa_account_id' => $wakafAccount->id, 'debit' => 0, 'credit' => $amount];
        } else {
            // Infaq Bebas or Infaq Terikat (campaign-specific)
            $code = $donation->campaign_id ? '4-2002' : '4-2001';
            $infaqAccount = CoaAccount::where('code', $code)->first()
                ?? CoaAccount::where('code', '4-2001')->firstOrFail();
            $items[] = ['coa_account_id' => $infaqAccount->id, 'debit' => 0, 'credit' => $amount];
        }

        return $this->createEntry([
            'entry_date' => ($donation->paid_at ?? now())->toDateString(),
            'description' => "Penerimaan {$donation->donation_type} ({$donation->invoice_number}) dari {$donation->display_name}",
            'reference_type' => Donation::class,
            'reference_id' => $donation->id,
        ], $items);
    }

    /**
     * Auto-journal for a disbursement:
     * - Debit: Penyaluran/Beban (5-1001 Beban Zakat, 5-2001/5-2002 Beban Infaq)
     * - Credit: Kas / Bank Operasional (1-1002) atau Kas Gateway (1-1001)
     */
    public function recordDisbursement(Disbursement $disbursement): JournalEntry
    {
        $disbursement->loadMissing('campaign');
        $category = $disbursement->campaign?->category->value ?? 'infaq';

        $expenseCode = match ($category) {
            'zakat' => '5-1001',
            'wakaf' => '5-2001',
            default => '5-2001',
        };

        $expenseAccount = CoaAccount::where('code', $expenseCode)->firstOrFail();
        $creditAccount = CoaAccount::where('code', '1-1002')->first()
            ?? CoaAccount::where('code', '1-1001')->firstOrFail();

        $amount = (int) $disbursement->amount;

        $items = [
            ['coa_account_id' => $expenseAccount->id, 'debit' => $amount, 'credit' => 0],
            ['coa_account_id' => $creditAccount->id, 'debit' => 0, 'credit' => $amount],
        ];

        return $this->createEntry([
            'entry_date' => ($disbursement->disbursement_date ?? now())->toDateString(),
            'description' => "Penyaluran {$category} untuk kampanye \"{$disbursement->campaign?->title}\" kepada mustahik ID: {$disbursement->mustahik_id}",
            'reference_type' => Disbursement::class,
            'reference_id' => $disbursement->id,
        ], $items);
    }
}

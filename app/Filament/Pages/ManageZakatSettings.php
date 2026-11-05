<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\ZakatCalculationService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageZakatSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Zakat';

    protected static ?string $title = 'Pengaturan Zakat';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static string $view = 'filament.pages.manage-zakat-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'gold_price_per_gram' => Setting::get('gold_price_per_gram'),
        ]);
    }

    public function form(Form $form): Form
    {
        $fallback = (int) config('ziswaf.gold_price_per_gram');

        return $form
            ->schema([
                Section::make('Harga emas (kalkulator zakat)')
                    ->description('Dipakai untuk nishab 85 gram emas. Kosongkan untuk memakai nilai fallback dari .env (GOLD_PRICE_PER_GRAM).')
                    ->schema([
                        TextInput::make('gold_price_per_gram')
                            ->label('Harga emas per gram')
                            ->numeric()->minValue(1)->prefix('Rp')
                            ->helperText('Fallback saat ini: Rp '.number_format($fallback, 0, ',', '.')),
                        Placeholder::make('nishab')
                            ->label('Nishab aktif (per tahun / per bulan)')
                            ->content(function () {
                                $z = app(ZakatCalculationService::class);

                                return 'Rp '.number_format($z->nishabYearly(), 0, ',', '.').' / Rp '.number_format($z->nishabMonthly(), 0, ',', '.');
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        Setting::set('gold_price_per_gram', $state['gold_price_per_gram'] ?: null);

        Notification::make()->title('Pengaturan disimpan')->success()->send();
    }
}

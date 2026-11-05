import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

const rupiah = (n) => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
const digits = (v) => parseInt(String(v ?? '').replace(/\D+/g, ''), 10) || 0;

/** One-page donation checkout. */
Alpine.data('checkout', ({ presets, initialAmount = 0 }) => ({
    presets,
    amount: initialAmount || presets[1] || 0,
    custom: '',
    anonim: false,
    init() {
        if (initialAmount && !presets.includes(initialAmount)) {
            this.custom = initialAmount.toLocaleString('id-ID');
        }
    },
    pick(v) {
        this.amount = v;
        this.custom = '';
    },
    onCustom() {
        const v = digits(this.custom);
        this.custom = v ? v.toLocaleString('id-ID') : '';
        this.amount = v;
    },
    get label() {
        return rupiah(this.amount);
    },
}));

/**
 * Zakat calculator. Formula mirrors App\Services\ZakatCalculationService:
 *  - Profesi: (penghasilan + bonus - pengeluaran pokok) >= nishab/12 -> 2.5%
 *  - Maal:    (aset - hutang), haul 1 tahun, >= nishab (85 g emas)    -> 2.5%
 */
Alpine.data('zakatCalc', ({ nishabYearly, nishabMonthly, rate }) => ({
    tab: 'profesi',
    income: '', bonus: '', expenses: '',
    assets: '', debts: '', haul: true,
    nishabYearly, nishabMonthly,
    fmt: rupiah,
    n(v) { return digits(v); },
    mask(key) {
        const v = digits(this[key]);
        this[key] = v ? v.toLocaleString('id-ID') : '';
    },
    get profesi() {
        const base = Math.max(0, this.n(this.income) + this.n(this.bonus) - this.n(this.expenses));
        const ok = base >= this.nishabMonthly && this.nishabMonthly > 0;
        return { base, nishab: this.nishabMonthly, ok, zakat: ok ? Math.floor(base * rate) : 0 };
    },
    get maal() {
        const base = Math.max(0, this.n(this.assets) - this.n(this.debts));
        const ok = this.haul && base >= this.nishabYearly && this.nishabYearly > 0;
        return { base, nishab: this.nishabYearly, ok, zakat: ok ? Math.floor(base * rate) : 0 };
    },
    get result() {
        return this.tab === 'profesi' ? this.profesi : this.maal;
    },
    get payUrl() {
        return `/donasi?type=zakat&amount=${this.result.zakat}`;
    },
}));

Alpine.start();

import './bootstrap';
import Alpine from 'alpinejs';
import * as anime from 'animejs';
import { animate, createTimeline, stagger } from 'animejs';

window.Alpine = Alpine;
window.anime = anime;
window.animate = animate;
window.createTimeline = createTimeline;
window.stagger = stagger;

const rupiah = (n) => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
const digits = (v) => parseInt(String(v ?? '').replace(/\D+/g, ''), 10) || 0;

window.rupiah = rupiah;
window.digits = digits;

/**
 * Animate numeric counter using anime.js
 */
window.animateValue = (el, endValue, duration = 1200, prefix = '', suffix = '') => {
    if (!el) return;
    const obj = { val: 0 };
    animate(obj, {
        val: endValue,
        duration: duration,
        ease: 'outExpo',
        onUpdate: () => {
            el.innerText = prefix + Math.round(obj.val).toLocaleString('id-ID') + suffix;
        }
    });
};

/** One-page donation checkout with micro-interactions. */
Alpine.data('checkout', ({ presets, initialAmount = 0 }) => ({
    presets,
    amount: initialAmount || presets[1] || presets[0] || 50000,
    custom: '',
    anonim: false,
    selectedCategory: 'infaq',
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
 * Zakat calculator with real-time feedback & animations.
 */
Alpine.data('zakatCalc', ({ nishabYearly, nishabMonthly, rate }) => ({
    tab: 'profesi',
    income: '', bonus: '', expenses: '',
    assets: '', debts: '', haul: true,
    nishabYearly, nishabMonthly, rate,
    fmt: rupiah,
    n(v) { return digits(v); },
    mask(key) {
        const v = digits(this[key]);
        this[key] = v ? v.toLocaleString('id-ID') : '';
    },
    get profesi() {
        const base = Math.max(0, this.n(this.income) + this.n(this.bonus) - this.n(this.expenses));
        const ok = base >= this.nishabMonthly && this.nishabMonthly > 0;
        return { base, nishab: this.nishabMonthly, ok, zakat: ok ? Math.floor(base * this.rate) : 0 };
    },
    get maal() {
        const base = Math.max(0, this.n(this.assets) - this.n(this.debts));
        const ok = this.haul && base >= this.nishabYearly && this.nishabYearly > 0;
        return { base, nishab: this.nishabYearly, ok, zakat: ok ? Math.floor(base * this.rate) : 0 };
    },
    get result() {
        return this.tab === 'profesi' ? this.profesi : this.maal;
    },
    get payUrl() {
        return `/donasi?type=zakat&amount=${this.result.zakat}`;
    },
}));

Alpine.start();

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Strip thousands separators from the custom amount input ("1.500.000").
        $amount = $this->input('amount');
        if (is_string($amount)) {
            $this->merge(['amount' => (int) preg_replace('/\D+/', '', $amount)]);
        }

        // Accept "0812-3456-7890", "+62 812 3456 7890", etc.
        $phone = $this->input('phone_number');
        if (is_string($phone)) {
            $this->merge(['phone_number' => preg_replace('/[\s\-().]+/', '', $phone)]);
        }
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:'.config('ziswaf.min_donation'), 'max:1000000000'],
            'name' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'campaign_id' => ['nullable', 'integer', Rule::exists('campaigns', 'id')->where('status', 'active')->whereNull('deleted_at')],
            'donation_type' => ['nullable', Rule::in(['zakat', 'infaq', 'wakaf', 'kemanusiaan'])],
            'payment_method' => ['nullable', Rule::in(array_merge(array_keys(config('ziswaf.payment_methods')), ['any']))],
            'doa_harapan' => ['nullable', 'string', 'max:500'],
            'is_anonim' => ['sometimes', 'boolean'],
            'referral_code' => ['nullable', 'string', 'max:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Minimal donasi Rp '.number_format(config('ziswaf.min_donation'), 0, ',', '.').'.',
            'phone_number.regex' => 'Nomor WhatsApp tidak valid (contoh: 081234567890).',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * New stock arriving with a batch number and an expiry date — a purchase from
 * a supplier, most often. Unlike `StockAdjustmentRequest`, this always adds,
 * and it always creates or extends a specific, traceable batch.
 */
class StockReceiptRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'batchNumber' => ['required', 'string', 'max:60'],
            'expiresAt' => ['required', 'date', 'after:today'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'costPrice' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'batchNumber' => 'nomor batch',
            'expiresAt' => 'tanggal kedaluwarsa',
            'quantity' => 'jumlah',
            'costPrice' => 'harga beli',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'expiresAt.after' => 'Tanggal kedaluwarsa harus di masa depan.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A correction to a branch's stock count, not tied to a specific batch.
 * `delta` may be negative — see `App\Support\Inventory\StockAdjuster`.
 */
class StockAdjustmentRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'delta' => ['required', 'integer', 'min:-100000', 'max:100000', 'not_in:0'],
            'reason' => ['required', Rule::in(AdminOptions::labels(AdminOptions::ADJUSTMENT_REASONS))],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'delta' => 'jumlah penyesuaian',
            'reason' => 'alasan',
            'note' => 'catatan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'delta.not_in' => 'Jumlah penyesuaian tidak boleh nol.',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceNumber extends Model
{
    protected $fillable = ['user_id', 'number'];

    /**
     * Generate and store the next invoice number for a user.
     *
     * @param int $user_id
     * @return string
     */
    public static function generateInvoiceNumber(int $user_id): string
    {
        $user = User::with(['buyer', 'vendor', 'invoiceNumbers'])->findOrFail($user_id);

        // Decide which model to pull user_code from
        $userCode = null;
        
        if ($user->user_type == 1) {
            $userCode = $user->buyer?->buyer_code;
        } else {
            $userCode = $user->vendor?->vendor_code;
        }

        // Optional fallback if user_code is not found
        $userCode = $userCode ?: 'Invoice-000';

        // Get last number and increment
        $lastNumber = $user->invoiceNumbers()->latest('id')->value('number');
        $nextValue = $lastNumber ? ((int) $lastNumber + 1) : 1;

        // Create the new invoice record
        $user->invoiceNumbers()->create([
            'number' => $nextValue,
        ]);

        return $userCode . '/' . $nextValue;
    }
}

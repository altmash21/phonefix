<?php

namespace App\DTOs\MobileShop;

use Illuminate\Http\Request;

class SaleInput
{
    public function __construct(
        public readonly int $companyId,
        public readonly ?int $customerId,
        public readonly string $customerName,
        public readonly ?string $customerPhone,
        public readonly ?string $customerAddress,
        public readonly array $items,
        public readonly string $paymentMode,
        public readonly float $discount = 0.0,
        public readonly ?string $notes = null,
        public readonly ?int $createdBy = null,
    ) {}

    public static function fromRequest(Request $request, int $companyId): self
    {
        return new self(
            companyId: $companyId,
            customerId: $request->filled('customer_id') ? (int) $request->customer_id : null,
            customerName: (string) ($request->customer_name ?? 'Walk-in Customer'),
            customerPhone: $request->customer_phone,
            customerAddress: $request->customer_address,
            items: (array) ($request->items ?? []),
            paymentMode: (string) ($request->payment_mode ?? 'cash'),
            discount: (float) ($request->discount ?? 0.0),
            notes: $request->notes,
            createdBy: auth()->id(),
        );
    }
}

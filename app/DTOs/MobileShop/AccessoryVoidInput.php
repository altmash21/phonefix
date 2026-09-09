<?php

namespace App\DTOs\MobileShop;

use Illuminate\Http\Request;

class AccessoryVoidInput
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $saleId,
        public readonly string $reason,
        public readonly ?int $voidedBy = null,
    ) {}

    public static function fromRequest(Request $request, int $saleId, int $companyId): self
    {
        return new self(
            companyId: $companyId,
            saleId: $saleId,
            reason: (string) ($request->reason ?? 'Customer return / mistake'),
            voidedBy: auth()->id(),
        );
    }
}

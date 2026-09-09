<?php

namespace App\DTOs\MobileShop;

use Illuminate\Http\Request;

class StockInwardInput
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $brand,
        public readonly string $model,
        public readonly string $imei1,
        public readonly ?string $imei2,
        public readonly float $purchaseCost,
        public readonly float $sellingPrice,
        public readonly string $type = 'new',
        public readonly ?string $color = null,
        public readonly ?string $ram = null,
        public readonly ?string $storage = null,
        public readonly ?string $photoPath = null,
        public readonly ?int $supplierId = null,
        public readonly ?int $createdBy = null,
    ) {}

    public static function fromRequest(Request $request, int $companyId, ?string $photoPath = null): self
    {
        return new self(
            companyId: $companyId,
            brand: (string) $request->brand,
            model: (string) $request->model,
            imei1: (string) $request->imei_1,
            imei2: $request->imei_2,
            purchaseCost: (float) $request->purchase_cost,
            sellingPrice: (float) $request->selling_price,
            type: (string) ($request->type ?? 'new'),
            color: $request->color ?? 'Standard',
            ram: $request->ram,
            storage: $request->storage,
            photoPath: $photoPath,
            supplierId: $request->filled('supplier_id') ? (int) $request->supplier_id : null,
            createdBy: auth()->id(),
        );
    }
}

<?php

namespace App\Repositories\MobileShop\Contracts;

interface MsStockRepositoryInterface
{
    public function findById(int $id, ?int $companyId = null): ?object;

    public function findByImei(string $imei, ?int $companyId = null): ?object;

    public function existsByImei(string $imei, int $companyId): bool;

    public function findAvailable(int $companyId, array $filters = []): array;

    public function countAvailable(int $companyId): int;

    public function insertDevice(array $data): int;

    public function updateStatus(int $id, string $status, array $extra = []): bool;

    public function deleteDevice(int $id, int $companyId): bool;
}

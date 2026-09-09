<?php

namespace App\Repositories\MobileShop;

use App\Repositories\MobileShop\Contracts\MsStockRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MsStockRepository implements MsStockRepositoryInterface
{
    protected string $table = 'ms_mobile_devices';

    public function findById(int $id, ?int $companyId = null): ?object
    {
        $query = DB::table($this->table)->where('id', $id);
        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }
        return $query->first();
    }

    public function findByImei(string $imei, ?int $companyId = null): ?object
    {
        $query = DB::table($this->table)
            ->where(function ($q) use ($imei) {
                $q->where('imei_1', $imei)
                  ->orWhere('imei_2', $imei);
            });

        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }

        return $query->first();
    }

    public function existsByImei(string $imei, int $companyId): bool
    {
        return DB::table($this->table)
            ->where('company_id', $companyId)
            ->where(function ($q) use ($imei) {
                $q->where('imei_1', $imei)
                  ->orWhere('imei_2', $imei);
            })
            ->exists();
    }

    public function findAvailable(int $companyId, array $filters = []): array
    {
        $query = DB::table($this->table)
            ->where('company_id', $companyId)
            ->where('status', 'in_stock');

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('imei_1', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function countAvailable(int $companyId): int
    {
        return DB::table($this->table)
            ->where('company_id', $companyId)
            ->where('status', 'in_stock')
            ->count();
    }

    public function insertDevice(array $data): int
    {
        $now = now();
        if (!isset($data['created_at'])) {
            $data['created_at'] = $now;
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = $now;
        }

        return DB::table($this->table)->insertGetId($data);
    }

    public function updateStatus(int $id, string $status, array $extra = []): bool
    {
        $updateData = array_merge($extra, [
            'status'     => $status,
            'updated_at' => now(),
        ]);

        return DB::table($this->table)
            ->where('id', $id)
            ->update($updateData) > 0;
    }

    public function deleteDevice(int $id, int $companyId): bool
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->delete() > 0;
    }
}

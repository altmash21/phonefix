<?php

namespace App\Providers;

use App\Interfaces\Utility\DocumentNumber as DocumentNumberInterface;
use App\Interfaces\Utility\TransactionNumber as TransactionNumberInterface;
use App\Utilities\DocumentNumber;
use App\Repositories\MobileShop\Contracts\MsStockRepositoryInterface;
use App\Repositories\MobileShop\MsStockRepository;
use Illuminate\Support\ServiceProvider;

class Binding extends ServiceProvider
{
    /**
     * All container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        DocumentNumberInterface::class => DocumentNumber::class,
        TransactionNumberInterface::class => TransactionNumber::class,
        MsStockRepositoryInterface::class => MsStockRepository::class,
    ];
}

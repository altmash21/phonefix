<?php

namespace App\Observers;

use App\Abstracts\Observer;
use App\Models\Banking\Transaction as Model;

class Transaction extends Observer
{
    /**
     * Listen to the deleted event.
     *
     * @param  Model  $transaction
     * @return void
     */
    public function deleted(Model $transaction)
    {
        // Safe no-op for deleted transactions in MobiTrack
    }
}


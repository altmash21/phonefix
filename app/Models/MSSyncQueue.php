<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MSSyncQueue extends Model
{
    protected $table = 'ms_sync_queue';
    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'synced_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MSSyncOffset extends Model
{
    protected $table = 'ms_sync_offsets';
    protected $primaryKey = null;
    protected $guarded = [];
    public $timestamps = true;
    public $incrementing = false;
}

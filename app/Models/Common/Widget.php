<?php

namespace App\Models\Common;

use App\Abstracts\Model;
use Bkwld\Cloner\Cloneable;

class Widget extends Model
{
    use Cloneable;

    protected $table = 'widgets';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'dashboard_id', 'class', 'name', 'sort', 'settings', 'created_from', 'created_by'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'settings' => 'array',
        'sort' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function dashboard()
    {
        return $this->belongsTo('App\Models\Common\Dashboard');
    }
}

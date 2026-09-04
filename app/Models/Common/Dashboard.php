<?php

namespace App\Models\Common;

use App\Abstracts\Model;
use Bkwld\Cloner\Cloneable;

class Dashboard extends Model
{
    use Cloneable;

    protected $table = 'dashboards';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'name', 'enabled', 'created_from', 'created_by'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsToMany(user_model_class(), 'App\Models\Auth\UserDashboard', 'dashboard_id', 'user_id');
    }

    public function widgets()
    {
        return $this->hasMany('App\Models\Common\Widget');
    }
}

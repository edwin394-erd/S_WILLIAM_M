<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    protected $fillable = ['name', 'impact'];

    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'installation_work_order', 'installation_id', 'work_order_id');
    }   
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'equipment_work_order', 'equipment_id', 'work_order_id');
    }

}

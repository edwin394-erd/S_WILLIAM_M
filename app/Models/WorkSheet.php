<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\OrderTask;

class WorkSheet extends Model
{
    protected $fillable = ['week_number', 'start_date', 'end_date', 'total_odm_scheduled', 'department_id', 'codigo', 'enviado'];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(OrderTask::class, WorkOrder::class, 'work_sheet_id', 'work_order_id', 'id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    

    
}
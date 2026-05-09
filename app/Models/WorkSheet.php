<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSheet extends Model
{
    protected $fillable = ['week_number', 'start_date', 'end_date', 'total_odm_scheduled', 'department_id', 'codigo'];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    
}
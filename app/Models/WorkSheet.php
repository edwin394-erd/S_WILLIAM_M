<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\OrderTask;

class WorkSheet extends Model
{
    use HasFactory;

    protected $fillable = ['week_number', 'start_date', 'end_date', 'total_odm_scheduled', 'department_id', 'codigo', 'enviado'];

    protected $appends = ['week_year', 'week_label', 'week_key'];

    public function getWeekYearAttribute()
    {
        return $this->start_date ? date('Y', strtotime($this->start_date)) : null;
    }

    public function getWeekLabelAttribute()
    {
        if (!$this->week_number) {
            return null;
        }

        $year = $this->week_year ? ' (' . $this->week_year . ')' : '';
        return 'Semana ' . $this->week_number . $year;
    }

    public function getWeekKeyAttribute()
    {
        return $this->week_number . '-' . ($this->week_year ?? '');
    }

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
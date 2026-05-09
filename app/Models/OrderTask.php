<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTask extends Model
{
    protected $fillable = [
        'work_order_id', 'department_id', 'discipline_id',
        'date', 'time_start', 'time_end',
        'status', 'observation', 'evidence_path'
    ];

    protected $casts = [
        'date'       => 'date',
        'time_start' => 'datetime',
        'time_end'   => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }
}
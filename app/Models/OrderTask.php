<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

use App\Models\OrderTaskEvidence;

class OrderTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id', 'department_id', 'discipline_id',
        'date', 'time_start', 'time_end',
        'status', 'observation', 'evidence_path', 'user_report_id'
    ];

    protected $casts = [
        'date'       => 'date',
        'time_start' => 'datetime',
        'time_end'   => 'datetime',
    ];

    public static function markOverduePendingAsNotCompleted(): int
    {
        return static::where('status', 'PENDIENTE')
            ->whereHas('workOrder.workSheet', function ($query) {
                $query->where('end_date', '<', Carbon::now('America/Caracas')->toDateString());
            })
            ->update(['status' => 'NO COMPLETADO']);
    }

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

    public function evidences(): HasMany
    {
        return $this->hasMany(OrderTaskEvidence::class, 'order_task_id');
    }
}
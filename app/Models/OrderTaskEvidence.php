<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OrderTaskEvidence extends Model
{
    use HasFactory;

    protected $table = 'order_task_evidences';

    protected $fillable = [
        'order_task_id',
        'path',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return $this->path ? Storage::url($this->path) : null;
    }

    public function orderTask(): BelongsTo
    {
        return $this->belongsTo(OrderTask::class);
    }
}

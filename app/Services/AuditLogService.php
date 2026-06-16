<?php

namespace App\Services;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class AuditLogService
{
    public static function record(string $action, $subject = null, array $data = []): AuditLog
    {
        $userId = Auth::id();

        $payload = [
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'old_values' => Arr::get($data, 'old'),
            'new_values' => Arr::get($data, 'new'),
            'meta' => Arr::get($data, 'meta'),
            'created_at' => Carbon::now('America/Caracas'),
            'updated_at' => Carbon::now('America/Caracas'),
        ];

        return AuditLog::create(array_filter($payload, fn ($value) => $value !== null));
    }
}

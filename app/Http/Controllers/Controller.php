<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;

abstract class Controller
{
    protected function logAudit(string $action, $subject = null, array $data = []): void
    {
        AuditLogService::record($action, $subject, $data);
    }
}

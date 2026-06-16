<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('resource')) {
            $query->where('subject_type', $request->resource);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('subject_id', 'like', "%{$search}%");
            });
        }

        $resourceOptions = AuditLog::RESOURCE_MAP;
        $actionOptions = AuditLog::ACTION_GROUPS;
        $userOptions = User::whereIn('id', AuditLog::select('user_id')->distinct()->whereNotNull('user_id'))->orderBy('name')->pluck('name', 'id')->toArray();

        $auditLogs = $query->take(200)->get();

        return view('audit-logs.index')->with([
            'auditLogs' => $auditLogs,
            'resourceOptions' => $resourceOptions,
            'actionOptions' => $actionOptions,
            'userOptions' => $userOptions,
            'selectedResource' => $request->resource,
            'selectedAction' => $request->action,
            'selectedUserId' => $request->user_id,
            'selectedDateFrom' => $request->date_from,
            'selectedDateTo' => $request->date_to,
            'searchQuery' => $request->search,
        ]);
    }
}

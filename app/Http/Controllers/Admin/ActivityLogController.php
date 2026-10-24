<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:60'],
        ]);

        $logs = ActivityLog::query()
            ->with('user')
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', 'like', "{$action}%"))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $actions = ActivityLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('admin.activity.index', compact('logs', 'actions', 'filters'));
    }
}

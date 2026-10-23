<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FollowUpTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = FollowUpTask::query()
            ->with(['inquiry', 'customer', 'assignedUser'])
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->paginate(20);

        return view('admin.tasks.index', compact('tasks'));
    }

    public function complete(Request $request, FollowUpTask $task): RedirectResponse
    {
        $data = $request->validate(['completion_notes' => ['nullable', 'string', 'max:2000']]);
        $task->update([
            'status' => 'done',
            'completed_at' => now(),
            'completion_notes' => $data['completion_notes'] ?? null,
        ]);

        return back()->with('success', 'Task completed.');
    }
}

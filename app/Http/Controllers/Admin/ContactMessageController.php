<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::query()->latest()->paginate(15);

        return view('admin.contacts.index', compact('messages'));
    }

    public function markHandled(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update([
            'status' => 'handled',
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('success', 'Message marked as handled.');
    }
}

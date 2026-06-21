<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class AdminContactController extends Controller
{
    /**
     * Display a listing of all contacts with filters.
     */
    public function index(Request $request)
    {
        $query = Contact::query();

        // Search by name or email
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->status && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        $contacts = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Get stats for summary
        $stats = [
            'total' => Contact::count(),
            'new' => Contact::where('status', 'new')->count(),
            'read' => Contact::where('status', 'read')->count(),
            'replied' => Contact::where('status', 'replied')->count(),
            'closed' => Contact::where('status', 'closed')->count(),
        ];

        return inertia('Admin/Contacts/Index', [
            'contacts' => $contacts,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
            ]
        ]);
    }

    /**
     * Display details for a specific contact.
     */
    public function show(Contact $contact)
    {
        // Mark as read if new or unread
        if ($contact->status === 'new') {
            $contact->markAsRead();
        }

        return inertia('Admin/Contacts/Show', [
            'contact' => $contact
        ]);
    }

    /**
     * Mark contact as read.
     */
    public function markAsRead(Contact $contact)
    {
        $contact->markAsRead();

        return redirect()->route('admin.contacts.show', $contact)
            ->with('success', 'Contact marked as read');
    }

    /**
     * Mark contact as closed.
     */
    public function markAsClosed(Contact $contact)
    {
        $contact->markAsClosed();

        return redirect()->route('admin.contacts.show', $contact)
            ->with('success', 'Contact marked as closed');
    }

    /**
     * Delete a contact.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')
            ->with('success', 'Contact deleted successfully');
    }
}

<?php

namespace App\Http\Controllers;

use App\Mail\ContactAdminMail;
use App\Mail\ContactConfirmationMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:5000',
        ]);

        // Save contact to database
        try {
            Contact::create($validated);
        } catch (\Exception $e) {
            \Log::error('Contact form database save failed: ' . $e->getMessage());
            // Continue anyway, still return success to user
        }

        // Queue admin notification email
        try {
            Mail::queue(new ContactAdminMail(
                name: $validated['name'],
                email: $validated['email'],
                subject: $validated['subject'],
                body: $validated['message'],
            ));
        } catch (\Exception $e) {
            \Log::warning('Contact form admin email queuing failed: ' . $e->getMessage());
        }

        // Queue confirmation email to the user
        try {
            Mail::queue(new ContactConfirmationMail(
                name: $validated['name'],
                email: $validated['email'],
                subject: $validated['subject'],
            ));
        } catch (\Exception $e) {
            \Log::warning('Contact form confirmation email queuing failed: ' . $e->getMessage());
        }

        // Always return success to user
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully! We will get back to you soon.',
            ], 200);
        }

        return back()->with('success', 'Message sent successfully! We will get back to you soon.');
    }
}

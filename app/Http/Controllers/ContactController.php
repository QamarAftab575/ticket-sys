<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Contact;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
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

        // Send email to admin (silently fails if mail config not set)
        try {
            Mail::raw(
                "Name: {$validated['name']}\n" .
                "Email: {$validated['email']}\n" .
                "Subject: {$validated['subject']}\n\n" .
                "Message:\n{$validated['message']}",
                function ($message) use ($validated) {
                    $message->to(config('mail.from.address', 'support@asira.in'))
                        ->subject("Contact Form: {$validated['subject']}")
                        ->replyTo($validated['email']);
                }
            );
        } catch (\Exception $e) {
            \Log::warning('Contact form admin email failed: ' . $e->getMessage());
            // Silently fail - don't throw error
        }

        // Send confirmation email to user (silently fails if mail config not set)
        try {
            Mail::raw(
                "Thank you for reaching out to Asira!\n\n" .
                "We received your message and will get back to you as soon as possible.\n\n" .
                "Best regards,\n" .
                "The Asira Team",
                function ($message) use ($validated) {
                    $message->to($validated['email'])
                        ->subject('We received your message - Asira');
                }
            );
        } catch (\Exception $e) {
            \Log::warning('Contact form confirmation email failed: ' . $e->getMessage());
            // Silently fail - don't throw error
        }

        // Always return success to user
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully! We will get back to you soon.'
            ], 200);
        }

        return back()->with('success', 'Message sent successfully! We will get back to you soon.');
    }
}

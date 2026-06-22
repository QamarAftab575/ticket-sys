<?php

namespace App\Http\Controllers;

use App\Models\PrivateNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivateNoteController extends Controller
{
    /**
     * Get the authenticated user's private note
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $note = PrivateNote::where('user_id', $user->id)->first();

        return response()->json([
            'content' => $note?->content ?? '',
            'updated_at' => $note?->updated_at,
        ]);
    }

    /**
     * Update or create the authenticated user's private note (auto-save)
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'content' => 'nullable|string',
        ]);

        $user = Auth::user();

        $note = PrivateNote::updateOrCreate(
            ['user_id' => $user->id],
            ['content' => $validated['content'] ?? '']
        );

        return response()->json([
            'id' => $note->id,
            'content' => $note->content,
            'updated_at' => $note->updated_at,
            'message' => 'Note saved successfully',
        ]);
    }

    /**
     * Delete the authenticated user's private note
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        PrivateNote::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Note deleted successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\InvitationService;
use Illuminate\Http\Request;

class InvitationAcceptController extends Controller
{
    public function __construct(private InvitationService $invitationService)
    {
    }

    /**
     * Single entry point for all invitation acceptance.
     * URL: /invitation/accept?token=XYZ
     */
    public function accept(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            abort(400, 'Invitation token is required');
        }

        return $this->invitationService->process($token);
    }
}

<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Default user model channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private per-user channel — notifications, personal task updates
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return $user->id === $userId;
});

// Private per-project channel — task events, comments
// Broadcast::channel('project.{projectId}', function ($user, $projectId) {
//     return true;
// });

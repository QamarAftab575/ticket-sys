<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'admin_reply',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scope for getting new contacts
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    // Scope for getting unread contacts
    public function scopeUnread($query)
    {
        return $query->whereIn('status', ['new', 'replied']);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->status = 'read';
        $this->save();
    }

    // Mark as replied
    public function markAsReplied($reply)
    {
        $this->admin_reply = $reply;
        $this->status = 'replied';
        $this->replied_at = now();
        $this->save();
    }

    // Mark as closed
    public function markAsClosed()
    {
        $this->status = 'closed';
        $this->save();
    }
}

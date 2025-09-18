<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['chat_id','sender_id','sender_is_admin','body','is_read'];

    public function chat() { return $this->belongsTo(Chat::class); }
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function attachments() { return $this->hasMany(MessageAttachment::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['message_id','path','mime','size'];

    public function message() { return $this->belongsTo(Message::class); }
}

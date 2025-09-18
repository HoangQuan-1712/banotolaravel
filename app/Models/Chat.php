<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','assigned_admin_id','status','last_message_at'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function assignedAdmin() { return $this->belongsTo(User::class, 'assigned_admin_id'); }
    public function messages() { return $this->hasMany(Message::class); }
}

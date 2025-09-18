<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/
Broadcast::routes(['middleware' => ['web','auth']]); // quan trọng để /broadcasting/auth hoạt động

// Ví dụ kênh private cho chat.{id}
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (!$chat) return false;

    // cho phép: chủ chat hoặc admin
    if ($user->id === ($chat->user_id ?? 0)) return ['id'=>$user->id, 'name'=>$user->name];
    if ($user->is_admin ?? false) return ['id'=>$user->id, 'name'=>$user->name, 'is_admin'=>true];
    return false;
});

// Ví dụ presence channel theo dõi admin online
Broadcast::channel('presence.admins', function ($user) {
    if ($user->is_admin ?? false) {
        return ['id'=>$user->id, 'name'=>$user->name];
    }
    return false;
});
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

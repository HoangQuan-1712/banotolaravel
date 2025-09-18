<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\TypingEvent;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request, Chat $chat)
    {
        $user = $request->user();

        // Kiểm tra quyền: user sở hữu chat hoặc admin
        $isOwner = $user->id === ($chat->user_id ?? 0);
        $isAdmin = $user->isAdmin();

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Không có quyền truy cập chat này.');
        }

        $limit = (int) $request->query('limit', 50);
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        $before = $request->query('before'); // ISO datetime string

        $baseQuery = $chat->messages()->with(['sender', 'attachments']);

        if ($before) {
            // Load older than the provided timestamp (for infinite scroll up)
            $baseQuery->where('created_at', '<', $before)
                ->orderByDesc('created_at')
                ->take($limit);
            $collection = $baseQuery->get()->sortBy('created_at')->values();
        } else {
            // Initial load: latest N messages, but return ASC for rendering top->bottom
            $baseQuery->orderByDesc('created_at')->take($limit);
            $collection = $baseQuery->get()->sortBy('created_at')->values();
        }

        $messages = $collection->map(function ($message) {
            return [
                'id' => $message->id,
                'chat_id' => $message->chat_id,
                'body' => $message->body,
                'sender_is_admin' => (bool)$message->sender_is_admin,
                'sender' => [
                    'id' => $message->sender_id,
                    'name' => $message->sender->name ?? 'User',
                    'is_admin' => (bool)$message->sender_is_admin,
                ],
                'attachments' => $message->attachments,
                'created_at' => $message->created_at,
            ];
        });

        return response()->json($messages);
    }

    public function store(Request $request, Chat $chat)
    {
        $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'files.*' => ['file', 'max:5120'], // <= 5MB/file
        ]);

        $user = $request->user();

        // Kiểm tra quyền: user sở hữu chat hoặc admin
        $isOwner = $user->id === ($chat->user_id ?? 0);
        $isAdmin = $user->isAdmin();

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Không có quyền truy cập chat này.');
        }

        abort_if($chat->status !== 'open', 422, 'Chat đã đóng.');

        $message = null;

        DB::transaction(function () use ($request, $chat, $user, &$message) {
            $message = $chat->messages()->create([
                'sender_id' => $user->id,
                'sender_is_admin' => $user->isAdmin(),
                'body' => $request->input('body'),
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if (!$file->isValid()) continue;
                    $path = $file->store('chat', 'public');
                    $message->attachments()->create([
                        'path' => $path,
                        'mime' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            $chat->last_message_at = now();

            // Nếu admin gửi lần đầu và chưa assign, assign cho admin này
            if ($user->isAdmin() && !$chat->assigned_admin_id) {
                $chat->assigned_admin_id = $user->id;
            }
            $chat->save();
        });

        // Phát sự kiện realtime + thông báo (an toàn khi static analyzer nghĩ có thể null)
        if ($message) {
            broadcast(new MessageSent($message))->toOthers();
            $this->notifyCounterpart($chat, $user, $message);
            $message->load(['sender', 'attachments']);
        }

        $responseMessage = $message ? [
            'id' => $message->id,
            'chat_id' => $message->chat_id,
            'body' => $message->body,
            'sender_is_admin' => (bool)$message->sender_is_admin,
            'sender' => [
                'id' => $message->sender_id,
                'name' => $message->sender->name ?? 'User',
                'is_admin' => (bool)$message->sender_is_admin,
            ],
            'attachments' => $message->attachments,
            'created_at' => $message->created_at,
        ] : null;

        return response()->json(['ok' => true, 'message' => $responseMessage]);
    }

    protected function notifyCounterpart(Chat $chat, $sender, Message $message)
    {
        // Nếu sender là user -> notify admin được assign; nếu chưa assign -> notify tất cả admin online
        if (!$sender->isAdmin()) {
            if ($chat->assigned_admin_id) {
                optional(User::find($chat->assigned_admin_id))
                    ?->notify(new NewMessageNotification($message));
            } else {
                // Có thể broadcast tới presence.admins để hiển thị "new chat"
                // Hoặc notify một nhóm admin mặc định (tùy bạn triển khai thêm)
            }
        } else {
            // sender là admin -> notify user
            optional($chat->user)->notify(new NewMessageNotification($message));
        }
    }

    // Typing indicator
    public function typing(Request $request, Chat $chat)
    {
        $user = $request->user();

        // Kiểm tra quyền: user sở hữu chat hoặc admin
        $isOwner = $user->id === ($chat->user_id ?? 0);
        $isAdmin = $user->isAdmin();

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Không có quyền truy cập chat này.');
        }

        $request->validate(['typing' => ['required', 'boolean']]);

        broadcast(new TypingEvent(
            chatId: $chat->id,
            userId: $user->id,
            userName: $user->name ?? 'User',
            isAdmin: $user->isAdmin(),
            typing: (bool)$request->boolean('typing'),
        ))->toOthers();

        return response()->json(['ok' => true]);
    }
}

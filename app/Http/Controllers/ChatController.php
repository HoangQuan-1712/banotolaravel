<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // Tạo hoặc lấy chat hiện tại của user
    public function open(Request $request)
    {
        $this->middleware('auth');

        $chat = Chat::firstOrCreate(
            ['user_id' => $request->user()->id, 'status' => 'open'],
            ['last_message_at' => now()]
        );

        // Nếu request từ AJAX, trả về JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'chat_id' => $chat->id,
                'status' => $chat->status,
                'user_id' => $chat->user_id
            ]);
        }

        return view('chat.box', compact('chat'));
    }

    // Admin list chat hàng đợi
    public function adminIndex(Request $request)
    {
        // Kiểm tra user có phải admin không
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Chỉ admin mới có thể truy cập.');
        }

        $chats = Chat::with([
            'user',
            'assignedAdmin',
            'messages' => function ($q) {
                $q->orderByDesc('created_at')->limit(1);
            }
        ])
            ->where('status', 'open')
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('admin.chats.index', compact('chats'));
    }

    // Admin xem chi tiết chat
    public function adminShow(Request $request, Chat $chat)
    {
        // Kiểm tra user có phải admin không
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Chỉ admin mới có thể truy cập.');
        }

        // Auto assign nếu chưa có admin
        if (!$chat->assigned_admin_id) {
            $chat->assigned_admin_id = $user->id;
            $chat->save();
        }

        return view('admin.chats.show', compact('chat'));
    }

    // Admin nhận (assign) một chat vào mình
    public function assign(Request $request, Chat $chat)
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        if (!$chat->assigned_admin_id) {
            $chat->assigned_admin_id = $request->user()->id;
            $chat->save();
        }
        return back()->with('success', 'Đã nhận chat.');
    }

    public function close(Request $request, Chat $chat)
    {
        $can = ($request->user()->is_admin ?? false) || $chat->user_id === $request->user()->id;
        abort_unless($can, 403);

        $chat->status = 'closed';
        $chat->save();

        return back()->with('success', 'Đã đóng chat.');
    }
}

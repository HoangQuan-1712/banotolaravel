@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h5 class="mb-3">Hỗ trợ trực tuyến</h5>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>Chat #{{ $chat->id }} @if($chat->assignedAdmin) • Admin: {{ $chat->assignedAdmin->name }} @endif</div>
      <div>
        <span id="admin-online-badge" class="badge bg-secondary">Admin: offline</span>
      </div>
    </div>

    <div id="messages" class="card-body" style="height: 420px; overflow-y: auto;">
      <div class="text-muted">Đang tải tin nhắn...</div>
    </div>

    <div class="card-footer">
      <form id="chat-form" enctype="multipart/form-data">
        @csrf
        <div class="mb-2 d-flex gap-2">
          <input type="text" id="msg-input" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off">
          <input type="file" id="file-input" name="files[]" multiple class="form-control" style="max-width: 260px;">
          <button class="btn btn-primary" type="submit">Gửi</button>
        </div>
        <div id="typing-indicator" class="text-muted small" style="display:none">Admin đang nhập...</div>
      </form>
    </div>
  </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/echo.pusher.min.js') }}"></script> {{-- build file (xem section JS) --}}
<script>
  const chatId = {{ $chat->id }};
  const messagesEl = document.getElementById('messages');
  const inputEl = document.getElementById('msg-input');
  const fileEl = document.getElementById('file-input');
  const typingEl = document.getElementById('typing-indicator');
  const adminOnlineBadge = document.getElementById('admin-online-badge');

  function appendMsg(m) {
    const who = m.sender.is_admin ? 'Admin' : (m.sender.name || 'Bạn');
    const side = m.sender.is_admin ? 'start' : 'end';
    const wrapper = document.createElement('div');
    wrapper.className = `mb-3 d-flex justify-content-${side}`;
    let files = '';
    if (m.attachments && m.attachments.length) {
      files = '<div class="mt-2 d-flex flex-wrap gap-2">' +
        m.attachments.map(a => `<a target="_blank" href="${a.url}" class="btn btn-sm btn-outline-secondary">Tệp</a>`).join('') +
        '</div>';
    }
    wrapper.innerHTML = `
      <div class="border rounded p-2" style="max-width:72%">
        <div class="small text-muted mb-1">${who} • ${new Date(m.created_at).toLocaleString()}</div>
        <div>${m.body ? m.body.replace(/</g,'&lt;') : ''}</div>
        ${files}
      </div>
    `;
    messagesEl.appendChild(wrapper);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  // Load initial
  axios.get(`{{ route('chat.messages.index', $chat) }}`).then(res=>{
    messagesEl.innerHTML = '';
    res.data.forEach(appendMsg);
  });

  // Echo subscribe
  window.Echo.private(`chat.${chatId}`)
    .listen('.message.sent', (e) => { appendMsg(e); })
    .listen('.chat.typing', (e) => {
      if (e.is_admin) {
        typingEl.style.display = e.typing ? 'block' : 'none';
      }
    });

  // Presence channel: admin online/offline
  window.Echo.join('presence.admins')
    .here((users) => {
      adminOnlineBadge.className = 'badge bg-' + (users.length ? 'success' : 'secondary');
      adminOnlineBadge.textContent = 'Admin: ' + (users.length ? 'online' : 'offline');
    })
    .joining((user) => {
      adminOnlineBadge.className = 'badge bg-success';
      adminOnlineBadge.textContent = 'Admin: online';
    })
    .leaving((user) => {
      // Không đảm bảo tất cả out, đơn giản hiển thị "offline" nếu rời hết
      // (ở client user không có danh sách chi tiết, đủ dùng)
    });

  // Send typing
  let typingTimer;
  inputEl.addEventListener('input', ()=>{
    axios.post(`{{ route('chat.typing', $chat) }}`, { typing: true });
    clearTimeout(typingTimer);
    typingTimer = setTimeout(()=>{
      axios.post(`{{ route('chat.typing', $chat) }}`, { typing: false });
    }, 1200);
  });

  // Send message
  document.getElementById('chat-form').addEventListener('submit', function(e){
    e.preventDefault();
    const text = inputEl.value.trim();
    const form = new FormData();
    if (text.length) form.append('body', text);
    if (fileEl.files.length) {
      for (const f of fileEl.files) form.append('files[]', f);
    }
    axios.post(`{{ route('chat.messages.store', $chat) }}`, form, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }).then(res=>{
      inputEl.value='';
      fileEl.value=null;
      // optional: append my message immediately (server broadcasts to others)
      appendMsg({
        id: res.data.message.id,
        chat_id: chatId,
        body: res.data.message.body,
        sender: { id: {{ auth()->id() }}, name: "{{ auth()->user()->name }}", is_admin: {{ auth()->user()->is_admin ? 'true' : 'false' }} },
        attachments: (res.data.message.attachments || []).map(a=>({url: `{{ asset('storage') }}/${a.path}`, mime: a.mime, size: a.size})),
        created_at: new Date().toISOString(),
      });
    });
  });
</script>
@endsection

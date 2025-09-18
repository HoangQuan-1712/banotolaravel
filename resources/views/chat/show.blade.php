@extends('layouts.app')

@section('content')
  <div class="container">
      <h1>Trang chat</h1>
      <p>Mở console để xem log khi nhận event</p>
  </div>
@endsection

@section('scripts')
<script type="module">
  import Echo from 'https://cdn.skypack.dev/laravel-echo';
  import Pusher from 'https://cdn.skypack.dev/pusher-js';

  window.Pusher = Pusher;
  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '{{ env('VITE_PUSHER_APP_KEY', env('PUSHER_APP_KEY')) }}',
    cluster: '{{ env('VITE_PUSHER_APP_CLUSTER', env('PUSHER_APP_CLUSTER')) }}',
    forceTLS: true
  });

  const chatId = {{ $chat->id }};
  window.Echo.private(`chat.${chatId}`)
    .listen('.message.sent', (e) => {
      console.log('RECEIVED:', e);
    });
</script>
@endsection

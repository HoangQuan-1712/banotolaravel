@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h2>Xác thực Email</h2>
        <p>Vui lòng kiểm tra email để xác thực tài khoản của bạn.</p>

        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">Gửi lại email xác thực</button>
        </form>
    </div>
@endsection

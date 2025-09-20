@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include('user.account.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header">Thông tin cá nhân</div>
                <div class="card-body">
                    <form action="{{ route('user.account.profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label>Tên</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}">
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Đổi mật khẩu</div>
                <div class="card-body">
                    <form action="{{ route('user.account.password.change') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label>Mật khẩu hiện tại</label>
                            <input type="password" name="current_password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Xác nhận mật khẩu mới</label>
                            <input type="password" name="new_password_confirmation" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

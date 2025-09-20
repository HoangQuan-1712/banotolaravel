@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            @include('user.account.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Sổ địa chỉ</h4>
                <a href="{{ route('user.addresses.create') }}" class="btn btn-primary">Thêm địa chỉ mới</a>
            </div>

            @if($addresses->isEmpty())
                <p>Bạn chưa có địa chỉ nào.</p>
            @else
                <div class="row">
                    @foreach($addresses as $address)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $address->name }} @if($address->is_default)<span class="badge bg-primary">Mặc định</span>@endif</h5>
                                    <p class="card-text">
                                        {{ $address->full_address }}<br>
                                        {{ $address->phone }}
                                    </p>
                                    <a href="{{ route('user.addresses.edit', $address) }}" class="btn btn-sm btn-info">Sửa</a>
                                    <form action="{{ route('user.addresses.destroy', $address) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

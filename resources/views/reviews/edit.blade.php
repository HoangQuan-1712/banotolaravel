@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card">
                    <div class="card-header">Sửa nội dung đánh giá</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('reviews.update', $review) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Nội dung</label>
                                <textarea name="content" class="form-control" rows="4" required>{{ old('content', $review->content) }}</textarea>
                                <div class="form-text">Bạn chỉ có thể sửa nội dung trong 7 ngày sau khi đăng.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Số sao</label>
                                <input type="text" class="form-control" value="{{ $review->rating }}/5" disabled>
                                <div class="form-text text-warning">Bạn không thể thay đổi số sao sau khi đã đánh giá.</div>
                            </div>

                            <button class="btn btn-primary" type="submit">Lưu thay đổi</button>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">Huỷ</a>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

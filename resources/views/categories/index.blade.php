@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-tags"></i> Danh Mục Xe Hơi</h4>
                        @auth
                            @if(auth()->user()->isAdmin())
                                <a class="btn btn-primary" href="{{ route('admin.categories.create') }}">
                                    <i class="fas fa-plus"></i> Thêm Danh Mục Mới
                                </a>
                            @endif
                        @endauth
                    </div>
                    <div class="card-body">
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($message = Session::get('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Mã Số</th>
                                        <th>Tên Danh Mục</th>
                                        <th>Số Lượng Xe</th>
                                        <th>Ngày Tạo</th>
                                        <th width="280px">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categories as $category)
                                        <tr>
                                            <td>{{ $category->id }}</td>
                                            <td>
                                                <strong>{{ $category->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $category->products->count() }} xe</span>
                                            </td>
                                            <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a class="btn btn-info btn-sm"
                                                        href="{{ route('categories.show', $category->id) }}"
                                                        title="Xem Chi Tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @auth
                                                        @if(auth()->user()->isAdmin())
                                                            <a class="btn btn-primary btn-sm"
                                                                href="{{ route('admin.categories.edit', $category->id) }}"
                                                                title="Chỉnh Sửa Danh Mục">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('admin.categories.destroy', $category->id) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm"
                                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này? Tất cả xe trong danh mục này cũng sẽ bị xóa.')"
                                                                    title="Xóa Danh Mục">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endauth
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Không tìm thấy danh mục xe nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

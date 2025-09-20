@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Quản lý Voucher</h4>
        <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary">Thêm Voucher</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Mã</th>
                        <th>Loại</th>
                        <th>Giá trị</th>
                        <th>Thời hạn</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td>{{ $voucher->name }}</td>
                            <td><code>{{ $voucher->code }}</code></td>
                            <td>{{ $voucher->type }}</td>
                            <td>{{ $voucher->value }}</td>
                            <td>{{ $voucher->start_date?->format('d/m/Y') }} - {{ $voucher->end_date?->format('d/m/Y') }}</td>
                            <td>
                                @if($voucher->active)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Không hoạt động</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="btn btn-sm btn-info">Sửa</a>
                                <form action="{{ route('admin.vouchers.destroy', $voucher) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Không có voucher nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $vouchers->links() }}
        </div>
    </div>
</div>
@endsection

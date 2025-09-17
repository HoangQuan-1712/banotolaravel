@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> Khách Hàng Đã Mua Hàng</h4>
                    <div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-users"></i> Tất Cả Người Dùng
                        </a>
                        <a href="{{ route('admin.users.statistics') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Thống Kê
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $customers->total() }}</h3>
                                    <small>Tổng Khách Hàng</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $customers->sum('orders_count') }}</h3>
                                    <small>Tổng Đơn Hàng</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $customers->avg('orders_count') > 0 ? number_format($customers->avg('orders_count'), 1) : 0 }}</h3>
                                    <small>Đơn Hàng TB/Khách</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $customers->where('orders_count', '>', 1)->count() }}</h3>
                                    <small>Khách Hàng Quay Lại</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mã Số</th>
                                    <th>Thông Tin Khách Hàng</th>
                                    <th>Số Đơn Hàng</th>
                                    <th>Đơn Hàng Gần Nhất</th>
                                    <th>Ngày Tham Gia</th>
                                    <th width="150px">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                    <tr>
                                        <td>{{ $customer->id }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $customer->name }}</strong><br>
                                                <small class="text-muted">{{ $customer->email }}</small>
                                                @if($customer->phone)
                                                    <br><small class="text-muted">📱 {{ $customer->phone }}</small>
                                                @endif
                                                @if($customer->address)
                                                    <br><small class="text-muted">📍 {{ Str::limit($customer->address, 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary fs-6">{{ $customer->orders_count }} đơn</span>
                                            @if($customer->orders_count > 1)
                                                <br><small class="text-success">✓ Khách hàng quay lại</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($customer->orders->count() > 0)
                                                <div>
                                                    <strong>{{ $customer->orders->first()->created_at->format('d/m/Y') }}</strong><br>
                                                    <small class="text-muted">{{ $customer->orders->first()->created_at->format('H:i') }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">Không có</span>
                                            @endif
                                        </td>
                                        <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-info btn-sm" href="{{ route('admin.users.show', $customer->id) }}" title="Xem Chi Tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a class="btn btn-primary btn-sm" href="{{ route('admin.users.edit', $customer->id) }}" title="Chỉnh Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Không tìm thấy khách hàng nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Xác nhận xóa người dùng')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Xác nhận xóa người dùng
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-info-circle"></i> Thông tin quan trọng</h5>
                        <p>Người dùng này có lịch sử đơn hàng. Hệ thống sẽ thực hiện <strong>ẩn tài khoản</strong> thay vì xóa vĩnh viễn để bảo toàn dữ liệu.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Thông tin người dùng</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Tên:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vai trò:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $user->role == 'admin' ? 'danger' : 'primary' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Thống kê đơn hàng</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Tổng đơn hàng:</strong></td>
                                    <td><span class="badge badge-info">{{ $orderSummary['total_orders'] }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Đơn hoàn thành:</strong></td>
                                    <td><span class="badge badge-success">{{ $orderSummary['completed_orders'] }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Đơn chờ xử lý:</strong></td>
                                    <td><span class="badge badge-warning">{{ $orderSummary['pending_orders'] }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tổng chi tiêu:</strong></td>
                                    <td><strong class="text-success">{{ number_format($orderSummary['total_spent']) }} $</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        @if($user->canBeDeleted())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Người dùng này không có đơn hàng nào. Sẽ được <strong>xóa vĩnh viễn</strong> khỏi hệ thống.
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-shield-alt"></i>
                                Người dùng này có {{ $orderSummary['total_orders'] }} đơn hàng ({{ $orderSummary['completed_orders'] }} hoàn thành). 
                                Tài khoản sẽ được <strong>ẩn khỏi hệ thống</strong> nhưng dữ liệu đơn hàng được bảo toàn.
                            </div>
                        @endif
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mr-3">
                            <i class="fas fa-arrow-left"></i> Hủy bỏ
                        </a>
                        
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                                @if($user->canBeDeleted())
                                    Xóa vĩnh viễn
                                @else
                                    Ẩn tài khoản
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

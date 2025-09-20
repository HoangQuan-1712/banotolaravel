<div class="modal fade" id="tierProgressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hạng Thành Viên & Tiến Trình</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $user = auth()->user();
                    $currentTier = $user->tier;
                    $nextTier = $currentTier ? \App\Models\CustomerTier::where('min_spent', '>', $currentTier->min_spent)->orderBy('min_spent')->first() : \App\Models\CustomerTier::orderBy('min_spent')->first();
                    $totalSpent = $user->lifetime_spent ?? 0;
                    $vouchersUsedCount = $user->voucherUsages()->count();
                @endphp

                <div class="text-center mb-4">
                    @if($currentTier)
                        <h4 style="color: {{ $currentTier->color }};">{{ $currentTier->name }}</h4>
                    @else
                        <h4>Thành viên</h4>
                    @endif
                </div>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Tổng chi tiêu:</span>
                        <strong>{{ number_format($totalSpent, 2) }} $</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Voucher đã sử dụng:</span>
                        <strong>{{ $vouchersUsedCount }}</strong>
                    </li>
                </ul>

                @if($nextTier)
                    <div class="mt-4">
                        <h6>Tiến trình lên hạng tiếp theo: <span style="color: {{ $nextTier->color }};">{{ $nextTier->name }}</span></h6>
                        @php
                            $needed = $nextTier->min_spent - $totalSpent;
                            $progress = $needed > 0 ? ($totalSpent / $nextTier->min_spent) * 100 : 100;
                        @endphp
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">{{ number_format($progress, 1) }}%</div>
                        </div>
                        @if($needed > 0)
                            <p class="text-center mt-2">Bạn cần chi tiêu thêm <strong>${{ number_format($needed, 2) }}</strong> để đạt hạng tiếp theo.</p>
                        @else
                            <p class="text-center mt-2">Chúc mừng! Bạn đã đủ điều kiện cho hạng tiếp theo.</p>
                        @endif
                    </div>
                @else
                    <p class="text-center mt-4">Chúc mừng! Bạn đã đạt hạng cao nhất.</p>
                @endif
            </div>
        </div>
    </div>
</div>

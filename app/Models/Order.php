<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'total_price',
        'deposit_amount',
        'status',
        'momo_request_id',
        'momo_order_id',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_AWAITING_DEPOSIT = 'chờ đặt cọc';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Derived, customer-friendly stage for UI timelines
    public function getStageAttribute()
    {
        $status = strtolower(trim($this->status));

        if ($status === self::STATUS_AWAITING_DEPOSIT || str_contains($status, 'chờ đặt cọc')) {
            return 'awaiting_deposit';
        }
        if (str_contains($status, 'đã đặt cọc')) {
            return 'deposited';
        }
        if ($status === self::STATUS_PROCESSING || str_contains($status, 'đang xử lý') || str_contains($status, 'chờ thanh toán')) {
            return 'processing';
        }
        if ($status === self::STATUS_COMPLETED || str_contains($status, 'đã thanh toán (momo)') || str_contains($status, 'hoàn thành')) {
            return 'completed';
        }
        if ($status === self::STATUS_CANCELLED || str_contains($status, 'đã hủy') || str_contains($status, 'không thành công') || str_contains($status, 'thất bại')) {
            return 'cancelled';
        }

        return 'processing';
    }

    public function getStageStepsAttribute()
    {
        return [
            'awaiting_deposit' => 'Chờ đặt cọc',
            'deposited' => 'Đã đặt cọc',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    }

    public function getStageIndexAttribute()
    {
        $map = [
            'awaiting_deposit' => 0,
            'deposited' => 1,
            'processing' => 2,
            'completed' => 3,
            'cancelled' => 3,
        ];
        return $map[$this->stage] ?? 0;
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Inventory reservation logic
    public function reserveStock(): void
    {
        // Reserve stock for each item (increase reserved_quantity)
        $this->loadMissing('items.product');
        DB::transaction(function () {
            foreach ($this->items as $item) {
                if (!$item->product) { continue; }
                $product = $item->product->lockForUpdate()->find($item->product->id) ?? $item->product;
                $reserved = (int) ($product->reserved_quantity ?? 0);
                $available = max(0, (int)$product->quantity - $reserved);
                $qtyToReserve = min($item->quantity, $available); // don't over-reserve
                if ($qtyToReserve > 0) {
                    $product->reserved_quantity = $reserved + $qtyToReserve;
                    $product->save();
                }
            }
        });
    }

    public function releaseReservedStock(): void
    {
        // Release reservations back to available (decrease reserved_quantity)
        $this->loadMissing('items.product');
        DB::transaction(function () {
            foreach ($this->items as $item) {
                if (!$item->product) { continue; }
                $product = $item->product->lockForUpdate()->find($item->product->id) ?? $item->product;
                $reserved = (int) ($product->reserved_quantity ?? 0);
                $release = min($reserved, (int)$item->quantity);
                if ($release > 0) {
                    $product->reserved_quantity = max(0, $reserved - $release);
                    $product->save();
                }
            }
        });
    }

    public function deductReservedToSold(): void
    {
        // Convert reservations to actual sale: decrease quantity AND reserved_quantity
        $this->loadMissing('items.product');
        DB::transaction(function () {
            foreach ($this->items as $item) {
                if (!$item->product) { continue; }
                $product = $item->product->lockForUpdate()->find($item->product->id) ?? $item->product;
                $reserved = (int) ($product->reserved_quantity ?? 0);
                $deduct = min((int)$item->quantity, $reserved, (int)$product->quantity);
                if ($deduct > 0) {
                    $product->reserved_quantity = max(0, $reserved - $deduct);
                    $product->quantity = max(0, (int)$product->quantity - $deduct);
                    $product->save();
                }
            }
        });
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing()
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_AWAITING_DEPOSIT => 'Chờ đặt cọc',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_AWAITING_DEPOSIT => 'primary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }
}

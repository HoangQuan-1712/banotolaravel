<?php
// app/Models/VoucherUsage.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model {
    public $timestamps = false;
    protected $table = 'voucher_usages';
    protected $fillable = ['voucher_id','user_id','order_id','used_at'];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

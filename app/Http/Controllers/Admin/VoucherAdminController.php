<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherAdminController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::latest()->paginate(15);
        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        return view('admin.vouchers.form');
    }

    public function store(Request $request)
    {
        $data = $this->validateVoucher($request);
        Voucher::create($data);
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher created successfully.');
    }

    public function edit(Voucher $voucher)
    {
        return view('admin.vouchers.form', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $this->validateVoucher($request, $voucher);
        $voucher->update($data);
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher updated successfully.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher deleted successfully.');
    }

    protected function validateVoucher(Request $request, Voucher $voucher = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:vouchers,code,' . ($voucher ? $voucher->id : ''),
            'type' => 'required|in:tiered_choice,random_gift,vip_tier,discount,service_voucher',
            'value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'group_code' => 'nullable|string|max:50',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_order_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'stock' => 'nullable|integer|min:0',
            'weight' => 'nullable|integer|min:1',
            'tier_level' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'active' => 'boolean',
        ];

        $data = $request->validate($rules);
        $data['active'] = $request->has('active');
        return $data;
    }
}

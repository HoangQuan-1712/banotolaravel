<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\UserAddress;
use App\Models\Voucher;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user()->load('tier');
        $addresses = $user->addresses()->orderBy('is_default', 'desc')->get();
        $vouchers = collect();
        if ($user->tier) {
            $vouchers = Voucher::active()->vipTier($user->tier->level)->get();
        }
        return view('user.account.dashboard', compact('user', 'addresses', 'vouchers'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return redirect()->route('user.account.dashboard')->with('success', 'Thông tin cá nhân đã được cập nhật.');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('user.account.dashboard')->with('success', 'Mật khẩu đã được thay đổi.');
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Models\User;

class AuthController extends Controller
{
    // Hiển thị form đăng ký
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('categories.index');
        }
        return view('auth.register');
    }

    // Xử lý đăng ký
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);



        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'customer',
        ]);
        // Gửi email xác thực
        event(new Registered($user));

        Auth::login($user);
        return redirect()->route('verification.notice')
            ->with('success', 'Vui lòng kiểm tra email để xác thực tài khoản trước khi đăng nhập.');
    }

    // Hiển thị form đăng nhập
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('categories.index');
        }
        return view('auth.login');
    }

    // Xử lý đăng nhập
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Nếu không tìm thấy user hoặc mật khẩu sai
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không chính xác.',
            ])->onlyInput('email');
        }

        // Kiểm tra xác thực email (trừ admin)
        if ($user->role !== 'admin' && !$user->hasVerifiedEmail()) {
            return back()->withErrors([
                'email' => 'Tài khoản của bạn chưa được xác thực. 
                    Vui lòng kiểm tra email: ' . e($user->email),
            ])->onlyInput('email');
        }

        // Nếu mọi thứ ok → login
        Auth::login($user);
        $request->session()->regenerate();



        // Nếu là user
        return redirect()->intended(route('categories.index'));
    }




    // Xử lý đăng xuất
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
    }
}

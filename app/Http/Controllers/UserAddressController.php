<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();
        return view('user.addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('user.addresses.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address_line_1' => 'required|string|max:255',
                'address_line_2' => 'nullable|string|max:255',
                'city' => 'required|string|max:100',
                'district' => 'required|string|max:100',
                'ward' => 'required|string|max:100',
                'postal_code' => 'nullable|string|max:10',
            ]);

            // Convert checkbox value to boolean
            $data = $request->all();
            $data['is_default'] = $request->has('is_default') ? true : false;
            
            // Log data for debugging
            \Log::info('Creating address with data:', $data);

            $address = Auth::user()->addresses()->create($data);
            
            \Log::info('Address created:', ['id' => $address->id, 'user_id' => $address->user_id]);

            if ($data['is_default']) {
                $address->setAsDefault();
            }

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Địa chỉ đã được thêm thành công.',
                    'address' => $address->load('user')
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error creating address:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi lưu địa chỉ: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
        
        return redirect()->route('user.addresses.index')
            ->with('success', 'Địa chỉ đã được thêm thành công.');
    }

    public function show(UserAddress $address)
    {
        $this->authorize('view', $address);
        return view('user.addresses.show', compact('address'));
    }

    public function edit(UserAddress $address)
    {
        $this->authorize('update', $address);
        return view('user.addresses.edit', compact('address'));
    }

    public function update(Request $request, UserAddress $address)
    {
        $this->authorize('update', $address);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'ward' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'is_default' => 'boolean',
        ]);

        $address->update($request->all());

        if ($request->is_default) {
            $address->setAsDefault();
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Địa chỉ đã được cập nhật thành công.',
                'address' => $address
            ]);
        }
        
        return redirect()->route('user.addresses.index')
            ->with('success', 'Địa chỉ đã được cập nhật thành công.');
    }

    public function destroy(UserAddress $address)
    {
        $this->authorize('delete', $address);
        
        $isDefault = $address->is_default;
        $address->delete();

        // Nếu xóa địa chỉ mặc định, đặt địa chỉ đầu tiên làm mặc định
        if ($isDefault) {
            $firstAddress = Auth::user()->addresses()->first();
            if ($firstAddress) {
                $firstAddress->setAsDefault();
            }
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Địa chỉ đã được xóa thành công.'
            ]);
        }
        
        return redirect()->route('user.addresses.index')
            ->with('success', 'Địa chỉ đã được xóa thành công.');
    }

    public function setDefault(UserAddress $address)
    {
        $this->authorize('update', $address);
        
        $address->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Đã đặt làm địa chỉ mặc định.'
        ]);
    }

    // API để lấy danh sách địa chỉ cho checkout
    public function getAddresses()
    {
        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();
        
        return response()->json([
            'addresses' => $addresses->map(function($address) {
                return [
                    'id' => $address->id,
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'full_address' => $address->full_address,
                    'is_default' => $address->is_default,
                    'formatted' => $address->formatted_address,
                ];
            })
        ]);
    }
}

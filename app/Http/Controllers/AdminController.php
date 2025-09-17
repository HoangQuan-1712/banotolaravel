<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // Categories management
    public function index()
    {
        $categories = Category::orderBy('created_at', 'desc')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ], [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category name already exists.',
            'name.max' => 'Category name cannot exceed 255 characters.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Category::create($request->all());
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create category. Please try again.')
                ->withInput();
        }
    }

    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ], [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category name already exists.',
            'name.max' => 'Category name cannot exceed 255 characters.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $category->update($request->all());
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update category. Please try again.')
                ->withInput();
        }
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Failed to delete category. Please try again.');
        }
    }

    // Products management
    public function productIndex()
    {
        $products = Product::with('category')->orderBy('created_at', 'desc')->get();
        return view('admin.products.index', compact('products'));
    }

    public function productCreate()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function productStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'Tên xe là bắt buộc.',
            'quantity.required' => 'Số lượng tồn kho là bắt buộc.',
            'quantity.min' => 'Số lượng tồn kho phải ít nhất là 0.',
            'price.required' => 'Giá là bắt buộc.',
            'price.min' => 'Giá phải ít nhất là 0.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'image.image' => 'File phải là ảnh.',
            'image.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'image.max' => 'Ảnh không được lớn hơn 2MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->all();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                // Validate file
                if (!$image->isValid()) {
                    return redirect()->back()
                        ->with('error', 'File ảnh không hợp lệ.')
                        ->withInput();
                }

                // Generate unique filename
                $imageName = time() . '_' . uniqid() . '_' . $image->getClientOriginalName();
                
                // Store image
                $imagePath = $image->storeAs('products', $imageName, 'public');
                
                if (!$imagePath) {
                    return redirect()->back()
                        ->with('error', 'Không thể lưu ảnh. Vui lòng thử lại.')
                        ->withInput();
                }
                
                $data['image'] = $imagePath;
                
                // Debug information
                \Log::info('Image uploaded successfully', [
                    'original_name' => $image->getClientOriginalName(),
                    'stored_path' => $imagePath,
                    'file_size' => $image->getSize(),
                    'mime_type' => $image->getMimeType()
                ]);
            }

            $product = Product::create($data);

            return redirect()->route('admin.products.index')
                ->with('success', 'Xe đã được thêm thành công.');
        } catch (\Exception $e) {
            \Log::error('Error creating product: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Không thể thêm xe. Lỗi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function productShow(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function productEdit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function productUpdate(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'Tên xe là bắt buộc.',
            'quantity.required' => 'Số lượng tồn kho là bắt buộc.',
            'quantity.min' => 'Số lượng tồn kho phải ít nhất là 0.',
            'price.required' => 'Giá là bắt buộc.',
            'price.min' => 'Giá phải ít nhất là 0.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'image.image' => 'File phải là ảnh.',
            'image.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'image.max' => 'Ảnh không được lớn hơn 2MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->all();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                // Validate file
                if (!$image->isValid()) {
                    return redirect()->back()
                        ->with('error', 'File ảnh không hợp lệ.')
                        ->withInput();
                }

                // Delete old image if exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                // Generate unique filename
                $imageName = time() . '_' . uniqid() . '_' . $image->getClientOriginalName();
                
                // Store image
                $imagePath = $image->storeAs('products', $imageName, 'public');
                
                if (!$imagePath) {
                    return redirect()->back()
                        ->with('error', 'Không thể lưu ảnh. Vui lòng thử lại.')
                        ->withInput();
                }
                
                $data['image'] = $imagePath;
                
                // Debug information
                \Log::info('Image updated successfully', [
                    'product_id' => $product->id,
                    'original_name' => $image->getClientOriginalName(),
                    'stored_path' => $imagePath,
                    'file_size' => $image->getSize(),
                    'mime_type' => $image->getMimeType()
                ]);
            }

            $product->update($data);

            return redirect()->route('admin.products.index')
                ->with('success', 'Xe đã được cập nhật thành công.');
        } catch (\Exception $e) {
            \Log::error('Error updating product: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Không thể cập nhật xe. Lỗi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function productDestroy(Product $product)
    {
        try {
            // Delete image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Car deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Failed to delete car. Please try again.');
        }
    }
}

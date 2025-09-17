<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'reviews']);

        // Search by keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function($cat) use ($search) {
                      $cat->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock availability
        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '=', 0);
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)->where('quantity', '<=', 5);
                    break;
            }
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->whereHas('reviews', function($q) use ($request) {
                $q->where('status', 'approved')
                  ->havingRaw('AVG(rating) >= ?', [$request->rating]);
            });
        }

        // Sort results
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'rating':
                $query->withAvg('reviews as avg_rating')
                      ->orderBy('avg_rating', 'desc');
                break;
            case 'popularity':
                $query->withCount('reviews as review_count')
                      ->orderBy('review_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        // Get search statistics
        $totalResults = $products->total();
        $priceRange = [
            'min' => Product::min('price'),
            'max' => Product::max('price')
        ];

        // Get popular search terms
        $popularSearches = $this->getPopularSearches();

        return view('search.index', compact(
            'products', 
            'categories', 
            'totalResults', 
            'priceRange',
            'popularSearches'
        ));
    }

    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(5)
            ->pluck('name')
            ->toArray();

        $categorySuggestions = Category::where('name', 'like', "%{$query}%")
            ->limit(3)
            ->pluck('name')
            ->toArray();

        return response()->json([
            'products' => $suggestions,
            'categories' => $categorySuggestions
        ]);
    }

    public function advanced(Request $request)
    {
        $query = Product::with(['category', 'reviews']);

        // Advanced filters
        if ($request->filled('brand')) {
            $query->where('name', 'like', "%{$request->brand}%");
        }

        if ($request->filled('year_from')) {
            $query->where('name', 'like', "%{$request->year_from}%");
        }

        if ($request->filled('year_to')) {
            $query->where('name', 'like', "%{$request->year_to}%");
        }

        // Multiple categories
        if ($request->filled('categories')) {
            $query->whereIn('category_id', $request->categories);
        }

        // Price range with slider
        if ($request->filled('price_range')) {
            $range = explode('-', $request->price_range);
            if (count($range) == 2) {
                $query->whereBetween('price', $range);
            }
        }

        // Stock status
        if ($request->filled('stock_status')) {
            $statuses = $request->stock_status;
            if (is_array($statuses)) {
                $query->where(function($q) use ($statuses) {
                    foreach ($statuses as $status) {
                        switch ($status) {
                            case 'in_stock':
                                $q->orWhere('quantity', '>', 0);
                                break;
                            case 'out_of_stock':
                                $q->orWhere('quantity', '=', 0);
                                break;
                            case 'low_stock':
                                $q->orWhere(function($subQ) {
                                    $subQ->where('quantity', '>', 0)
                                         ->where('quantity', '<=', 5);
                                });
                                break;
                        }
                    }
                });
            }
        }

        // Rating filter
        if ($request->filled('min_rating')) {
            $query->whereHas('reviews', function($q) use ($request) {
                $q->where('status', 'approved')
                  ->havingRaw('AVG(rating) >= ?', [$request->min_rating]);
            });
        }

        // Sort by multiple criteria
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'rating') {
            $query->withAvg('reviews as avg_rating')
                  ->orderBy('avg_rating', $sortOrder);
        } elseif ($sortBy === 'popularity') {
            $query->withCount('reviews as review_count')
                  ->orderBy('review_count', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $products = $query->paginate(16)->withQueryString();
        $categories = Category::all();

        return view('search.advanced', compact('products', 'categories'));
    }

    private function getPopularSearches()
    {
        // This would typically come from a search log table
        // For now, return some common car-related searches
        return [
            'Toyota', 'Honda', 'BMW', 'Mercedes', 'SUV', 'Sedan', 'Luxury', 'Electric'
        ];
    }

    public function compare(Request $request)
    {
        $productIds = $request->get('products', []);
        
        if (count($productIds) < 2) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất 2 sản phẩm để so sánh.');
        }

        if (count($productIds) > 4) {
            $productIds = array_slice($productIds, 0, 4);
        }

        $products = Product::with(['category', 'reviews'])
            ->whereIn('id', $productIds)
            ->get();

        return view('search.compare', compact('products'));
    }
}

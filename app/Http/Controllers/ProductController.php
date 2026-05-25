<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->query('search');
            $category = $request->query('category');
            $limit = min(max((int) $request->query('limit', 10), 1), 100);
            $page = max((int) $request->query('page', 1), 1);

            $cacheKey = 'products:' . md5(json_encode($request->query()));

            $keys = Cache::get('products_cache_keys', []);
            if (! in_array($cacheKey, $keys)) {
                $keys[] = $cacheKey;
                Cache::put('products_cache_keys', $keys, 3600);
            }

            $result = Cache::remember($cacheKey, 60, function () use ($search, $category, $limit, $page) {
                $query = Product::with(['createdBy', 'updatedBy']);

                if ($search) {
                    $query->where('title', 'like', '%' . $search . '%');
                }

                if ($category) {
                    $query->where('category', $category);
                }

                $paginator = $query->paginate($limit, ['*'], 'page', $page);

                return [
                    'data' => collect($paginator->items())->map(fn ($p) => $p->toApiResponse())->values(),
                    'meta' => [
                        'total' => $paginator->total(),
                        'per_page' => $paginator->perPage(),
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ];
            });

            return response()->json(array_merge(['status' => 200], $result));
        } catch (Throwable $e) {
            Log::error('Failed to fetch products', ['error' => $e->getMessage()]);

            return response()->json(['status' => 500, 'message' => 'Failed to fetch products. Please try again.'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $cacheKey = "product:{$id}";

            $product = Cache::remember($cacheKey, 60, function () use ($id) {
                return Product::with(['createdBy', 'updatedBy'])->find($id);
            });

            if (! $product) {
                return response()->json(['status' => 404, 'message' => 'Product not found'], 404);
            }

            return response()->json(['status' => 200, 'data' => $product->toApiResponse()]);
        } catch (Throwable $e) {
            Log::error('Failed to fetch product', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json(['status' => 500, 'message' => 'Failed to fetch product. Please try again.'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'images' => 'required|array|min:1',
            'images.*' => 'required|string|url',
        ]);

        try {
            $product = DB::transaction(function () use ($validated, $request) {
                return Product::create([
                    'title' => $validated['title'],
                    'price' => $validated['price'],
                    'description' => $validated['description'] ?? null,
                    'category' => $validated['category'],
                    'images' => $validated['images'],
                    'created_by_id' => $request->user()->id,
                    'updated_by_id' => $request->user()->id,
                ]);
            });

            $product->load(['createdBy', 'updatedBy']);
            $this->clearProductsListCache();

            return response()->json(['status' => 201, 'data' => $product->toApiResponse()], 201);
        } catch (Throwable $e) {
            Log::error('Failed to create product', ['error' => $e->getMessage()]);

            return response()->json(['status' => 500, 'message' => 'Failed to create product. Please try again.'], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['status' => 404, 'message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:100',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'required_with:images|string|url',
        ]);

        try {
            DB::transaction(function () use ($product, $validated, $request) {
                $product->update(array_merge($validated, [
                    'updated_by_id' => $request->user()->id,
                ]));
            });

            $product->load(['createdBy', 'updatedBy']);
            Cache::forget("product:{$id}");
            $this->clearProductsListCache();

            return response()->json(['status' => 200, 'data' => $product->toApiResponse()]);
        } catch (Throwable $e) {
            Log::error('Failed to update product', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json(['status' => 500, 'message' => 'Failed to update product. Please try again.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['status' => 404, 'message' => 'Product not found'], 404);
        }

        try {
            DB::transaction(function () use ($product) {
                $product->delete();
            });

            Cache::forget("product:{$id}");
            $this->clearProductsListCache();

            return response()->json(['status' => 200, 'message' => 'Product deleted successfully']);
        } catch (Throwable $e) {
            Log::error('Failed to delete product', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json(['status' => 500, 'message' => 'Failed to delete product. Please try again.'], 500);
        }
    }

    private function clearProductsListCache(): void
    {
        $keys = Cache::get('products_cache_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget('products_cache_keys');
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $shopIds = $request->user()->shops()->pluck('shops.id');
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 50);

        return ProductResource::collection(
            Product::query()
                ->whereIn('shop_id', $shopIds)
                ->orderBy('product_name')
                ->orderBy('id')
                ->paginate($perPage)
        );
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $shop = $request->user()->defaultShop();

        if (! $shop) {
            return response()->json(['message' => 'No shop is assigned to this user.'], 422);
        }

        $validated = $request->validated();
        $validated['shop_id'] = $shop->id;

        $product = Product::create($validated);

        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $product->update($request->validated());

        return new ProductResource($product->fresh());
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();

        return response()->json(['message' => 'Product archived successfully.']);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Supplier::class);

        $shopIds = $request->user()->shops()->pluck('shops.id');

        $query = Supplier::query()
            ->whereIn('shop_id', $shopIds)
            ->with('creator');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('whatsapp_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->query('status'), ['active', 'inactive', 'all'], true)) {
            $status = $request->query('status');

            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        } elseif ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $suppliers = $query->orderByDesc('updated_at')->paginate(15)->appends($request->query());

        return SupplierResource::collection($suppliers);
    }

    public function store(StoreSupplierRequest $request)
    {
        $this->authorize('create', Supplier::class);

        $shop = $request->user()->defaultShop();

        if (! $shop) {
            return response()->json(['message' => 'No shop is assigned to this user.'], 422);
        }

        $validated = $request->validated();
        $validated['phone'] = $this->normalizePhone($validated['phone']);
        $validated['whatsapp_phone'] = $validated['whatsapp_phone']
            ? $this->normalizePhone($validated['whatsapp_phone'])
            : null;
        $validated['shop_id'] = $shop->id;
        $validated['created_by'] = $request->user()->id;

        $supplier = Supplier::create($validated);

        return (new SupplierResource($supplier->fresh()))->response()->setStatusCode(201);
    }

    public function show(Supplier $supplier)
    {
        $this->authorize('view', $supplier);

        return new SupplierResource($supplier->load('creator'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        $validated = $request->validated();
        $validated['phone'] = $this->normalizePhone($validated['phone']);
        $validated['whatsapp_phone'] = $validated['whatsapp_phone']
            ? $this->normalizePhone($validated['whatsapp_phone'])
            : null;

        $supplier->update($validated);

        return new SupplierResource($supplier->fresh());
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        return str_starts_with($digits, '0') && strlen($digits) === 11
            ? '+' . '91' . substr($digits, 1)
            : $digits;
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('delete', $supplier);

        $supplier->delete();

        return response()->json([
            'message' => 'Supplier deleted successfully.',
            'data' => null,
        ]);
    }

    public function deactivate(Supplier $supplier)
    {
        $this->authorize('deactivate', $supplier);

        $supplier->update(['is_active' => false]);

        return new SupplierResource($supplier->fresh());
    }

    public function reactivate(Supplier $supplier)
    {
        $this->authorize('reactivate', $supplier);

        $supplier->update(['is_active' => true]);

        return new SupplierResource($supplier->fresh());
    }
}

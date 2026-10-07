<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\SmsServiceInterface;
use App\Contracts\WhatsAppServiceInterface;
use App\Enums\CommunicationStatus;
use App\Enums\MessageChannel;
use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Resources\SupplierResource;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderMessageGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    public function products(Request $request)
    {
        $this->authorize('viewAny', Product::class);
        $shop = $request->user()->defaultShop();

        if (! $shop) {
            return response()->json(['message' => 'No shop is assigned to this user.'], 422);
        }

        return ProductResource::collection(
            Product::query()->where('shop_id', $shop->id)->where('is_active', true)->orderBy('product_name')->get()
        );
    }

    public function suppliers(Request $request)
    {
        $this->authorize('viewAny', Supplier::class);
        $shop = $request->user()->defaultShop();

        if (! $shop) {
            return response()->json(['message' => 'No shop is assigned to this user.'], 422);
        }

        $query = Supplier::query()->where('shop_id', $shop->id)->with('creator')->orderBy('name');
        if (! $request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        return SupplierResource::collection($query->get());
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);
        $shop = $request->user()->defaultShop();

        if (! $shop) {
            return response()->json(['message' => 'No shop is assigned to this user.'], 422);
        }

        $query = PurchaseOrder::query()
            ->where('shop_id', $shop->id)
            ->with(['supplier', 'items.product', 'communications'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($orders) use ($search) {
                $orders->whereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('items', fn ($items) => $items->where('product_name_snapshot', 'like', "%{$search}%"));
            });
        }

        foreach (['supplier_id', 'status', 'channel'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->query('to'));
        }

        return PurchaseOrderResource::collection($query->paginate(20)->appends($request->query()));
    }

    public function store(Request $request, PurchaseOrderMessageGenerator $messageGenerator)
    {
        $this->authorize('create', PurchaseOrder::class);
        $shop = $request->user()->defaultShop();

        if (! $shop) {
            return response()->json(['message' => 'No shop is assigned to this user.'], 422);
        }

        $validated = Validator::make($request->all(), [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ])->validate();

        $supplier = Supplier::query()
            ->where('shop_id', $shop->id)
            ->where('is_active', true)
            ->find($validated['supplier_id']);

        if (! $supplier) {
            return response()->json(['message' => 'The selected supplier is unavailable.', 'errors' => [
                'supplier_id' => ['Choose an active supplier belonging to your shop.'],
            ]], 422);
        }

        $productIds = collect($validated['items'])->pluck('product_id');
        $products = Product::query()->where('shop_id', $shop->id)->whereIn('id', $productIds)->get()->keyBy('id');

        if ($products->count() !== $productIds->unique()->count()) {
            return response()->json(['message' => 'One or more selected products are unavailable.', 'errors' => [
                'items' => ['Every product must belong to your shop.'],
            ]], 422);
        }

        $order = DB::transaction(function () use ($shop, $request, $supplier, $validated, $products, $messageGenerator) {
            $order = PurchaseOrder::create([
                'shop_id' => $shop->id,
                'supplier_id' => $supplier->id,
                'created_by' => $request->user()->id,
                'status' => PurchaseOrderStatus::Draft,
            ]);

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'product_name_snapshot' => $product->product_name,
                    'barcode_snapshot' => $product->barcode,
                    'unit_snapshot' => $product->unit,
                ]);
            }

            $order->load('items');
            $order->message = $messageGenerator->generate($supplier, $order, $shop);
            $order->save();

            return $order;
        });

        return (new PurchaseOrderResource($order->load(['supplier', 'items', 'communications'])))
            ->response()->setStatusCode(201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('view', $purchaseOrder);

        return new PurchaseOrderResource($purchaseOrder->load(['supplier', 'items.product', 'communications']));
    }

    public function updateMessage(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);
        $validated = $request->validate(['message' => ['required', 'string', 'max:10000']]);
        $purchaseOrder->update([
            'message' => trim($validated['message']),
            'status' => PurchaseOrderStatus::Reviewed,
        ]);

        return new PurchaseOrderResource($purchaseOrder->load(['supplier', 'items', 'communications']));
    }

    public function saveDraft(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);
        $validated = $request->validate(['message' => ['required', 'string', 'max:10000']]);
        $purchaseOrder->update([
            'message' => trim($validated['message']),
            'status' => PurchaseOrderStatus::Draft,
        ]);

        return new PurchaseOrderResource($purchaseOrder->load(['supplier', 'items', 'communications']));
    }

    public function handoff(
        Request $request,
        PurchaseOrder $purchaseOrder,
        WhatsAppServiceInterface $whatsApp,
        SmsServiceInterface $sms
    ) {
        $this->authorize('update', $purchaseOrder);
        $validated = $request->validate([
            'channel' => ['required', 'in:whatsapp,sms'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $channel = MessageChannel::from($validated['channel']);
        $phone = $channel === MessageChannel::WhatsApp
            ? $purchaseOrder->supplier->whatsapp_phone
            : $purchaseOrder->supplier->phone;

        if (! $purchaseOrder->supplier->is_active || ! $phone) {
            return response()->json(['message' => 'The supplier has no suitable active contact number.', 'errors' => [
                'channel' => ['Check the supplier phone number for the selected channel.'],
            ]], 422);
        }

        $message = trim($validated['message']);
        $handoff = $channel === MessageChannel::WhatsApp
            ? ['handoff_url' => $whatsApp->handoff($phone, $message), 'status' => CommunicationStatus::Opened->value]
            : $sms->handoff($phone, $message);

        $communication = DB::transaction(function () use ($purchaseOrder, $channel, $message, $handoff) {
            $purchaseOrder->update([
                'channel' => $channel,
                'message' => $message,
                'status' => PurchaseOrderStatus::Reviewed,
            ]);

            return $purchaseOrder->communications()->create([
                'shop_id' => $purchaseOrder->shop_id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'channel' => $channel,
                'message' => $message,
                'status' => $handoff['status'],
            ]);
        });

        return response()->json([
            'data' => new PurchaseOrderResource($purchaseOrder->load(['supplier', 'items', 'communications'])),
            'handoff_url' => $handoff['handoff_url'],
            'handoff_status' => $communication->status->value,
        ]);
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('delete', $purchaseOrder);
        $purchaseOrder->update(['status' => PurchaseOrderStatus::Cancelled]);

        return new PurchaseOrderResource($purchaseOrder->load(['supplier', 'items', 'communications']));
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('delete', $purchaseOrder);
        $purchaseOrder->delete();

        return response()->json(['message' => 'Purchase order deleted successfully.']);
    }
}
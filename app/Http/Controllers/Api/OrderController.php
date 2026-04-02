<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api;

use App\Aggregates\OrderAggregate;
use App\Models\Order;
use App\Models\DailyOrderStats;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('items')->latest('placed_at');
        if ($request->has('status')) $query->byStatus($request->input('status'));
        if ($request->has('customer_id')) $query->byCustomer($request->input('customer_id'));
        return response()->json($query->paginate(20));
    }

    public function show(string $uuid): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->with(['items', 'notes'])->firstOrFail();
        return response()->json(['order' => $order]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'coupon_code' => 'nullable|string',
        ]);

        $subtotal = collect($validated['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']);
        $tax = round($subtotal * 0.0875, 2);
        $total = round($subtotal + $tax, 2);
        $uuid = (string) Str::uuid();

        OrderAggregate::retrieve($uuid)
            ->placeOrder(
                customerId: $validated['customer_id'],
                items: $validated['items'],
                subtotal: $subtotal,
                tax: $tax,
                total: $total,
                shippingAddress: $validated['shipping_address'],
                billingAddress: $validated['billing_address'],
                couponCode: $validated['coupon_code'] ?? null,
            )
            ->persist();

        $order = Order::where('uuid', $uuid)->with('items')->first();
        return response()->json(['order' => $order], 201);
    }

    public function confirm(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate(['estimated_delivery' => 'nullable|string']);
        OrderAggregate::retrieve($uuid)->confirm(auth()->id() ?? 'system', $validated['estimated_delivery'] ?? null)->persist();
        return response()->json(['order' => Order::where('uuid', $uuid)->first()]);
    }

    public function ship(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate(['tracking_number' => 'required|string', 'carrier' => 'required|string']);
        OrderAggregate::retrieve($uuid)->ship($validated['tracking_number'], $validated['carrier'])->persist();
        return response()->json(['order' => Order::where('uuid', $uuid)->first()]);
    }

    public function deliver(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate(['signed_by' => 'nullable|string']);
        OrderAggregate::retrieve($uuid)->deliver($validated['signed_by'] ?? null)->persist();
        return response()->json(['order' => Order::where('uuid', $uuid)->first()]);
    }

    public function cancel(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate(['reason' => 'required|string', 'refund' => 'boolean']);
        OrderAggregate::retrieve($uuid)->cancel($validated['reason'], auth()->id() ?? 'system', $validated['refund'] ?? false)->persist();
        return response()->json(['order' => Order::where('uuid', $uuid)->first()]);
    }

    public function refund(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:0.01', 'reason' => 'required|string']);
        $refundId = 're_' . Str::random(16);
        OrderAggregate::retrieve($uuid)->refund($validated['amount'], $validated['reason'], $refundId)->persist();
        return response()->json(['order' => Order::where('uuid', $uuid)->first(), 'refund_id' => $refundId]);
    }

    public function addNote(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate(['note' => 'required|string|max:2000', 'is_internal' => 'boolean']);
        OrderAggregate::retrieve($uuid)->addNote($validated['note'], auth()->id() ?? 'system', $validated['is_internal'] ?? true)->persist();
        return response()->json(['order' => Order::where('uuid', $uuid)->with('notes')->first()]);
    }

    public function events(string $uuid): JsonResponse
    {
        $events = \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent::query()
            ->where('aggregate_uuid', $uuid)
            ->orderBy('id')
            ->get()
            ->map(fn ($e) => ['id' => $e->id, 'type' => class_basename($e->event_class), 'data' => $e->event_properties, 'created_at' => $e->created_at]);
        return response()->json(['events' => $events]);
    }

    public function analytics(): JsonResponse
    {
        $stats = DailyOrderStats::orderByDesc('date')->take(30)->get();
        return response()->json(['analytics' => $stats]);
    }
}

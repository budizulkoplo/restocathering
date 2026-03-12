<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RestaurantOrderController extends Controller
{
    public function index(): View
    {
        $editOrderId = request()->integer('edit');
        $editingOrder = $editOrderId ? RestaurantOrder::query()->with('items')->find($editOrderId) : null;

        return view('modules.restaurant-orders', [
            'pageTitle' => 'Transaksi Resto',
            'tables' => DiningTable::query()->where('is_active', true)->orderBy('name')->get(),
            'menuItems' => MenuItem::query()->with('ingredients')->where('is_active', true)->where('is_resto', true)->orderBy('name')->get(),
            'orders' => RestaurantOrder::query()->with(['table', 'items'])->latest()->get(),
            'menuItemsJson' => MenuItem::query()->with('ingredients')->where('is_active', true)->where('is_resto', true)->orderBy('name')->get()->map(function (MenuItem $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => (float) $item->selling_price,
                ];
            })->values(),
            'editingOrder' => $editingOrder,
            'editingOrderJson' => $editingOrder ? [
                'id' => $editingOrder->id,
                'dining_table_id' => $editingOrder->dining_table_id,
                'source' => $editingOrder->source,
                'notes' => $editingOrder->notes,
                'discount' => (float) $editingOrder->discount,
                'cash_received' => (float) $editingOrder->cash_received,
                'items' => $editingOrder->items->map(fn (RestaurantOrderItem $item) => [
                    'menu_item_id' => $item->menu_item_id,
                    'qty' => (float) $item->qty,
                ])->values(),
            ] : null,
        ]);
    }

    public function publicOrder(string $token): View
    {
        $table = DiningTable::query()->where('qr_token', $token)->where('is_active', true)->firstOrFail();

        return view('public.table-order', [
            'table' => $table,
            'menuItems' => MenuItem::query()->where('is_active', true)->where('is_resto', true)->orderBy('name')->get(),
            'menuItemsJson' => MenuItem::query()->where('is_active', true)->where('is_resto', true)->orderBy('name')->get()->map(function (MenuItem $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => (float) $item->selling_price,
                ];
            })->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->saveOrder($request);
    }

    public function update(Request $request, RestaurantOrder $order): RedirectResponse
    {
        return $this->saveOrder($request, $order);
    }

    public function destroy(RestaurantOrder $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $order->load('items.menuItem.ingredients.ingredient');

            if ($order->stock_applied) {
                $this->restoreIngredientStockForRestaurant($order);
            }

            $order->delete();
        });

        return redirect()
            ->route('restaurant-orders.index')
            ->with('success', 'Order resto dibatalkan dan dihapus.');
    }

    private function saveOrder(Request $request, ?RestaurantOrder $existingOrder = null): RedirectResponse
    {
        $validated = $request->validate([
            'dining_table_id' => ['required', 'integer', 'exists:dining_tables,id'],
            'source' => ['required', 'in:cashier,qr,takeaway'],
            'notes' => ['nullable', 'string'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
        ]);

        $menuItems = MenuItem::query()
            ->with('ingredients')
            ->whereIn('id', collect($validated['items'])->pluck('menu_item_id')->all())
            ->get()
            ->keyBy('id');

        $subtotalCheck = collect($validated['items'])->sum(function (array $item) use ($menuItems) {
            $menu = $menuItems->get($item['menu_item_id']);
            return ((float) $item['qty']) * (float) ($menu?->selling_price ?? 0);
        });
        $discountCheck = min((float) ($validated['discount'] ?? 0), $subtotalCheck);
        $grandTotalCheck = max($subtotalCheck - $discountCheck, 0);
        $cashReceivedCheck = (float) ($validated['cash_received'] ?? 0);

        if (($validated['source'] ?? 'cashier') !== 'qr' && $cashReceivedCheck < $grandTotalCheck) {
            return back()->withInput()->with('error', 'Uang diterima kurang dari total bayar order resto.');
        }

        $createdOrderId = null;

        DB::transaction(function () use ($validated, $menuItems, $request, &$createdOrderId, $existingOrder) {
            $subtotal = 0;
            $rows = [];

            foreach ($validated['items'] as $item) {
                $menu = $menuItems->get($item['menu_item_id']);
                $qty = (float) $item['qty'];
                $lineTotal = $qty * (float) $menu->selling_price;
                $subtotal += $lineTotal;

                $rows[] = [
                    'menu_item_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'qty' => $qty,
                    'unit_price' => $menu->selling_price,
                    'subtotal' => $lineTotal,
                    'notes' => null,
                    'status' => 'queued',
                ];
            }

            $discount = min((float) ($validated['discount'] ?? 0), $subtotal);
            $grandTotal = max($subtotal - $discount, 0);
            $cashReceived = (float) ($validated['cash_received'] ?? 0);
            $isPaid = $cashReceived >= $grandTotal && $grandTotal > 0;

            $payload = [
                'dining_table_id' => $validated['dining_table_id'],
                'source' => $validated['source'],
                'status' => 'open',
                'payment_status' => $isPaid ? 'paid' : 'unpaid',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $grandTotal,
                'cash_received' => $cashReceived,
                'change_amount' => $isPaid ? max($cashReceived - $grandTotal, 0) : 0,
                'stock_applied' => false,
                'notes' => $validated['notes'] ?? null,
                'created_by' => (int) ($request->session()->get('user_id') ?? 0),
            ];

            if ($existingOrder) {
                $existingOrder->load('items.menuItem.ingredients.ingredient');
                if ($existingOrder->stock_applied) {
                    $this->restoreIngredientStockForRestaurant($existingOrder);
                }
                $existingOrder->update($payload);
                $existingOrder->items()->delete();
                $existingOrder->items()->createMany($rows);
                $order = $existingOrder;
            } else {
                $payload['order_number'] = 'RST-' . now()->format('Ymd') . '-' . str_pad((string) (RestaurantOrder::query()->count() + 1), 4, '0', STR_PAD_LEFT);
                $order = RestaurantOrder::query()->create($payload);
                $order->items()->createMany($rows);
            }

            $order->load('items.menuItem.ingredients.ingredient');
            $this->applyIngredientStockForRestaurant($order);
            $order->update(['stock_applied' => true]);

            $createdOrderId = $order->id;
        });

        return redirect()
            ->route('restaurant-orders.print', $createdOrderId)
            ->with('success', $existingOrder ? 'Order resto berhasil diperbarui.' : 'Order resto berhasil disimpan dan nota siap dicetak.');
    }

    public function storePublicOrder(Request $request, string $token): RedirectResponse
    {
        $table = DiningTable::query()->where('qr_token', $token)->where('is_active', true)->firstOrFail();

        $request->merge([
            'dining_table_id' => $table->id,
            'source' => 'qr',
        ]);

        return $this->store($request);
    }

    public function kitchen(): View
    {
        return view('modules.kitchen-display', [
            'pageTitle' => 'Display Dapur',
            'items' => RestaurantOrderItem::query()
                ->with(['order.table'])
                ->whereHas('order', fn ($query) => $query->whereIn('status', ['open', 'checkout']))
                ->orderByRaw("CASE status WHEN 'queued' THEN 1 WHEN 'preparing' THEN 2 WHEN 'ready' THEN 3 ELSE 4 END")
                ->latest()
                ->get(),
        ]);
    }

    public function updateKitchenStatus(Request $request, RestaurantOrderItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:queued,preparing,ready,served'],
        ]);

        DB::transaction(function () use ($validated, $item) {
            $oldStatus = $item->status;
            $item->update([
                'status' => $validated['status'],
                'prepared_at' => $validated['status'] === 'ready' ? now() : $item->prepared_at,
            ]);

            $order = $item->order()->with('items')->first();
            if ($order && $order->items->every(fn (RestaurantOrderItem $orderItem) => $orderItem->status === 'served')) {
                $order->update(['status' => 'checkout']);
            }
        });

        return back()->with('success', 'Status order dapur diperbarui.');
    }

    public function pay(Request $request, RestaurantOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'discount' => ['nullable', 'numeric', 'min:0'],
            'cash_received' => ['required', 'numeric', 'min:0'],
        ]);

        $discount = min((float) ($validated['discount'] ?? 0), (float) $order->subtotal);
        $grandTotal = max((float) $order->subtotal - $discount, 0);
        $cashReceived = (float) $validated['cash_received'];

        if ($cashReceived < $grandTotal) {
            return back()->with('error', 'Uang diterima kurang dari total bayar.');
        }

        $order->update([
            'status' => 'paid',
            'payment_status' => 'paid',
            'discount' => $discount,
            'total' => $grandTotal,
            'cash_received' => $cashReceived,
            'change_amount' => $cashReceived - $grandTotal,
        ]);

        return back()->with('success', 'Transaksi resto ditandai lunas.');
    }

    public function print(RestaurantOrder $order): View
    {
        return view('modules.restaurant-orders-print', [
            'order' => $order->load(['table', 'items']),
        ]);
    }

    private function applyIngredientStockForRestaurant(RestaurantOrder $order): void
    {
        foreach ($order->items as $item) {
            $menuItem = $item->menuItem;
            if (! $menuItem) {
                continue;
            }

            foreach ($menuItem->ingredients as $composition) {
                $ingredient = $composition->ingredient;
                if (! $ingredient instanceof Ingredient) {
                    continue;
                }

                $ingredient->update([
                    'stock' => max((float) $ingredient->stock - ((float) $composition->quantity * (float) $item->qty), 0),
                ]);
            }
        }
    }

    private function restoreIngredientStockForRestaurant(RestaurantOrder $order): void
    {
        foreach ($order->items as $item) {
            $menuItem = $item->menuItem;
            if (! $menuItem) {
                continue;
            }

            foreach ($menuItem->ingredients as $composition) {
                $ingredient = $composition->ingredient;
                if (! $ingredient instanceof Ingredient) {
                    continue;
                }

                $ingredient->update([
                    'stock' => (float) $ingredient->stock + ((float) $composition->quantity * (float) $item->qty),
                ]);
            }
        }
    }
}

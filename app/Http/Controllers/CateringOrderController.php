<?php

namespace App\Http\Controllers;

use App\Models\CateringOrder;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\MenuItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CateringOrderController extends Controller
{
    public function calendar(Request $request): View
    {
        $selectedMonth = max(1, min(12, (int) $request->integer('month', now()->month)));
        $selectedYear = max(2024, min(2100, (int) $request->integer('year', now()->year)));
        $editOrderId = $request->integer('edit');
        $editingOrder = $editOrderId ? CateringOrder::query()->with('items')->find($editOrderId) : null;

        $orders = CateringOrder::query()
            ->with('items')
            ->whereMonth('event_date', $selectedMonth)
            ->whereYear('event_date', $selectedYear)
            ->orderBy('event_date')
            ->orderBy('customer_name')
            ->get();

        $calendarMap = $orders
            ->groupBy(fn (CateringOrder $order) => $order->event_date->format('Y-m-d'))
            ->map(fn ($items) => $items->map(function (CateringOrder $order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'guest_count' => $order->guest_count,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total' => (float) $order->total,
                    'items' => $order->items->map(fn ($item) => [
                        'menu_name' => $item->menu_name,
                        'qty' => (float) $item->qty,
                    ])->values(),
                ];
            })->values())
            ->toArray();

        return view('home.calendar', [
            'pageTitle' => 'Kalender Reservasi Catering',
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthName' => Carbon::create($selectedYear, $selectedMonth, 1)->locale('id')->translatedFormat('F Y'),
            'calendarMap' => $calendarMap,
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(),
            'customersJson' => Customer::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (Customer $customer) => [
                    $customer->id => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'address' => $customer->address,
                    ],
                ]),
            'menuItems' => MenuItem::query()
                ->with('ingredients')
                ->where('is_active', true)
                ->where('is_catering', true)
                ->orderBy('name')
                ->get(),
            'menuItemsJson' => MenuItem::query()
                ->with('ingredients')
                ->where('is_active', true)
                ->where('is_catering', true)
                ->orderBy('name')
                ->get()
                ->map(function (MenuItem $item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'price' => (float) $item->selling_price,
                        'category' => $item->category_label,
                    ];
                })
                ->values(),
            'editingOrderJson' => $editingOrder ? [
                'id' => $editingOrder->id,
                'event_date' => $editingOrder->event_date?->toDateString(),
                'customer_id' => $editingOrder->customer_id,
                'customer_name' => $editingOrder->customer_name,
                'customer_phone' => $editingOrder->customer_phone,
                'customer_address' => $editingOrder->customer_address,
                'guest_count' => $editingOrder->guest_count,
                'status' => $editingOrder->status,
                'payment_status' => $editingOrder->payment_status,
                'discount' => (float) $editingOrder->discount,
                'down_payment' => (float) $editingOrder->down_payment,
                'cash_received' => (float) $editingOrder->cash_received,
                'notes' => $editingOrder->notes,
                'items' => $editingOrder->items->map(fn ($item) => [
                    'menu_item_id' => $item->menu_item_id,
                    'qty' => (float) $item->qty,
                ])->values(),
            ] : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->saveOrder($request);
    }

    public function update(Request $request, CateringOrder $order): RedirectResponse
    {
        return $this->saveOrder($request, $order);
    }

    public function destroy(CateringOrder $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $order->load('items.menuItem.ingredients.ingredient');

            if ($order->stock_applied) {
                $this->restoreIngredientStockForCatering($order);
            }

            $order->delete();
        });

        return redirect()
            ->route('catering.calendar')
            ->with('success', 'Reservasi catering dibatalkan dan dihapus.');
    }

    private function saveOrder(Request $request, ?CateringOrder $existingOrder = null): RedirectResponse
    {
        $validated = $request->validate([
            'event_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string'],
            'guest_count' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,reserved,confirmed,completed,cancelled'],
            'payment_status' => ['required', 'in:unpaid,dp,paid'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
        ]);

        $customer = null;

        if (! empty($validated['customer_id'])) {
            $customer = Customer::query()->find($validated['customer_id']);
        } elseif (! empty($validated['customer_name'])) {
            $customer = Customer::query()->create([
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?? null,
                'address' => $validated['customer_address'] ?? null,
                'is_active' => true,
            ]);
        }

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
        $downPaymentCheck = (float) ($validated['down_payment'] ?? 0);
        $cashReceivedCheck = (float) ($validated['cash_received'] ?? 0);

        if (($validated['payment_status'] ?? 'unpaid') === 'paid' && $cashReceivedCheck < $grandTotalCheck) {
            return back()->withInput()->with('error', 'Uang diterima kurang dari total bayar catering.');
        }

        if (($validated['payment_status'] ?? 'unpaid') === 'dp' && $cashReceivedCheck < $downPaymentCheck) {
            return back()->withInput()->with('error', 'Uang diterima kurang dari nominal DP.');
        }

        $createdOrderId = null;

        DB::transaction(function () use ($validated, $customer, $menuItems, $request, &$createdOrderId, $existingOrder) {
            $subtotal = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $menuItem = $menuItems->get($item['menu_item_id']);
                $qty = (float) $item['qty'];
                $unitPrice = (float) $menuItem->selling_price;
                $lineTotal = $qty * $unitPrice;
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'menu_item_id' => $menuItem->id,
                    'menu_name' => $menuItem->name,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'hpp' => (float) $menuItem->hpp,
                    'subtotal' => $lineTotal,
                    'notes' => null,
                ];
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $downPayment = (float) ($validated['down_payment'] ?? 0);
            $total = max($subtotal - $discount, 0);
            $balance = max($total - $downPayment, 0);
            $cashReceived = (float) ($validated['cash_received'] ?? 0);
            $changeAmount = max($cashReceived - ($validated['payment_status'] === 'paid' ? $total : $downPayment), 0);
            $eventDate = Carbon::parse($validated['event_date']);

            $payload = [
                'order_date' => now()->toDateString(),
                'event_date' => $eventDate->toDateString(),
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->name ?? $validated['customer_name'],
                'customer_phone' => $customer?->phone ?? ($validated['customer_phone'] ?? null),
                'customer_address' => $customer?->address ?? ($validated['customer_address'] ?? null),
                'guest_count' => (int) ($validated['guest_count'] ?? 0),
                'status' => $validated['status'],
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'down_payment' => $downPayment,
                'total' => $total,
                'cash_received' => $cashReceived,
                'change_amount' => $changeAmount,
                'balance_due' => $balance,
                'stock_applied' => false,
                'created_by' => (int) ($request->session()->get('user_id') ?? 0),
            ];

            if ($existingOrder) {
                $existingOrder->load('items.menuItem.ingredients.ingredient');
                if ($existingOrder->stock_applied) {
                    $this->restoreIngredientStockForCatering($existingOrder);
                }
                $existingOrder->update($payload);
                $existingOrder->items()->delete();
                $existingOrder->items()->createMany($orderItems);
                $order = $existingOrder;
            } else {
                $payload['order_number'] = 'CAT-' . $eventDate->format('Ymd') . '-' . str_pad((string) (CateringOrder::query()->count() + 1), 4, '0', STR_PAD_LEFT);
                $order = CateringOrder::query()->create($payload);
                $order->items()->createMany($orderItems);
            }

            $order->load('items.menuItem.ingredients.ingredient');

            if ($order->status === 'completed') {
                $this->applyIngredientStockForCatering($order);
                $order->update(['stock_applied' => true]);
            }

            $createdOrderId = $order->id;
        });

        return redirect()
            ->route('catering.print', $createdOrderId)
            ->with('success', $existingOrder ? 'Reservasi catering berhasil diperbarui.' : 'Reservasi catering berhasil disimpan dan nota siap dicetak.');
    }

    public function print(CateringOrder $order): View
    {
        return view('home.catering-print', [
            'order' => $order->load('items'),
        ]);
    }

    private function applyIngredientStockForCatering(CateringOrder $order): void
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

    private function restoreIngredientStockForCatering(CateringOrder $order): void
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

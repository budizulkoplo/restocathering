<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Ingredient;
use App\Models\IngredientPurchase;
use App\Models\MenuItem;
use App\Models\RestaurantProfile;
use App\Models\StockOpname;
use App\Models\RestaurantOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function customers(): View
    {
        return view('modules.customers', [
            'pageTitle' => 'Master Customer',
            'customers' => Customer::query()->latest()->get(),
        ]);
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Customer::query()->create([
            'code' => 'CUS-' . str_pad((string) (Customer::query()->count() + 1), 4, '0', STR_PAD_LEFT),
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('success', 'Customer berhasil disimpan.');
    }

    public function ingredients(): View
    {
        $editIngredient = request()->integer('edit');

        return view('modules.ingredients', [
            'pageTitle' => 'Bahan Baku',
            'ingredients' => Ingredient::query()->latest()->get(),
            'editing' => $editIngredient ? Ingredient::query()->find($editIngredient) : null,
        ]);
    }

    public function storeIngredient(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'latest_purchase_price' => ['nullable', 'numeric', 'min:0'],
            'price_unit_quantity' => ['nullable', 'numeric', 'gt:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Ingredient::query()->create([
            'code' => 'ING-' . str_pad((string) (Ingredient::query()->count() + 1), 4, '0', STR_PAD_LEFT),
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'stock' => $validated['stock'] ?? 0,
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
            'latest_purchase_price' => $validated['latest_purchase_price'] ?? 0,
            'price_unit_quantity' => $validated['price_unit_quantity'] ?? 1,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('success', 'Bahan baku berhasil disimpan.');
    }

    public function updateIngredient(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'latest_purchase_price' => ['nullable', 'numeric', 'min:0'],
            'price_unit_quantity' => ['nullable', 'numeric', 'gt:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ingredient->update([
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'stock' => $validated['stock'] ?? 0,
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
            'latest_purchase_price' => $validated['latest_purchase_price'] ?? 0,
            'price_unit_quantity' => $validated['price_unit_quantity'] ?? 1,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('ingredients.index')->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroyIngredient(Ingredient $ingredient): RedirectResponse
    {
        $ingredient->delete();

        return back()->with('success', 'Bahan baku berhasil dihapus.');
    }

    public function menuItems(): View
    {
        $editMenu = request()->integer('edit');

        return view('modules.menu-items', [
            'pageTitle' => 'Master Menu',
            'menuItems' => MenuItem::query()->with(['ingredients.ingredient'])->latest()->get(),
            'ingredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(),
            'ingredientsJson' => Ingredient::query()->where('is_active', true)->orderBy('name')->get()->map(function (Ingredient $ingredient) {
                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'unit' => $ingredient->unit,
                    'price' => (float) $ingredient->unit_cost,
                    'display_price' => (float) $ingredient->latest_purchase_price,
                    'price_unit_quantity' => (float) $ingredient->price_unit_quantity,
                ];
            })->values(),
            'editing' => $editMenu ? MenuItem::query()->with('ingredients')->find($editMenu) : null,
            'editingIngredientsJson' => $editMenu
                ? MenuItem::query()->with('ingredients')->find($editMenu)?->ingredients->map(fn ($row) => [
                    'ingredient_id' => $row->ingredient_id,
                    'quantity' => (float) $row->quantity,
                ])->values()
                : collect(),
        ]);
    }

    public function storeMenuItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'is_resto' => ['nullable', 'boolean'],
            'is_catering' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0.001'],
        ]);

        DB::transaction(function () use ($validated) {
            $menuItem = MenuItem::query()->create([
                'code' => 'MNU-' . str_pad((string) (MenuItem::query()->count() + 1), 4, '0', STR_PAD_LEFT),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'selling_price' => $validated['selling_price'] ?? 0,
                'is_resto' => (bool) ($validated['is_resto'] ?? false),
                'is_catering' => (bool) ($validated['is_catering'] ?? false),
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            foreach ($validated['ingredients'] ?? [] as $ingredientRow) {
                if (empty($ingredientRow['ingredient_id']) || empty($ingredientRow['quantity'])) {
                    continue;
                }

                $ingredient = Ingredient::query()->find($ingredientRow['ingredient_id']);

                $menuItem->ingredients()->create([
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $ingredientRow['quantity'],
                    'unit' => $ingredient->unit,
                    'ingredient_cost' => $ingredient->unit_cost,
                ]);
            }
        });

        return back()->with('success', 'Menu berhasil disimpan.');
    }

    public function updateMenuItem(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'is_resto' => ['nullable', 'boolean'],
            'is_catering' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0.001'],
        ]);

        DB::transaction(function () use ($validated, $menuItem) {
            $menuItem->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'selling_price' => $validated['selling_price'] ?? 0,
                'is_resto' => (bool) ($validated['is_resto'] ?? false),
                'is_catering' => (bool) ($validated['is_catering'] ?? false),
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            $menuItem->ingredients()->delete();

            foreach ($validated['ingredients'] ?? [] as $ingredientRow) {
                if (empty($ingredientRow['ingredient_id']) || empty($ingredientRow['quantity'])) {
                    continue;
                }

                $ingredient = Ingredient::query()->find($ingredientRow['ingredient_id']);

                $menuItem->ingredients()->create([
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $ingredientRow['quantity'],
                    'unit' => $ingredient->unit,
                    'ingredient_cost' => $ingredient->unit_cost,
                ]);
            }
        });

        return redirect()->route('menu-items.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroyMenuItem(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    public function tables(): View
    {
        return view('modules.tables', [
            'pageTitle' => 'Meja & QR',
            'tables' => DiningTable::query()->latest()->get(),
            'baseOrderUrl' => url('/resto/order'),
        ]);
    }

    public function storeTable(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $count = DiningTable::query()->count() + 1;

        DiningTable::query()->create([
            'name' => $validated['name'],
            'code' => 'TB-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT),
            'capacity' => $validated['capacity'],
            'location' => $validated['location'] ?? null,
            'qr_token' => (string) Str::uuid(),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('success', 'Meja berhasil disimpan.');
    }

    public function purchases(): View
    {
        return view('modules.purchases', [
            'pageTitle' => 'Belanja Bahan Baku',
            'purchases' => IngredientPurchase::query()->with('items')->latest()->get(),
            'ingredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(),
            'ingredientsJson' => Ingredient::query()->where('is_active', true)->orderBy('name')->get()->map(function (Ingredient $ingredient) {
                return ['id' => $ingredient->id, 'name' => $ingredient->name, 'unit' => $ingredient->unit];
            })->values(),
        ]);
    }

    public function storePurchase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_date' => ['required', 'date'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $total = 0;

            $purchase = IngredientPurchase::query()->create([
                'invoice_number' => 'BLI-' . now()->format('Ymd') . '-' . str_pad((string) (IngredientPurchase::query()->count() + 1), 4, '0', STR_PAD_LEFT),
                'purchase_date' => $validated['purchase_date'],
                'supplier_name' => $validated['supplier_name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total' => 0,
                'created_by' => (int) ($request->session()->get('user_id') ?? 0),
            ]);

            foreach ($validated['items'] as $item) {
                $ingredient = Ingredient::query()->findOrFail($item['ingredient_id']);
                $subtotal = (float) $item['qty'] * (float) $item['unit_price'];
                $total += $subtotal;

                $purchase->items()->create([
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'qty' => $item['qty'],
                    'unit' => $ingredient->unit,
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);

                $ingredient->update([
                    'stock' => (float) $ingredient->stock + (float) $item['qty'],
                    'latest_purchase_price' => $item['unit_price'],
                ]);
            }

            $purchase->update(['total' => $total]);
        });

        return back()->with('success', 'Belanja bahan baku berhasil disimpan.');
    }

    public function stockOpnames(): View
    {
        return view('modules.stock-opnames', [
            'pageTitle' => 'Stok Opname',
            'stockOpnames' => StockOpname::query()->with('items')->latest()->get(),
            'ingredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(),
            'ingredientsJson' => Ingredient::query()->where('is_active', true)->orderBy('name')->get()->map(function (Ingredient $ingredient) {
                return ['id' => $ingredient->id, 'name' => $ingredient->name, 'unit' => $ingredient->unit, 'stock' => (float) $ingredient->stock];
            })->values(),
        ]);
    }

    public function storeStockOpname(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'items.*.actual_stock' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $opname = StockOpname::query()->create([
                'opname_number' => 'OPN-' . now()->format('Ymd') . '-' . str_pad((string) (StockOpname::query()->count() + 1), 4, '0', STR_PAD_LEFT),
                'opname_date' => $validated['opname_date'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => (int) ($request->session()->get('user_id') ?? 0),
            ]);

            foreach ($validated['items'] as $item) {
                $ingredient = Ingredient::query()->findOrFail($item['ingredient_id']);
                $difference = (float) $item['actual_stock'] - (float) $ingredient->stock;

                $opname->items()->create([
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'system_stock' => $ingredient->stock,
                    'actual_stock' => $item['actual_stock'],
                    'difference' => $difference,
                    'unit' => $ingredient->unit,
                    'notes' => $item['notes'] ?? null,
                ]);

                $ingredient->update([
                    'stock' => $item['actual_stock'],
                ]);
            }
        });

        return back()->with('success', 'Stok opname berhasil disimpan.');
    }

    public function reports(): View
    {
        return view('modules.reports', [
            'pageTitle' => 'Laporan',
            'customerCount' => Customer::query()->count(),
            'ingredientCount' => Ingredient::query()->count(),
            'menuCount' => MenuItem::query()->count(),
            'purchaseTotal' => IngredientPurchase::query()->sum('total'),
            'restaurantOrderTotal' => RestaurantOrder::query()->where('payment_status', 'paid')->sum('total'),
            'stockValue' => Ingredient::query()->get()->sum(fn (Ingredient $ingredient) => (float) $ingredient->stock * (float) $ingredient->unit_cost),
            'stockRows' => Ingredient::query()->orderBy('name')->get(),
        ]);
    }

    public function settings(): View
    {
        return view('modules.settings', [
            'pageTitle' => 'Setting Sistem',
            'profile' => RestaurantProfile::query()->first(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $profile = RestaurantProfile::query()->first();

        if ($profile) {
            $profile->update($validated);
        } else {
            RestaurantProfile::query()->create($validated);
        }

        return back()->with('success', 'Setting resto berhasil diperbarui.');
    }
}

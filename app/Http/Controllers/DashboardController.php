<?php

namespace App\Http\Controllers;

use App\Models\CateringOrder;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Ingredient;
use App\Models\MenuItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now()->locale('id');
        $month = (int) $now->month;
        $year = (int) $now->year;

        $orders = CateringOrder::query()
            ->with('items')
            ->whereMonth('event_date', $month)
            ->whereYear('event_date', $year)
            ->orderBy('event_date')
            ->get();

        $paymentSummary = collect([
            'Belum Dibayar' => $orders->where('payment_status', 'unpaid')->count(),
            'DP' => $orders->where('payment_status', 'dp')->count(),
            'Lunas' => $orders->where('payment_status', 'paid')->count(),
        ]);

        $dailySummary = $orders
            ->groupBy(fn (CateringOrder $order) => $order->event_date->format('Y-m-d'))
            ->map(fn (Collection $items, string $date) => [
                'tanggal' => Carbon::parse($date),
                'total' => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $topDay = $dailySummary->first();

        return view('home.index', [
            'pageTitle' => 'Dashboard Resto & Catering',
            'monthLabel' => $now->translatedFormat('F Y'),
            'records' => $orders,
            'paymentSummary' => $paymentSummary,
            'summaryCards' => [
                [
                    'label' => 'Reservasi Catering',
                    'value' => $orders->count(),
                    'icon' => 'fa-calendar',
                    'color' => 'blue',
                    'subtext' => 'Periode ' . $now->translatedFormat('F Y'),
                ],
                [
                    'label' => 'Customer Aktif',
                    'value' => Customer::query()->count(),
                    'icon' => 'fa-users',
                    'color' => 'navy',
                ],
                [
                    'label' => 'Menu Terdaftar',
                    'value' => MenuItem::query()->count(),
                    'icon' => 'fa-cutlery',
                    'color' => 'yellow',
                ],
                [
                    'label' => 'Bahan Baku',
                    'value' => Ingredient::query()->count(),
                    'icon' => 'fa-cubes',
                    'color' => 'red',
                    'subtext' => DiningTable::query()->count() . ' meja aktif',
                ],
            ],
            'topDay' => $topDay,
            'totalRevenue' => $orders->sum('total'),
        ]);
    }
}

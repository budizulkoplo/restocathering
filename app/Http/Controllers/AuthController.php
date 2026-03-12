<?php

namespace App\Http\Controllers;

use App\Models\RestaurantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Form Login
    |--------------------------------------------------------------------------
    */

    public function login()
    {
        if (Session::get('login')) {
            return redirect()->route('main');
        }

        $setting = RestaurantProfile::query()->first();

        if (! $setting && Schema::hasTable('setting')) {
            $legacySetting = DB::table('setting')->first();

            if ($legacySetting) {
                $setting = (object) [
                    'name' => $legacySetting->nama_perusahaan ?? 'Resto Catering',
                    'address' => $legacySetting->alamat ?? null,
                    'phone' => $legacySetting->telepon ?? null,
                    'logo_path' => $legacySetting->path_logo ?? null,
                ];
            }
        }

        $selectedMonth = max(1, min(12, (int) request()->integer('month', now()->month)));
        $selectedYear = max(2024, min(2100, (int) request()->integer('year', now()->year)));
        $calendarMap = [];

        if (Schema::hasTable('catering_orders')) {
            $calendarMap = DB::table('catering_orders')
                ->select(['event_date', 'order_number', 'customer_name', 'guest_count', 'status', 'payment_status'])
                ->whereMonth('event_date', $selectedMonth)
                ->whereYear('event_date', $selectedYear)
                ->orderBy('event_date')
                ->orderBy('customer_name')
                ->get()
                ->groupBy('event_date')
                ->map(function ($items) {
                    return $items->map(function ($item) {
                        return [
                            'order_number' => (string) $item->order_number,
                            'customer_name' => (string) $item->customer_name,
                            'guest_count' => (int) ($item->guest_count ?? 0),
                            'status' => (string) $item->status,
                            'payment_status' => (string) $item->payment_status,
                        ];
                    })->values();
                })
                ->toArray();
        }

        $currentPeriod = Carbon::create($selectedYear, $selectedMonth, 1);
        $prevPeriod = $currentPeriod->copy()->subMonth();
        $nextPeriod = $currentPeriod->copy()->addMonth();

        return view('auth.login', [
            'setting' => $setting,
            'calendarMap' => $calendarMap,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthName' => $currentPeriod->locale('id')->translatedFormat('F Y'),
            'prevMonthUrl' => route('login', ['month' => $prevPeriod->month, 'year' => $prevPeriod->year]),
            'nextMonthUrl' => route('login', ['month' => $nextPeriod->month, 'year' => $nextPeriod->year]),
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | Proses Login
    |--------------------------------------------------------------------------
    */

    public function authenticate(Request $request)
    {

        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::query()
            ->where('username', $request->username)
            ->where('is_active', true)
            ->when(
                Schema::hasColumn('users', 'deleted_at'),
                fn ($query) => $query->whereNull('deleted_at')
            )
            ->first();

        if (! $user) {
            return back()
                ->withInput()
                ->with('error', 'Username tidak ditemukan');
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()
                ->withInput()
                ->with('error', 'Password salah');
        }

        Auth::login($user);

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        Session::put([
            'login' => true,
            'user_id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'level' => $user->level,
            'foto' => $user->foto,
            'email' => $user->email,
        ]);

        $request->session()->regenerate();

        return redirect()
                ->route('main')
                ->with('success', 'Login berhasil');

    }




    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();

        Session::flush();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')
                ->with('success', 'Logout berhasil');

    }



}

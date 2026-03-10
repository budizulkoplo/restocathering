<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Form Login
    |--------------------------------------------------------------------------
    */

    public function login()
    {
        // jika sudah login redirect ke dashboard
        if (Session::get('login')) {
            return redirect()->route('main');
        }

        $setting = DB::table('setting')->first();
        $selectedMonth = max(1, min(12, (int) request()->integer('month', now()->month)));
        $selectedYear = max(2024, min(2100, (int) request()->integer('year', now()->year)));
        $calendarMap = [];

        if (Schema::hasTable('cuti')) {
            $calendarMap = DB::table('cuti')
                ->select(['tanggal', 'pegawai_id', 'pegawai_nama', 'jabatan', 'status'])
                ->whereMonth('tanggal', $selectedMonth)
                ->whereYear('tanggal', $selectedYear)
                ->orderBy('tanggal')
                ->orderBy('pegawai_nama')
                ->get()
                ->groupBy('tanggal')
                ->map(function ($items) {
                    return $items->map(function ($item) {
                        return [
                            'pegawai_id' => (string) $item->pegawai_id,
                            'pegawai_nama' => (string) $item->pegawai_nama,
                            'jabatan' => (string) ($item->jabatan ?? ''),
                            'status' => (string) $item->status,
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


        // cari user
        $user = DB::table('user')

            ->where('username', $request->username)
            ->whereNull('deleted_at')
            ->first();


        // cek username
        if (!$user)
        {
            return back()
                ->withInput()
                ->with('error', 'Username tidak ditemukan');
        }


        // cek password
        if (!Hash::check($request->password, $user->password))
        {
            return back()
                ->withInput()
                ->with('error', 'Password salah');
        }



        /*
        |--------------------------------------------------------------------------
        | Simpan Session Login
        |--------------------------------------------------------------------------
        */

        Session::put([

            'login' => true,

            'user_id' => $user->id,

            'username' => $user->username,

            'name' => $user->name,

            'level' => $user->level,

            'foto' => $user->foto,

            'email' => $user->email,

        ]);


        /*
        |--------------------------------------------------------------------------
        | Regenerate Session (security)
        |--------------------------------------------------------------------------
        */

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

        Session::flush();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')
                ->with('success', 'Logout berhasil');

    }



}

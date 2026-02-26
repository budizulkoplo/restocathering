<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

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

        return view('auth.login', compact('setting'));
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
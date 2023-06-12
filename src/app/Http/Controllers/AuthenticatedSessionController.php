<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function login(Request $request)
    {
        // セッションIDの再生成、セッション固定攻撃対策
        $request->session()->regenerate();
        // CSRF トークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        if (Auth::check()) {
            return redirect('/')->route('timecard');
        } else {
            return view('auth/login');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        // CSRF トークンを再生成して、二重送信対策
        $request->session()->regenerateToken();
        return view('auth/login');
    }
}

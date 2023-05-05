<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;

class RegisterUserController extends Controller
{
    protected function getRegister()
    {
        return view('auth.register');
    }

    protected function postRegister(RegisterRequest $request)
    {
        // ユーザ登録処理
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // ホーム画面へリダイレクト
        return redirect('/');
    }
}

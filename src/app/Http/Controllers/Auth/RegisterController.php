<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use App\Mail\EmailVerification;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    // /**
    //  * Where to redirect users after registration.
    //  *
    //  * @var string
    //  */
    // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            // 'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    // /**
    //  * Create a new user instance after a valid registration.
    //  *
    //  * @param  array  $data
    //  * @return \App\Models\User
    //  */
    // protected function create(array $data)
    // {
    //     return User::create([
    //         'name' => $data['name'],
    //         'email' => $data['email'],
    //         'password' => Hash::make($data['password']),
    //     ]);
    // }

    // /**
    //  * ユーザ登録画面の表示
    //  * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
    //  */
    // protected function getRegister()
    // {
    //     return view('auth.register');
    // }

    // /**
    //  * ユーザ登録機能
    //  * @param array $data
    //  * @return unknown
    //  */
    // protected function postRegister(array $data)
    // {
    //     // ユーザ登録処理
    //     User::create([
    //         'name' => $data['name'],
    //         'email' => $data['email'],
    //         'password' => Hash::make($data['password']),
    //     ]);

    //     // ホーム画面へリダイレクト
    //     return redirect('/timecard');
    // }

    public function pre_check(Request $request)
    {
        $this->validator($request->all())->validate();
        //emailだけ指定し、フラッシュデータとして保持
        $request->flashOnly('email');

        $login_data = $request->all();
        // password マスキング
        $login_data['password_mask'] = '******';

        return view('auth.register_check')->with($login_data);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verify_token' => base64_encode($data['email']),
        ]);

        // Mailableクラス
        $email = new EmailVerification($user);
        // $user->email:宛先
        Mail::to($user->email)->send($email);

        return $user;
    }

    public function register(Request $request)
    {
        // DB登録したユーザーを設定
        event(new Registered($user = $this->create( $request->all() )));

        return view('auth.registered');
    }

    public function showForm($email_token)
    {
        // dd($email_token);
        // 使用可能なトークンか
        if (!User::where('email_verify_token', $email_token)->exists()) {
            return view('auth.main.register')->with('message', '無効なトークンです。');
        } else {
            $user = User::where('email_verify_token', $email_token)->first();
            // 本登録済みユーザーか
            if ($user->status == config('const.USER_STATUS.REGISTER')) //REGISTER=1
            {
                logger("status" . $user->status);
                return view('auth.main.register')->with('message', 'すでに本登録されています。ログインして利用してください。');
            }
            // ユーザーステータス更新
            $user->status = config('const.USER_STATUS.MAIL_AUTHED');
            // dd($user);
            $user->email_verified_at = Carbon::now();
            if ($user->save()) {
                // dd($email_token);
                return view('auth.main.register', compact('email_token'));
            } else {
                return view('auth.main.register')->with('message', 'メール認証に失敗しました。再度、メールからリンクをクリックしてください。');
            }
        }
    }

    public function mainCheck(Request $request, $email_token)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = new User();
        $user->name = $request->name;

        return view('auth.main.register_check', compact('user', 'email_token'));
    }

    public function mainRegister(Request $request, $email_token)
    {
        $user = User::where('email_verify_token', $email_token)->first();
        $user->status = config('const.USER_STATUS.REGISTER');
        $user->name = $request->name;
        $user->save();

        return view('auth.main.registered');
    }

}

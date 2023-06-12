<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'password' => ['required', 'string','min:8', 'max:191', 'confirmed'],
        ]);
    }

    /**
     * 仮会員登録内容確認
     */
    public function pre_check(Request $request)
    {
        $this->validator($request->all())->validate();
        //emailだけ指定し、フラッシュデータとして保持
        $request->flashOnly('email');

        $login_data = $request->all();
        // password マスキング
        $login_data['password_mask'] = '******';

        return view('auth.pre_register_check')->with($login_data);
    }

    /**
     * 仮会員登録データの追加、仮登録確認メールの処理
     */
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

    /**
     * 仮会員登録完了
     */
    public function register(Request $request)
    {
        // DB登録したユーザーを設定
        event(new Registered($user = $this->create( $request->all() )));

        return view('auth.pre_registered');
    }

    /**
     * 本会員登録フォーム
     */
    public function showForm($email_token)
    {
        // 使用可能なトークンか
        if (!User::where('email_verify_token', $email_token)->exists()) {
            return view('auth.main.register')->with('message', '無効なトークンです。');
        } else {
            $user = User::where('email_verify_token', $email_token)->first();
            // 本登録済みユーザーか
            // ステータス値は config/const.php で管理
            if ($user->status == config('const.USER_STATUS.REGISTER'))
            {
                logger("status" . $user->status);
                return view('auth.main.register')->with('message', 'すでに本登録されています。ログインして利用してください。');
            }
            // ユーザーステータスをメール認証済に更新
            $user->status = config('const.USER_STATUS.MAIL_AUTHED');;
            $user->email_verified_at = Carbon::now();

            if ($user->save()) {
                return view('auth.main.register', compact('email_token'));
            } else {
                return view('auth.main.register')->with('message', 'メール認証に失敗しました。再度、メールからリンクをクリックしてください。');
            }
        }
    }

    /**
     * 本会員登録内容確認
     */
    public function mainCheck(Request $request, $email_token)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:191'],
        ]);

        $user = new User();
        $user->name = $request->name;

        return view('auth.main.register_check', compact('user', 'email_token'));
    }

    /**
     * 本会員登録完了
     */
    public function mainRegister(Request $request, $email_token)
    {
        $user = User::where('email_verify_token', $email_token)->first();
        // ユーザーステータスを本登録済に更新
        $user->status = config('const.USER_STATUS.REGISTER');
        $user->name = $request->name;
        $user->save();

        return view('auth.main.registered');
    }

}

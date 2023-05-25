<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    /**
     * $userを設定し、クラス変数に代入
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * 認証メールに使用するviewと、tokenを返すクラスを作成
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('【Atte】仮登録が完了しました')
            ->view('auth.email.pre_register')
            ->with(['token' => $this->user->email_verify_token,]);
    }
}

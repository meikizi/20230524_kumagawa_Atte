# アプリケーション名
Atte（アット）

PHPのフレームワークLaravelを使っての勤怠管理システムです。
LaravelのAuth認証機能を利用し、メールによる二段階認証を行います。
1段階目：仮登録画面でメールアドレスを入力することで、そのアドレス宛に本登録のURLが記載されたメールが送信されます。
この状態では仮登録扱いとなり、ログインすることはできません。
2段階目：1段階目で送信されたメールに記載されているURLから本登録画面へアクセスします。
そこで本登録に必要な情報を入力することで本登録が完了します。

勤怠機能：1日で何度も休憩が可能、日を跨いだ時点で翌日の出勤操作に切り替える
日付別勤怠情報一覧機能：日付を選択することで、その日付の勤怠情報を表示
ユーザー一覧機能：ユーザー名一覧からユーザー検索を行うことにより、ユーザー別勤怠情報一覧ページにアクセス
ユーザー別勤怠情報一覧機能：検索したユーザーの勤怠情報を表示

< --- トップ画面の画像 --- >
https://github.com/meikizi/20230614_kumagawa_atte/issues/3#issue-1752337262

## 作成した目的
模擬案件を通して実践に近い開発経験をつむために作成しました。

## アプリケーションURL
URL: 52.194.241.146

テスト用メールサーバー
mailtrapを使用して実装しています。

## 機能一覧
・　会員登録とメールによる二段階認証
・　ログイン ・ ログアウト機能
・　出退勤時間記録
・　日付別勤怠情報一覧
・　ユーザー一覧
・　ユーザー別勤怠情報一覧

## 使用技術(実行環境)
言語 : PHP 7.4以上
フレームワーク : Laravel 8.83.27
ウェブサーバー : Apache/2.4.57
MySQL 8.0 (RDS)

## テーブル設計
https://github.com/meikizi/20230614_kumagawa_atte/issues/1#issue-1752301650

## ER図
https://github.com/meikizi/20230614_kumagawa_atte/issues/2#issue-1752304410

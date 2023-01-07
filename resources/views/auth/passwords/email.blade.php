@extends('layouts.app')

@section('title', 'inCul総合ログインパスワードの再設定')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">inCul総合ログインパスワードの再設定
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-5 col-form-label text-md-right">ご登録のメールアドレス</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <div class="col-md-6 offset-md-5 text-right">
                                <button type="submit" class="btn btn-primary">
                                    送信
                                </button>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <small>
                                    ※ 他のインクルサイトをご利用の場合は、同じメールアドレスで登録されている全てのインクルサイトのパスワードが変更されますのでご注意ください。<br>
                                    ※ パスワード発行メールをお送りするのに多少お時間がかかる場合がございます。<br>
                                    ※ パスワード発行メールが届かない場合、下記の理由が考えられます。

                                    <div class="my-3">
                                        <ul>
                                            <li>迷惑フォルダに入っている→ 迷惑フォルダをご確認ください</li>
                                            <li>アカウント登録されていないメールアドレスを入力した→登録したメールアドレスをご確認ください</li>
                                        </ul>
                                    </div>

                                    ご登録中のメールアドレスが不明、または登録状況が分からない場合は、お手数ですが下記までお問い合わせいただきますようお願いいたします。
                                    <div class="mt-3">
                                        <a href="/contact">お問い合わせ</a>
                                    </div>
                                </small>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

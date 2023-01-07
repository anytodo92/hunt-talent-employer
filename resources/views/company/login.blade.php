@extends('layouts.app')

@section('title', '求人企業様ログイン')

@section('content')

    <div id="mainContent">
        <div class="row">
            <div class="masonry-item col-md-2 d-none d-lg-block d-xl-block d-md-block col-12">&nbsp;</div>
            <div class="col-md-8 col-12">
                <div class="card">
                    <div class="card-header">
                        求人企業様：inCul総合ログイン
                    </div>
                    <div class="card-body">
                        <div class="masonry-item">
                            @if(session('message'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('message') }}
                                </div>
                            @elseif(session('error_message'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('error_message') }}
                                </div>
                            @endif
                            <form method="POST" action="/login">
                                @csrf
                                <input type="hidden" name="login_type" value="company">
                                <div class="form-group row">
                                    <label for="email" class="col-md-3 col-form-label">メールアドレス</label>
                                    <div class="col-md-9">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autofocus>
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password" class="col-md-3 col-form-label">パスワード</label>
                                    <div class="col-md-9">
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-3">&nbsp;</div>
                                    <div class="col-sm-9"><a href="{{ route('password.request') }}">パスワードを忘れた方はこちら</a>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12 text-right">
                                        <button type="submit" class="btn btn-primary">ログイン</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="masonry-item col-md-2 d-none d-lg-block d-xl-block d-md-block col-12">&nbsp;</div>
    </div>

@endsection
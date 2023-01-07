<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="@yield('meta_keywords', '人材紹介会社,求人募集,中途採用,SES,業務委託,フリーランスエージェント,プラットフォーム')">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ url('style.css') }}" rel="stylesheet">
    @yield('css')
</head>
<body class="app">
    @include('partials.loader')
    <div>
        <div class="page-container" style="padding-left: 0;">
            <div class="header navbar" style="width:100%;">
                <div class="header-container">
                    @hasSection('header')
                        @yield('header')
                    @else
                        <ul class="nav-left">
                            <div class="logo">
                                <a href="/">
                                    <img src="/assets/static/images/logo.png" alt="{{ config('app.name') }}">
                                </a>
                            </div>
                        </ul>
                    @endif
                </div>
            </div>
            <main id="app" class="main-content bgc-grey-100">
                @yield('content')
            </main>
        </div>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/loader.js') }}"></script>
    <script type="text/javascript" src="{{ url('/vendor.js') }}"></script>
    <script type="text/javascript" src="{{ url('/bundle.js') }}"></script>
    @yield('js')
</body>
</html>

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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.25/datatables.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/4.0.0/css/fixedColumns.dataTables.min.css"/>

    @yield('css')
</head>
<body class="app is-collapsed">
@include('partials.loader')
<div>
    <div class="sidebar">
        <div class="sidebar-inner">
            <div class="sidebar-logo">
                <div class="peers ai-c fxw-nw">
                    <div class="peer peer-greed">
                        <a class="sidebar-link td-n" href="{{ route('company.home') }}">
                            <div class="peers ai-c fxw-nw" style="justify-content: center;">
                                <div class="peer">
                                    <div class="logo"><img src="/assets/static/images/logo.png" alt=""></div>
                                </div>
                                <!--
                                <div class="peer peer-greed">
                                    <h5 class="lh-1 mB-0 logo-text">inCulエージェント</h5>
                                </div>
                                -->
                            </div>
                        </a>
                    </div>
                    <div class="peer">
                        <div class="mobile-toggle sidebar-toggle"><a href="" class="td-n"><i class="ti-arrow-circle-left"></i></a></div>
                    </div>
                </div>
            </div>
            <ul class="sidebar-menu scrollable pos-r ps">
                <li class="nav-item mT-30 active"><a class="sidebar-link" href="{{ route('company.home') }}"><span class="icon-holder"><i class="c-blue-500 ti-home"></i> </span><span class="title">inCulエージェントTOP</span></a></li>
                <li class="nav-item dropdown">
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <span class="icon-holder"><i class="c-brown-500 ti-briefcase"></i> </span>
                        <span class="title">求人管理</span> <span class="arrow"><i class="ti-angle-right"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="sidebar-link" href="{{ route('company.job_list') }}">求人一覧</a></li>
                        <li><a class="sidebar-link" href="{{ route('company.job_add', ['type' => 'J']) }}">新規人材紹介求人作成</a></li>
                        <li><a class="sidebar-link" href="{{ route('company.job_add', ['type' => 'G']) }}">新規業務委託案件作成</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="sidebar-link" href="{{ route('company.recruit.index') }}"><span class="icon-holder"><i class="c-blue-500 ti-share"></i> </span><span class="title">人材紹介ー選考</span></a></li>
                <li class="nav-item"><a class="sidebar-link" href="{{ route('company.outsource.index') }}"><span class="icon-holder"><i class="c-deep-orange-500 ti-share-alt"></i> </span><span class="title">業務委託ー選考</span></a></li>
                <li class="nav-item">
                    <a class="sidebar-link" href="{{ route('company.calendar') }}">
                        <div class="float-right">
                            @if($calendarCount)<span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-10 pY-10 bgc-red-50 c-red-500 mT-10">{{ $calendarCount }}</span>@endif
                        </div>
                        <span class="icon-holder"><i class="c-deep-purple-500 ti-calendar"></i> </span><span class="title">カレンダー</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="sidebar-link" href="{{ route('company.qa_list') }}">
                        <div class="float-right">
                            @if($qaCount)<span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-10 pY-10 bgc-red-50 c-red-500 mT-10">{{ $qaCount }}</span>@endif
                        </div>
                        <span class="icon-holder"><i class="c-indigo-500 ti-pencil-alt"></i> </span><span class="title">求人Q＆A</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="page-container">
        <div class="header navbar">
            <div class="header-container">
                <ul class="nav-left">
                    <li><a id="sidebar-toggle" class="sidebar-toggle" href="javascript:void(0);"><i class="ti-menu"></i></a></li>
                </ul>
                <ul class="nav-right">
                    <li>
                        <a href="{{ route('company.home') }}"><u>総合管理TOPへ</u></a>
                    </li>
                    <li class="notifications dropdown">@if($unreadCount)<span class="counter bgc-red">{{ $unreadCount }}</span>@endif <a href="" class="dropdown-toggle no-after" data-toggle="dropdown"><i class="ti-comment"></i></a>
                        <ul class="dropdown-menu">
                            <li class="pX-20 pY-15 bdB"><span class="fsz-sm fw-600 c-grey-900">チャット</span></li>
                            <li>
                                <ul class="ovY-a pos-r scrollable lis-n p-0 m-0 fsz-sm ps">
                                    <?php foreach($timelines as $timeline): ?>
                                    <li>
                                        <a href="{{ route('company.chat_detail', ['id' => $timeline->id]) }}" class="peers fxw-nw td-n p-20 bdB c-grey-800 cH-blue bgcH-grey-100">
                                            <div class="peer mR-15">
                                                <i class="ti-comment"></i>

                                            </div>
                                            <div class="peer peer-greed">
                                                <span>
                                                    @if($timeline->message_sender)
                                                    <span class="fw-500">
                                                        {{ @$timeline->messageSender->name }}
                                                    </span>
                                                    @else
                                                    <span class="fw-500">
                                                        運営事務局
                                                    </span>
                                                    @endif
                                                    <span class="c-grey-600" style="float: right">
                                                        {{ $timeline->offer_type_name }}
                                                        <span style="margin-left: 12px">{{ $timeline->ago }}</span>
                                                    </span>
                                                </span>
                                                <p class="m-1">
                                                    <span class="fw-500">{{ mb_strimwidth($timeline->message_detail, 0, 74, "…") }}</span>
                                                </p>
                                            </div>
                                            <div class="peer mL-35 read_icon @if($timeline->read_flg == 1) unread @endif">
                                                ●
                                            </div>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <li class="pX-20 pY-15 ta-c bdT" style="display: none"><span><a href="" class="c-grey-600 cH-blue fsz-sm td-n">チャットボックスへ <i class="ti-angle-right fsz-xs mL-10"></i></a></span></li>
                        </ul>
                    </li>
                    <li class="notifications dropdown">@if($undoCount)<span class="counter bgc-red">{{ $undoCount }}</span>@endif <a href="" class="dropdown-toggle no-after" data-toggle="dropdown"><i class="ti-check-box"></i></a>
                        <ul class="dropdown-menu">
                            <li class="pX-20 pY-15 bdB"><span class="fsz-sm fw-600 c-grey-900">Todo</span></li>
                            <li>
                                <ul class="ovY-a pos-r scrollable lis-n p-0 m-0 fsz-sm ps">
                                    <?php foreach($todos as $todo): ?>
                                    <li>
                                        <a href="{{ route('company.todo_detail', ['id' => $todo->id]) }}" class="peers fxw-nw td-n p-20 bdB c-grey-800 cH-blue bgcH-grey-100">
                                            <div class="peer mR-15">
                                                <i class="ti-pencil-alt"></i>

                                            </div>
                                            <div class="peer peer-greed">
                                                <p class="m-1">
                                                    <span class="text-dark" >{{ $todo->todo_content }}</span>
                                                </p>
                                                <span>
                                                    <span class="c-grey-600" style="float: left">
                                                        {{ $todo->ago }}
                                                        <span style="margin-left: 12px">{{ $todo->offer_type_name }}</span>
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="peer mL-25 read_icon @if($todo->read_flg == 1) unread @endif">
                                                ●
                                            </div>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <li class="pX-20 pY-15 ta-c bdT" style="display: none"><span><a href="" class="c-grey-600 cH-blue fsz-sm td-n">Todoへ <i class="ti-angle-right fsz-xs mL-10"></i></a></span></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="" class="dropdown-toggle no-after peers fxw-nw ai-c lh-1" data-toggle="dropdown">
                            <!-- <div class="peer mR-10"><img class="w-2r bdrs-50p" src="{{ $user->company_user->company->logo_url }}" alt=""></div> -->
                            <!-- <div class="peer mR-10"><img class="w-3r bdrs-40p" src="/assets/static/images/404.png" alt=""></div> -->
                            <div class="peer mR-10"><img class="w-3r bdrs-40p" src="{{ $user->company_user->company->full_logo }}" alt=""></div>
                            <div class="peer"><span class="fsz-sm c-grey-900">{{ $user->company_user->company->name }}／{{ $user->company_user->name }}</span></div>
                            <i class="ti-angle-right fsz-xs mL-10"></i>
                        </a>
                        <ul class="dropdown-menu fsz-sm">
                            <li><a href="" class="d-b td-n pY-5 bgcH-grey-100 c-grey-700"><i class="ti-user mR-10"></i> <span>アカウント設定</span></a></li>
                            <li><a href="" class="d-b td-n pY-5 bgcH-grey-100 c-grey-700"><i class="ti-notepad mR-10"></i> <span>よくある質問</span></a></li>
                            <li><a href="" class="d-b td-n pY-5 bgcH-grey-100 c-grey-700"><i class="ti-email mR-10"></i> <span>お問い合わせ</span></a></li>
                            <li><a href="" class="d-b td-n pY-5 bgcH-grey-100 c-grey-700"><i class="ti-settings mR-10"></i> <span>サポート</span></a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="/logout" class="d-b td-n pY-5 bgcH-grey-100 c-grey-700"><i class="ti-power-off mR-10"></i> <span>ログアウト</span></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <main class="main-content bgc-grey-100">
            @yield('breadcrumbs')
            @yield('content')
        </main>
        <footer class="footer bdT ta-c p-30 lh-0 fsz-sm c-grey-600">
            <span>Copyright © inCul co.,ltd ALL rights reserved.</span>
        </footer>
    </div>
</div>
<!-- Scripts -->
<script src="{{ asset('js/loader.js') }}"></script>
<script type="text/javascript" src="{{ url('/vendor.js') }}"></script>
<script type="text/javascript" src="{{ url('/bundle.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/4.0.0/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="{{ url('js/main.js') }}"></script>

@yield('js')
</body>
</html>

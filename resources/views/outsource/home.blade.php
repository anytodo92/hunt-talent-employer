@extends('layouts.outsource')

@section('title', 'inCulエージェント管理画面TOP')

@section('breadcrumbs')

    <small>
        <a href="{{ route('outsource.home') }}">inCulエージェント管理画面TOP</a>
    </small>

@endsection

@section('content')

    <div id="mainContent">
        <h3 class="c-grey-900 mT-20 mB-30">
            <!--<img src="/assets/static/images/logo.png">inCulエージェント管理画面TOP-->
            inCulエージェント管理画面TOP
        </h3>
        <h5 class="c-grey-900 mT-30 mB-10"><i class="ti-calendar mr-2"></i>本日の予定</h5>
        <div class="row gap-20">
            <?php foreach($calendars as $calendar): ?>
                    <div class="col-md-3">
                        <a href="{{ route('company.calendar_detail', ['id' => $calendar->id]) }}" class="c-grey-800">
                            <div class="peers bd bgc-white p-20">
                                <div class="peer peer-greed">
                                    <strong>{{ $calendar->interview_candidates_date->format('m月d日') }} {{ substr($calendar->interview_candidates_from, 0, 5) }}～{{ substr($calendar->interview_candidates_to, 0, 5) }}</strong><br>
                                    <!--<strong>{{ $calendar->interview_candidates_name }}</strong><br>-->
                                    <small>{{ $calendar->interview_content }}</small><br>
                                </div>
                                <div class="peer">
                                    <i class="ti-angle-right fsz-xs mL-10"></i>
                                </div>
                            </div>
                        </a>
                    </div>
            <?php endforeach; ?>
        </div>
        <!--
        <h5 class="c-grey-900 mT-30 mB-10"><i class="ti-bar-chart mr-2"></i>企業全体の求人統計データ<small>（人材紹介）</small></h5>
        <div class="row gap-20">
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
        </div>
        <div class="row gap-20">
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
        </div>
        <h5 class="c-grey-900 mT-30 mB-10"><i class="ti-bar-chart mr-2"></i>企業全体の求人統計データ<small>（業務委託）</small></h5>
        <div class="row gap-20">
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
        </div>
        <div class="row gap-20">
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class=" bd bgc-white p-20">
                    今月の採用(入社)数ランキング
                    <div class="text-right">○位</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="offset-md-4 col-md-4 pt-4 pb-5">
                <a href="" class="btn btn-outline-secondary btn-block">求人統計データへ</a>
            </div>
        </div>
        -->
        <div class="row gap-20">
            <div class="col-md-6">
                <h5 class="c-grey-900 mT-30 mB-10">
                    <i class="ti-check-box mr-2"></i>TODO
                    @if($undoCount)<span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{ $undoCount }}</span>@endif
                </h5>
                <?php foreach($todos as $todo): ?>
                        <div class="card mb-2">
                            <a href="{{ route('company.todo_detail', ['id' => $todo->id]) }}" class="c-grey-800">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-1">
                                            <i class="ti-pencil-alt"></i>
                                        </div>
                                        <div class="col-8">
                                            {{ mb_strimwidth($todo->todo_content, 0, 74, "…") }}<br>
                                            <small>{{ $todo->ago }}　{{ $todo->todo_type_name }}</small>
                                        </div>
                                        <div class="col">
                                            <div class="peer mL-25 read_icon @if($todo->read_flg == 1) unread @endif">
                                                ●
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-6">
                <h5 class="c-grey-900 mT-30 mB-10">
                    <i class="ti-comment mr-2"></i>チャット
                    @if($unreadCount)<span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{ $unreadCount }}</span>@endif
                </h5>
                <?php foreach($timelines as $timeline): ?>
                        <div class="card mb-2">
                            <a href="{{ route('company.chat_detail', ['id' => $timeline->id]) }}" class="c-grey-800">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-1">
                                            <i class="ti-comment"></i>
                                        </div>
                                        <div class="col-8">
                                            @if($timeline->message_sender)
                                                {{ @$timeline->messageSender->name }}
                                            @else
                                                運営事務局
                                            @endif
                                            {{ mb_strimwidth($timeline->message_detail, 0, 74, "…") }}<br>
                                            <small>{{ $timeline->ago }}　{{ $timeline->timeline_type_name }}</small>
                                        </div>
                                        <div class="col">
                                            <div class="peer mL-25 read_icon @if($timeline->read_flg == 1) unread @endif">
                                                ●
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <!--
        <h5 class="c-grey-900 mT-30 mB-10"><i class="ti-announcement mr-2"></i>運営事務局からのお知らせ</h5>
        <div class="row gap-20">
            <div class="col-md-12">
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                2021.4.20
                            </div>
                            <div class="col-md-10">
                                タイトルタイトルタイトルタイトルタイトルタイトル<br>
                                本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                2021.4.20
                            </div>
                            <div class="col-md-10">
                                タイトルタイトルタイトルタイトルタイトルタイトル<br>
                                本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文本文
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="offset-md-4 col-md-4 pt-4 pb-5">
                <a href="" class="btn btn-outline-secondary btn-block">お知らせへ</a>
            </div>
        </div>
        -->
    </div>

@endsection

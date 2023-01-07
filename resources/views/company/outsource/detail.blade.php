@extends('layouts.company')

@section('title', 'inCulエージェント 業務委託案件の参画候補者詳細（選考詳細）')

@section('breadcrumbs')
    <small>
        <a href="{{ route('company.home') }}">inCulエージェント 管理画面TOP</a>
    </small>
@endsection

@section('css')
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
<style>
.modal-footer {
    justify-content: center !important;
}
</style>
@endsection

@section('content')

    <div id="app" class="row mT-20">
        <div class="col-md-6">
            <div class="card mb-2">
                <div class="card-body ta-c">
                    <a href="#" class="seeker_name black">{{ $jobSeeker->initial }}</a> ／ <a href="{{ route('company.job_show', ['type' => 'G', 'id' => $offerInfo->id]) }}" class="black">{{ $offerInfo->job_title }}</a>
                </div>
            </div>

            <next-operation ref="nextoperation"
                :selection-flow="'{{ $selectionFlow }}'"
                :server-time="'{{ $now }}'"
            ></next-operation>

            <breadcrumb ref="breadcrumb"
                :selection-flow="'{{ $selectionFlow }}'"
            ></breadcrumb>

            <div class="card mb-2 card__tabs">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                          <button class="nav-link active" id="seeker-tab" data-bs-toggle="tab" data-bs-target="#seeker" type="button" role="tab" aria-controls="seeker" aria-selected="true">候補者情報</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link pT-15" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab" aria-controls="job" aria-selected="false">
                            <p>{{ g_enum('category_2', $offerInfo->occupation_category_1)[$offerInfo->occupation_category_2] }}</p>
                            <p class="fs-14">{{ $second_industry }}</p>
                          </button>
                        </li>
                        <div class="shortcut"><a href="/company/job_show/G/{{ $offerInfo->id }}" class="c-grey-icon">求人情報 ></a></div>
                      </ul>
                      <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active seeker-tab" id="seeker" role="tabpanel" aria-labelledby="seeker-tab">
                            <p class="fee-text">
                                紹介料： 無料<br>
                                採用事務手数料： 無料<br>
                                <span style="color:red">
                                    ※エージェント及びフリーランス等との直接契約はできません。<br>
                                    必ずinCulエージェント上での契約をお願いします。<br>
                                    詳しくは<a href="#">こちら</a>をご覧ください。
                                </span>
                            </p>
                            <div class="profile">
                                <figure class="figure">
                                    <img class="img" src="{{ g_grayLogo($jobSeeker->sex) }}" onerror="onImgErr(this)" alt="">
                                </figure>
                                <div class="name-group">
                                    <p class="label"><small>提案単価</small> <a href="#"><big>{{ number_format($outsourceJobSeekerApplyMgt->proposal_unit_price) }}</big> <small>円/月</small></a></p>
                                    <p class="last-name">{{ g_enum('sex', $jobSeeker->sex) }}</p>
                                </div>
                            </div>
                            <div class="attachment-group">
                                <p class="text bold">職務経歴書・スキルシート・その他</p>
                                @foreach ($attachments as $record)
                                <div class="attachment">
                                    <span class="icon-holder"><i class="c-grey-icon ti-file"></i></span>
                                    <p class="file-name">
                                        {{ $record->attachment_name }}
                                        <span class="c-grey-icon file-type">{{ substr($record->attachment, -3) }}</span>
                                        <span class="c-grey-icon file-date">{{ date('Y/n/j H:i', strtotime($record->upload_datetime)) }}</span>
                                    </p>
                                    <a href="{{ url('/storage/job_seeker_attachment/').'/'.$record->attachment }}" class="link fRight" download="{{ $record->attachment_name }}">
                                        <i class="link-icon ti-download"></i>
                                    </a>
                                    <a href="{{ url('/storage/job_seeker_attachment/').'/'.$record->attachment }}" class="link mL-10" target="_blank">
                                        <i class="link-icon ti-new-window"></i>
                                    </a>
                                </div>
                                @endforeach

                                @if (count($attachments) == 0)
                                <p>表示するデータがありません。</p>
                                @endif
                            </div>
                            <div class="profile-detail-group carousel-area">
                                <p class="text bold">提案内容</p>
                                <div class="text-line">
                                    <p class="line-content">{!! nl2br($jobSeeker->recommendation_statement) !!}</p>
                                </div>
                                <!-- ＃＃＃＃スキル詳細コメント＃＃＃＃＃＃＃＃＃＃ -->
                                <p class="content">{{ $jobSeeker->skill_detail_comment }}</p>
                            </div>
                            <!--<a class="carousel" href="javascript:">閉じる</a>-->
                            <p class="text bold mT-20">業務委託/SES企業</p>
                            <p class="text">{{ $outsourceCompany->name }}</p>
                        </div>
                        <div class="tab-pane fade job-tab" id="job" role="tabpanel" aria-labelledby="job-tab">
                            <p class="text red">募集期間</p>
                            <p class="text red">{{ date('Y年n月j日', strtotime($offerInfo->recruit_period)) }}まで</p>
                            <p class="text">職種</p>
                            <p class="text">{{ g_enum('category_1', $offerInfo->occupation_category_1) }}＞{{ g_enum('category_2', $offerInfo->occupation_category_1)[$offerInfo->occupation_category_2] }}</p>
                            <p class="text">業界</p>
                            <p class="text">{{ $first_industry }}＞{{ $second_industry }}</p>
                            <p class="text">単価</p>
                            <p class="text">{{ number_format($offerInfo->unit_price_start) }}～{{ number_format($offerInfo->unit_price_end) }}円/{{ $offerInfo->unit_price==1 ? '月' : '時' }}</p>
                            <p class="text">稼働/週</p>
                            <p class="text">{{ g_enum('outsource_working_days_week', $offerInfo->estimated_working_days_week) }}</p>
                            <p class="text">在宅</p>
                            <p class="text">{{ g_enum('outsource_telework', $offerInfo->telework) }}</p>
                            <p class="text">最寄駅</p>
                            <p class="text">{{ isset($workPlaces[0]) ? $workPlaces[0]->nearest_station_line . '　' . $workPlaces[0]->nearest_station : '' }}</p>
                            <p class="text">募集人数</p>
                            <p class="text">{{ g_enum('recruiting_plan_count', $offerInfo->recruiting_plan_count) }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-6">
            <timeline ref="timeline"
            ></timeline>
        </div>
    </div>

@endsection

@section('js')
<script>
    let enumPrefectures = @json(g_enum('prefectures'));
    let enumUnitPrices = @json(g_enum('unit_price'));
    let enumOutsourceDocConfirmStatuses = @json(g_enum('outsource_doc_confirm_status'));
    let enumOutsourceAppScreeningStatuses = @json(g_enum('outsource_app_screening_status'));
    let enumOutsourceInterviewStatuses = @json(g_enum('outsource_interview_status'));
    let enumOutsourceContractStatuses = @json(g_enum('outsource_contract_status'));
    let enumOutsourceJoiningStatuses = @json(g_enum('outsource_joining_status'));
    let enumOutsourcePhases = @json(g_enum('outsource_phase'));
    let enumOutsourceEvaluations = @json(g_enum('outsource_evaluation'));
    let enumOutsourceSelectionFlows = @json(g_enum('outsource_selection_flow'));
    let enumOutsourceApplyMgtSelectionResultKeys = @json(g_enum('outsource_apply_mgt_selection_result_key'));
    let enumOutsourceUnseatedReasons = @json(g_enum('outsource_unseated_reason'));
    let enumOutsourceUnseatedReasonSubs = @json(g_enum('outsource_unseated_reason_sub'));

    let g_outsourceApplyMgt = @json($outsourceJobSeekerApplyMgt);
    let g_offerInfo = @json($offerInfo);
    let g_jobSeeker = @json($jobSeeker);
    let g_outsourceCompany = @json($outsourceCompany);
    let g_recruitingCompany = @json($recruitingCompany);
    let g_selectionResults = @json($selectionResults);
    let g_interviewSchedules = @json($interviewSchedules);
    let g_timelines = @json($timelines);
    let g_contractTerms = @json($contractTerms);
    let g_offerCompanyUser = @json($offerCompanyUser);
</script>
<script src="{{ url('js/company/outsource_detail.js') }}"></script>
<script type="text/javascript">

    $(".carousel-area").css('max-height', 1500);
    $(".seeker-tab .carousel").click(function() {
        let isCollapsed = $(this).attr('data-collapsed');
        let seekerTab = $(".carousel-area");

        if (isCollapsed != 0) {
            seekerTab.css('max-height', "170px");
            $(this).html('もっと見る');
            $(this).attr('data-collapsed', 0);
        } else {
            seekerTab.css('max-height', "600px");
            $(this).html('閉じる');
            $(this).attr('data-collapsed', 1);
        }
    });
    /*
    $('#inputChat').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            $('#btnChatSend').trigger("click");
        }
    });
    */
</script>

@endsection

@extends('layouts.company')

@section('title', 'inCulエージェント 人材紹介求人の候補者詳細（選考詳細）')

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
                    <a href="#" class="seeker_name black">{{ $jobSeeker->last_name . ' ' . $jobSeeker->first_name }}</a> ／ <a href="{{ route('company.job_show', ['type' => 'J', 'id' => $offerInfo->id]) }}" class="black">{{ $offerInfo->job_title }}</a>
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
                        <div class="shortcut"><a href="/company/job_show/J/{{ $offerInfo->id }}" class="c-grey-icon">求人情報 ></a></div>
                      </ul>
                      <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active seeker-tab" id="seeker" role="tabpanel" aria-labelledby="seeker-tab">
                            <p class="fee-text">
                                紹介料：{{ $offerInfo->success_reward_calculation_method == 1 ? '年収の ' . floatval($offerInfo->theory_annual_income) . ' ％' : '一律固定報酬 ' . $offerInfo->theory_annual_income_definition . ' 万円' }}<br>
                                採用事務手数料：上記紹介料の20%（上限15万円）
                            </p>
                            <div class="profile">
                                <figure class="figure">
                                    <img class="img" src="{{ g_grayLogo($jobSeeker->sex) }}" onerror="onImgErr(this)" alt="">
                                </figure>
                                <div class="name-group">
                                    <p class="first-name">{{ $jobSeeker->last_name_kana . ' ' . $jobSeeker->first_name_kana }}</p>
                                    <p class="label">{{ $jobSeeker->last_name . ' ' .$jobSeeker->first_name }}</p>
                                    <p class="last-name">{{ g_enum('sex', $jobSeeker->sex) }}</p>
                                </div>
                            </div>
                            <div class="attachment-group">
                                <p class="text bold">履歴書・職務経歴書・その他</p>
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
                                <p class="text bold">推薦状</p>
                                <div class="text-line">
                                    <p class="line-content">{!! nl2br($jobSeeker->recommendation) !!}</p>
                                </div>
                            </div>
                            <!--<a class="carousel" href="javascript:">閉じる</a>-->
                            <p class="text bold mT-20">人材紹介会社</p>
                            <p class="text">{{ $recruitCompany->name }}</p>
                        </div>
                        <div class="tab-pane fade job-tab" id="job" role="tabpanel" aria-labelledby="job-tab">
                            <p class="text red">募集期間</p>
                            <p class="text red">{{ date('Y年n月j日', strtotime($offerInfo->recruit_period)) }}まで</p>
                            <p class="text">職種</p>
                            <p class="text">{{ g_enum('category_1', $offerInfo->occupation_category_1) }}＞{{ g_enum('category_2', $offerInfo->occupation_category_1)[$offerInfo->occupation_category_2] }}</p>
                            <p class="text">業界</p>
                            <p class="text">{{ $first_industry }}＞{{ $second_industry }}</p>
                            <p class="text">年収</p>
                            <p class="text"><?php
                                switch ($offerInfo->salary_type) {
                                    case 1:
                                        echo round($offerInfo->yearly_pay_amount_from / 10000) . ' ~ ' . round($offerInfo->yearly_pay_amount_to / 10000) . '万円';
                                        break;
                                    case 2:
                                        echo round($offerInfo->monthly_salary_from / 10000) . ' ~ ' . round($offerInfo->monthly_salary_to / 10000) . '万円';
                                        break;
                                    case 3:
                                        echo $offerInfo->daily_salary_from . ' ~ ' . $offerInfo->daily_salary_to . '円';
                                        break;
                                    case 4:
                                        echo $offerInfo->hourly_wage_from . ' ~ ' . $offerInfo->hourly_wage_to . '円';
                                        break;
                                }
                            ?></p>
                            <p class="text">人数</p>
                            <p class="text">{{ g_enum('recruiting_plan_count', $offerInfo->recruiting_plan_count) }}</p>
                            <p class="text">勤務地</p>
                            <p class="text">{{ isset($workPlaces[0]) ? g_enum('prefectures', $workPlaces[0]->prefecture).' '.$workPlaces[0]->address : '―' }}</p>
                            <p class="text">最寄駅</p>
                            <p class="text">{{ isset($workPlaces[0]) ? $workPlaces[0]->nearest_station_line . '　' . $workPlaces[0]->nearest_station : '' }}</p>
                            <p class="text">報酬額</p>
                            <p class="text">{{ $offerInfo->success_reward_calculation_method == 1 ? '年収の ' . floatval($offerInfo->theory_annual_income) . ' ％' : '一律固定報酬 ' . $offerInfo->theory_annual_income_definition . ' 万円' }}</p>
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
    let enumRecruitDocConfirmStatuses = @json(g_enum('recruit_doc_confirm_status'));
    let enumRecruitAppScreeningStatuses = @json(g_enum('recruit_app_screening_status'));
    let enumRecruitInterviewStatuses = @json(g_enum('recruit_interview_status'));
    let enumRecruitRecruitmentStatuses = @json(g_enum('recruit_recruitment_status'));
    let enumRecruitJoiningStatuses = @json(g_enum('recruit_joining_status'));
    let enumRecruitPhases = @json(g_enum('recruit_phase'));
    let enumRecruitEvaluations = @json(g_enum('recruit_evaluation'));
    let enumRecruitSelectionFlows = @json(g_enum('recruit_selection_flow'));
    let enumRecruitApplyMgtSelectionResultKeys = @json(g_enum('recruit_apply_mgt_selection_result_key'));
    let enumRecruitUnseatedReasons = @json(g_enum('recruit_unseated_reason'));
    let enumRecruitUnseatedReasonSubs = @json(g_enum('recruit_unseated_reason_sub'));

    let g_recruitApplyMgt = @json($recruitJobSeekerApplyMgt);
    let g_offerInfo = @json($offerInfo);
    let g_jobSeeker = @json($jobSeeker);
    let g_recruitCompany = @json($recruitCompany);
    let g_recruitingCompany = @json($recruitingCompany);
    let g_selectionResults = @json($selectionResults);
    let g_interviewSchedules = @json($interviewSchedules);
    let g_timelines = @json($timelines);
    let g_joiningConditionPresents = @json($joiningConditionPresents);
    let g_joinConditionAttachments = @json($joinConditionAttachments);
    let g_offerCompanyUser = @json($offerCompanyUser);
</script>
<script src="{{ url('js/company/recruit_detail.js') }}"></script>
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

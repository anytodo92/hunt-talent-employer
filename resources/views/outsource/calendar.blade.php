@extends('layouts.outsource')

@section('title', 'inCulエージェント 参画者の面談予定カレンダー（業務委託/SES）')

@section('breadcrumbs')
    <small>
        <a href="{{ route('outsource.home') }}">inCulエージェント 管理画面TOP</a>
    </small>
@endsection

@section('css')
<link href="{{ asset('libs/calendar/calendar.min.css') }}" rel="stylesheet">
<style>

  #calendar {
    margin: 0 auto;
  }

  .activeEvent {
    border: solid 3px #073763;
    background: #073763;
    font-weight: bold;
    padding: 0 5px;
  }

  .deactiveEvent {
    border: solid 3px #073763;
    background: none;
    color: #073763;
    font-weight: bold;
    padding: 0 5px;
  }

  td.fc-day.fc-past {
    background-color: #f00;
  }
  div.sent_area {
    opacity: 0.5;
    transition: 0.5s;
  }
  div.sent_area:hover {
    opacity: 1;
  }
</style>
@endsection

@section('content')

    <div id="app" class="row mT-20" style="max-width:none">
        <div class="col-md-3" style="padding: 0px;">
            <div class="card mb-2">
                <div class="card-body ta-c pY-10 calendar-ttl">
                    <b>参画候補者の選考・面談予定</b><br/>
                    <b>カレンダー</b>
                </div>
            </div>
            <div class="card mb-2">
                <div class="card-body ta-c pX-0">
                    <p class="mB-10">求人企業の選考（面談）日程一覧</p>
                    <i class="ti-layout-width-full bold" style="color: #073763; border: solid 3px #073763; background: #073763;"></i>
                    <b>確定している日程</b><br/><br/>
                    <!-- →　左サイドの「面接・面談日程一覧」は、確定した予定を入れる。 -->
                    <p id="interview_list" class="text">
                        @if (count($fixedSchedules) == 0)
                        表示する日程がありません。
                        @endif
                        @foreach ($fixedSchedules as $schedule)
                        <a style="color: blue;" href="/ses/apply/{{ $schedule->job_seeker_apply_mgt_id }}">{{ $schedule->interview_candidates_name }}さん</a>
                        {{ date('n/j', strtotime($schedule->interview_candidates_date)) }} {{ g_enum('week_days', date('w', strtotime($schedule->interview_candidates_date))) }} {{ substr($schedule->interview_candidates_from, 0, 5) }}〜{{ substr($schedule->interview_candidates_to, 0, 5) }}<br>
                        @endforeach
                    </p>
                </div>
            </div>
            <div class="card mb-2">
                <div class="card-body ta-c pX-0">
                    <i class="ti-layout-width-full bold" style="color: white; border: solid 3px #073763;"></i>
                    <b>現在調整中の日程</b><br/>（候補日程）<br/><br/>
                    <!-- →　左サイド「現在調整中の日程（候補日程）」には、面接調整中の候補日などを表示する。 -->
                    <p id="interview_others" class="text">
                        @if (count($pendingSchedules) == 0)
                        表示する日程がありません。
                        @endif
                        @foreach ($pendingSchedules as $schedule)
                        <a style="color: blue;" href="/ses/apply/{{ $schedule->job_seeker_apply_mgt_id }}">{{ $schedule->interview_candidates_name }}さん</a>
                        <span style="color: #073763;">{{ date('n/j', strtotime($schedule->interview_candidates_date)) }} {{ g_enum('week_days', date('w', strtotime($schedule->interview_candidates_date))) }} {{ substr($schedule->interview_candidates_from, 0, 5) }}〜{{ substr($schedule->interview_candidates_to, 0, 5) }}</span><br>
                        @endforeach
                    </p>
                </div>
            </div>

        </div>

        <div class="col-md-9">
            <div id='calendar'></div>
        </div>
    </div>

@endsection

@section('js')
<script src="{{ url('libs/calendar/calendar.min.js') }}"></script>
<script src="{{ url('libs/calendar/ja.js') }}"></script>
<script src="{{ url('libs/calendar/moment-with-locales.min.js') }}"></script>
<script type="text/javascript">
    var calendar = null;

    moment.locale('ja');

    $(document).ready(function() {
        var calendarEl = document.getElementById('calendar');

        calendar = new FullCalendar.Calendar(calendarEl, {
            //→　「月」「日」でも切り替え可
            headerToolbar: {
                left: 'dayGridMonth,timeGridWeek,timeGridDay',
                center: 'title',
                right: 'prev,next today'
            },
            initialDate: '{{ $now }}',
            //→　デフォルトはテンプレのweekで表示
            initialView: 'timeGridWeek',
            locale: 'ja',
            height: 'auto',
            navLinks: true, // can click day/week names to navigate views
            //→　設定できない日程を背景グレーで表示して日程を登録できないようにする。
            businessHours: false, // display business hours
            //→　本日の日付がわかるように色分ける
            nowIndicator: false,
            // →　候補日はドラッグで選択していく。３０分単位で自由に設定できる。
            slotDuration: '00:10:00',
            slotMinTime: '00:00:00',
            slotMaxTime: '24:00:00',
            slotLabelInterval: '01:00:00',
            allDaySlot: false,
            selectable: false,
            selectMirror: false,
            stickyHeaderDates: true,
            validRange: {   // Limits which dates the user can navigate to and where events can go.
                start: '{{ date("Y-m-01", strtotime("-2 month")) }}',
            },
            eventClick: function(arg) {
                if (confirm(arg.event.title+'の選考詳細ページへ移行しますか?')) {
                    window.location.href = "/ses/apply/" + arg.event.groupId;
                }
            },
            editable: false,
            dayMaxEvents: false, // allow "more" link when too many events
            events: []
        });

        let formated = null;
        let interview_candidates_from = null;
        let interview_candidates_to = null;

<?php for ($i=0; $i<count($fixedSchedules); $i++) { ?>
        formated = moment(new Date('{{ $fixedSchedules[$i]->interview_candidates_date }}')).format('YYYY/MM/DD');
        interview_candidates_from = moment(new Date(formated + ' ' + '{{ $fixedSchedules[$i]->interview_candidates_from }}')).format('YYYY-MM-DDTHH:mm:ss');
        interview_candidates_to  = moment(new Date(formated + ' ' + '{{ $fixedSchedules[$i]->interview_candidates_to }}')).format('YYYY-MM-DDTHH:mm:ss');
        calendar.addEvent({
            title: '{{ $fixedSchedules[$i]->interview_candidates_name."さん" }}',
            start: interview_candidates_from,
            end: interview_candidates_to,
            id: {{ $fixedSchedules[$i]->id }},
            groupId: '{{ $fixedSchedules[$i]->job_seeker_apply_mgt_id }}',
            className: 'activeEvent',
            textColor: 'white',
            editable: false,
        });
<?php } ?>

<?php for ($i=0; $i<count($pendingSchedules); $i++) { ?>
        formated = moment(new Date('{{ $pendingSchedules[$i]->interview_candidates_date }}')).format('YYYY/MM/DD');
        interview_candidates_from = moment(new Date(formated + ' ' + '{{ $pendingSchedules[$i]->interview_candidates_from }}')).format('YYYY-MM-DDTHH:mm:ss');
        interview_candidates_to  = moment(new Date(formated + ' ' + '{{ $pendingSchedules[$i]->interview_candidates_to }}')).format('YYYY-MM-DDTHH:mm:ss');
        calendar.addEvent({
            title: '{{ $pendingSchedules[$i]->interview_candidates_name."さん" }}',
            start: interview_candidates_from,
            end: interview_candidates_to,
            id: {{ $pendingSchedules[$i]->id }},
            groupId: '{{ $pendingSchedules[$i]->job_seeker_apply_mgt_id }}',
            className: 'deactiveEvent',
            textColor: '#073763',
            editable: false,
        });
<?php } ?>

        calendar.setOption('height', Math.max($(".footer").offset().top-$('#app').offset().top-20, $('#app').height()));
        if ($(document).width() < 768)
        {
            calendar.setOption('headerToolbar', {
                left: 'title',
                center: 'dayGridMonth,timeGridWeek,timeGridDay',
                right: 'prev,next today'
            });
        }
        calendar.render();
    });

</script>

@endsection

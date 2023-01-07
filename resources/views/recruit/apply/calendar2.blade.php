@extends('layouts.recruit')

@section('title', 'inCulエージェント 日程調整ページ（人材紹介）')

@section('breadcrumbs')
    <small>
        <a href="{{ route('recruit.home') }}">inCulエージェント 管理画面TOP</a>
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
    border: solid 3px #ccc;
    background: #ccc;
    color: #073763;
    padding: 0 5px;
  }

  td.fc-day.fc-past {
    background-color: #f00;
  }
  div.sent_area {
    position: fixed;
    background: red;
    height: 85px;
    opacity: 0.4;
    transition: 0.5s;
    bottom: 0;
    z-index: 1001;
  }
  button.btn-light {
    position: fixed;
    color: #fff;
    background-color: #073763;
    border-color: #073763;
    font-size: 16px !important;
    margin: 0 auto;
    left: calc(50% - 110px);
    bottom: 20px;
    width: 220px;
    height: 45px;
    z-index: 1002;
  }
</style>
@endsection

@section('content')

    <div id="app" class="row mT-20" style="max-width:none">
        <div class="col-md-3" style="padding: 0px;">
            <div class="card mb-2">
                <div class="card-body ta-c pY-10 calendar-ttl">
                    <b>{{ g_enum('recruit_interview_flow', $recruitJobSeekerApplyMgt->last_selection_flow_number) }}日程調整</b><br/>
                    <b>{{ $jobSeeker->last_name . ' ' . $jobSeeker->first_name }} ／ {{ $recruitCompany->name }}</b><br/>
                    <a class="link" style="cursor:pointer; font-size: 90%;" href="{{ '/agent/apply/'.$recruitJobSeekerApplyMgt->id }}">▶選考詳細に戻る</a>
                </div>
            </div>
            <div class="card mb-2">
                <div class="card-body ta-c">
                    <i class="ti-calendar bold"></i>
                    <b>{{ $offerInfo->company->name }}様へ希望日程を提示する</b><br/><br/>
                    選考の希望日程を右の予定表よりドラッグして選択し、送信してください。<br/>
                    （スムーズな選考のためにも可能な限り多くの候補日程をご提示ください）
                </div>
                <div class="card-body ta-c pT-0">
                    <div class="text-left">
                        <span style="color:red">
                            ※候補日時は時間帯でまとめて選択せず、所要時間に合わせて区切って設定してください。<br/>
                            （所要時間が60分であれば、候補日程を全て60分単位で区切って設定してください。先方は１区切り単位でしか選択できませんのでご注意ください）
                        </span><br/>
                    </div>
                    <i class="ti-layout-width-full" style="color: #073763; background: #073763;"></i>
                    候補日時
                    <p id="interview_list" class="text" style="color: rgb(43, 124, 191);">
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

    <div class="col-md-12 sent_area">
        <div class="card-body ta-c">
        </div>
    </div>
    <button type="button" class="btn cur-p btn-light" id="btn_sendSchedule">選択した日程を送信</button>

<script src="{{ url('libs/calendar/calendar.min.js') }}"></script>
<script src="{{ url('libs/calendar/ja.js') }}"></script>
<script src="{{ url('libs/calendar/moment-with-locales.min.js') }}"></script>
<script type="text/javascript">
    var calendar = null;
    var schedules = [];

    moment.locale('ja');

    //→　左サイド　予定表に入れた候補日程を一覧で即時表示する
    function refreshScheduleList() {
        let html = '';
        let events = calendar.getEvents();
        schedules = [];
        $.each(events, function(index, event) {
            if (event.groupId == '') {
                let element = new Object();
                element.id = event.id;
                element.start = moment(event.start).format('YYYY-MM-DD HH:mm:ss');
                element.end = moment(event.end).format('YYYY-MM-DD HH:mm:ss');
                element.status = event.groupId;
                schedules.push(element);
            }
        });
        schedules.sort(compareEvent);
        function compareEvent(a, b) {
            if (a.start < b.start) {
                return -1;
            }
            else if (a.start > b.start) {
                return 1;
            }
            else {
                if (a.end < b.end) {
                    return -1;
                }
                if (a.end > b.end) {
                    return 1;
                }
            }
            return 0;
        }
        $.each(schedules, function(index, event) {
            html += moment(event.start).format('M/D ddd HH:mm') + '〜' + moment(event.end).format('HH:mm');
            html += "<br/>";
        });

        if (html.trim() == '') {
            html = '表示する日程がありません。';
        }
        $("p#interview_list").html(html);
    }

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
            nowIndicator: true,
            // →　候補日はドラッグで選択していく。３０分単位で自由に設定できる。
            slotDuration: '00:10:00',
            slotMinTime: '00:00:00',
            slotMaxTime: '24:00:00',
            slotLabelInterval: '01:00:00',
            allDaySlot: false,
            selectable: true,
            selectMirror: true,
            stickyHeaderDates: true,
            selectAllow: function(select) {
              return moment().diff(select.start) <= 0
            },
            // →　左サイドの「候補日時」「NG日程」をクリックして選択後に予定表で日時を設定する。 (この仕様は削除済み2021-09-17。　NG日程は現在日時によって可変なので。)
            // →　設定できない日程は、現在の日時によって変動。現在の日時から「3時間以内」「過去」は登録不可。
            selectConstraint: {
                start: '{{ date("Y-m-d\TH:i:s", strtotime("+3 hour")) }}',
            },
            eventConstraint: {  // disable drag&drop
                start: '{{ date("Y-m-d\TH:i:s", strtotime("+3 hour")) }}',
            },
            validRange: {   // Limits which dates the user can navigate to and where events can go.
                start: '{{ date("Y-m-01", strtotime("-2 month")) }}',
            },
            select: function(arg) {
                var title = '{{ $jobSeeker->last_name.' '.$jobSeeker->first_name }}'; // prompt('Event Title:');
                if (title) {
                    calendar.addEvent({
                        title: title,
                        start: arg.start,
                        end: arg.end,
                        id: '',
                        groupId: '',
                        className: 'activeEvent',
                        textColor: 'white',
                        editable: true,
                    });
                    refreshScheduleList();
                }
                calendar.unselect();
            },
            eventClick: function(arg) {
                if (arg.event.id == '') {
                    if (confirm('求人企業様へ提示した日程を削除しますか?')) {
                        arg.event.remove();
                        refreshScheduleList();
                    }
                }
            },
            eventResize: function(arg) {
                refreshScheduleList();
            },
            eventDrop: function(arg) {
                refreshScheduleList();
            },
            editable: true,
            dayMaxEvents: false, // allow "more" link when too many events
            events: []
        });

        let formated = null;
        let interview_candidates_from = null;
        let interview_candidates_to = null;

<?php for ($i=0; $i<count($interviewSchedules); $i++) { ?>
        formated = moment(new Date('{{ $interviewSchedules[$i]->interview_candidates_date }}')).format('YYYY/MM/DD');
        interview_candidates_from = moment(new Date(formated + ' ' + '{{ $interviewSchedules[$i]->interview_candidates_from }}')).format('YYYY-MM-DDTHH:mm:ss');
        interview_candidates_to  = moment(new Date(formated + ' ' + '{{ $interviewSchedules[$i]->interview_candidates_to }}')).format('YYYY-MM-DDTHH:mm:ss');
        calendar.addEvent({
            title: '{{ $interviewSchedules[$i]->interview_candidates_name."さん" }}',
            start: interview_candidates_from,
            end: interview_candidates_to,
            id: {{ $interviewSchedules[$i]->id }},
            groupId: '',
            className: '{{ $interviewSchedules[$i]->interview_date_type == 4 ? "deactiveEvent" : "activeEvent" }}',
            textColor: 'white',
            editable: true,
        });
<?php } ?>

        calendar.addEvent({
          start: '{{ date("Y-m-01", strtotime("-2 month")) }}',
          end: '{{ date("Y-m-d\TH:i:s", strtotime("+3 hour")) }}',
          id: 'unavailable',
          groupId: 'unavailable',
          overlap: false,
          display: 'background',
          color: '#ccc'
        });

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

        refreshScheduleList();

        // →　画面下部に半透明で送信ボタンの追随フッターを常時表示。予定を入れたら押下できるようにする。ボタンをクリックで確認ポップアップ表示。「OK」押下で送信。
        $("#btn_sendSchedule").click(function() {
            if (schedules.length == 0) {
                alert("求人企業様へ提示する日程を右の予定表より選択してください。");
                return;
            }

            $.ajax({
                url: "{{ route('recruit.apply.send_interview_date') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}', 
                    mgt_id: {{ $recruitJobSeekerApplyMgt->id }},
                    schedules:schedules
                },
                dataType: 'JSON',
                success: function (response) {
                    if (response.success == true) {
                        window.location.href = "{{ '/agent/apply/'.$recruitJobSeekerApplyMgt->id }}";
                    }
                },
                error: function () {
                    console.log("ajax error");
                }
            }); 

        });

    });

</script>

@endsection

@extends('layouts.outsource')

@section('title', 'inCulエージェント 日程調整ページ（業務委託/SES）')

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
    border: solid 3px #ccc;
    background: #ccc;
    color: #073763;
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
                    <b>{{ g_enum('outsource_interview_flow', $outsourceJobSeekerApplyMgt->last_selection_flow_number) }}日程調整</b><br/>
                    <b>{{ $jobSeeker->initial }} ／ {{ $outsourceCompany->name }}</b><br/>
                    <a class="link" style="cursor:pointer; font-size: 90%;" href="{{ '/ses/apply/'.$outsourceJobSeekerApplyMgt->id }}">▶選考詳細に戻る</a>
                </div>
            </div>
            <div class="card mb-2">
                <div class="card-body ta-c">
                    <i class="ti-calendar bold"></i>
                    <b>{{ $offerInfo->company->name }}様からの候補日程</b><br/><br/>
                    提示された候補日程から選択してください。<br/>
                    （全てをNGにすると新たな候補日程を提示することができます）<br/><br/>
                    <p id="interview_list" class="text" style="color: rgb(43, 124, 191);">
                    </p>
                    <div class="card-body ta-c">
                        <button type="button" class="btn cur-p btn-outline-secondary" id="btn_clearSchedule">全てNGにして別の日程を提示する</button>
                    </div>
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
    var schedules = [];

    moment.locale('ja');

    //→　左サイド　予定表に入れた候補日程を一覧で即時表示する
    function refreshScheduleList() {
        let html = '';
        let events = calendar.getEvents();
        schedules = [];
        $.each(events, function(index, event) {
            if (event.groupId == '' || event.groupId == 'pending' || event.groupId == 'unconfirm' || event.groupId == 'rejected') {
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
            if (event.status == '' || event.status == 'pending') {
                html += "&nbsp;&nbsp;&nbsp;<a href='javascript:clickConfirmDate("+event.id+", true)' style='color:green'><b>〇</b></a>";
            }
            else {
                html += "&nbsp;&nbsp;&nbsp;<a style='color:grey'><b>✖</b></a>";
            }
            html += "<br/>";
        });

        if (html.trim() == '') {
            html = '表示する日程がありません。';
        }
        $("p#interview_list").html(html);
    }

    // →　「〇」をクリックで確認ポップアップ表示。「OK」押下で確定。
    function clickConfirmDate(id, check) {
        let selected = null;

        let events = calendar.getEvents();
        $.each(events, function(index, event) {
            if (event.id == id && (event.groupId == '' || event.groupId == 'pending')) {
                selected = event;
                return;
            }
        });
        if (selected == null) {
            return;
        }

        if (moment(selected.start).format('YYYY-MM-DD HH:mm:ss') < moment(new Date()).add(3, 'hours').format('YYYY-MM-DD HH:mm:ss')) {
            alert("３時間以内の候補日は確定できません。");
            return;
        }

        let msg = '選択した日程を確定しますか?\r\n';
        msg += moment(selected.start).format('M/D ddd HH:mm') + '〜' + moment(selected.end).format('HH:mm');
        if (confirm(msg)) {
            $.ajax({
                url: "{{ route('outsource.apply.send_confirm_interview_date') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}', 
                    mgt_id: {{ $outsourceJobSeekerApplyMgt->id }},
                    target_id: selected.id,
                    status: check ? 2 : 3,  // 2:確定した日(=◯) 3:確定しなかった日(=X)
                },
                dataType: 'JSON',
                success: function (response) {
                    if (check && response.success == true) {
                        window.location.href = "{{ '/ses/apply/'.$outsourceJobSeekerApplyMgt->id }}";
                    }
                    else {
                        selected.setProp('groupId', check ? '' : 'unconfirm');
                        refreshScheduleList()
                    }
                },
                error: function () {
                    console.log("ajax error");
                }
            }); 
        }
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
            selectable: false,
            selectMirror: false,
            stickyHeaderDates: true,
            validRange: {   // Limits which dates the user can navigate to and where events can go.
                start: '{{ date("Y-m-01", strtotime("-2 month")) }}',
            },
            eventClick: function(arg) {
                if (arg.event.groupId == '' || arg.event.groupId == 'pending') {
                    clickConfirmDate(arg.event.id, true);
                }
            },
            editable: false,
            dayMaxEvents: false, // allow "more" link when too many events
            events: []
        });

        let formated = null;
        let interview_candidates_from = null;
        let interview_candidates_to = null;
        let interview_date_type = null;

<?php for ($i=0; $i<count($interviewSchedules); $i++) { ?>
        formated = moment(new Date('{{ $interviewSchedules[$i]->interview_candidates_date }}')).format('YYYY/MM/DD');
        interview_candidates_from = moment(new Date(formated + ' ' + '{{ $interviewSchedules[$i]->interview_candidates_from }}')).format('YYYY-MM-DDTHH:mm:ss');
        interview_candidates_to  = moment(new Date(formated + ' ' + '{{ $interviewSchedules[$i]->interview_candidates_to }}')).format('YYYY-MM-DDTHH:mm:ss');
        interview_date_type = "{{ $interviewSchedules[$i]->interview_date_type == 1 ? 'pending' : ($interviewSchedules[$i]->interview_date_type == 3 ? 'unconfirm' : ($interviewSchedules[$i]->interview_date_type == 4 ? 'rejected' : '')) }}";
        calendar.addEvent({
            title: '{{ $interviewSchedules[$i]->interview_candidates_name."さん" }}',
            start: interview_candidates_from,
            end: interview_candidates_to,
            id: {{ $interviewSchedules[$i]->id }},
            groupId: interview_date_type,
            className: '{{ ($interviewSchedules[$i]->interview_date_type == 3 || $interviewSchedules[$i]->interview_date_type == 4) ? "deactiveEvent" : "activeEvent" }}',
            textColor: 'white',
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

        refreshScheduleList();

        // →　画面下部に半透明で送信ボタンの追随フッターを常時表示。予定を入れたら押下できるようにする。ボタンをクリックで確認ポップアップ表示。「OK」押下で送信。
        $("#btn_clearSchedule").click(function() {

            if (confirm('求人企業様から提示された日程を全てNGにして別の日程を提示しますか?')) {
                $.ajax({
                    url: "{{ route('outsource.apply.send_clear_interview_date') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', 
                        mgt_id: {{ $outsourceJobSeekerApplyMgt->id }},
                    },
                    dataType: 'JSON',
                    success: function (response) {
                        window.location.href = "{{ '/ses/calendar2?id='.$outsourceJobSeekerApplyMgt->id }}";
                    },
                    error: function () {
                        console.log("ajax error");
                    }
                }); 
            }
        });

    });

</script>

@endsection

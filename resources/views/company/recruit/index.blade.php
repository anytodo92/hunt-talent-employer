@extends('layouts.company')

@section('title', 'inCulエージェント 人材紹介求人の応募者選考一覧')

@section('breadcrumbs')

    <small>
        <a href="{{ route('company.home') }}">inCulエージェント管理画面TOP</a>
    </small>

@endsection

@section('css')
<style>

.recruit_status {
    cursor: pointer;
    /*border: 1px solid #959595;*/
    border-radius: 3px;
    padding: 3px 2px 3px 10px;
    margin-right: 5px;
    color: #999;
    font-size: 13px;
}

.recruit_status:hover {
    opacity: 0.5;
}

.recruit_status.bold {
    font-size: 14px;
    font-weight: bold;
    color: black;
    /*background: #c8e9fb;
    color: #004b74;
    border-color: #004b74;*/
}

.sort_recruit {
    float: right;
}

.dataTables_scrollHeadInner table, #dataTable-recruit {
    margin-bottom: 0 !important;
}
th, td {
    vertical-align: middle !important;
    text-align: center !important;
    white-space: nowrap !important;
}

td {
    padding: 0px !important;
    height: 80px;
}

td .padding-box {
    padding: 5px 10px !important;
}

td a {
    color: #212529;
}

td p {
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 0;
    white-space: normal;
}

td p.bold {
    font-weight: bold;
}

td div.padding-box p {
    white-space: nowrap;
}

td p em {
    font-style: unset;
    font-weight: bold;
}

td span {
    font-size: 13px;
    line-height: 1;
    white-space: nowrap;
}

td span.bold {
    font-weight: bold;
    font-size: 14px;
}

td .link-detail {
    padding-top: 5px;
    font-size: 14px;
}

td .first-line p {
    line-height: 2.3;
    margin-bottom: 0;
}

td .second-line.bg-blue {
    color: white;
    background: #073763;
    line-height: 1.3;
    padding: 5px 5px;
    margin-bottom: 0;
}

.green {
    color: #6AA84F;
}

.orange {
    color: #FF9900;
}

.gray {
    color: #999999;
}

tr.label-info td:first-child {
    border-left: solid 2px #00bcd4;
}
tr.label-danger td:first-child {
    border-left: solid 2px #ff0000;
}
tr.label-green td:first-child {
    border-left: solid 2px #6aa84f;
}
tr.label-orange td:first-child {
    border-left: solid 2px #ff9900;
}

</style>
@endsection

@section('content')

    <div id="mainContent">
        <h3 class="c-grey-900 mT-10 mB-20">
            <!-- <img src="/assets/static/images/logo.png">求人情報一覧 -->
            人材紹介求人の選考一覧
        </h3>

        <div class="row">
            <div class="col-md-12">
                <div class="bgc-white bd bdrs-3 p-20 mB-10">
                    <div class="row" id="search_box">
                        <div class="text-right pX-5">
                            <label class="form-label" style="margin-top: 5px; font-weight: bolder">求人情報</label>
                        </div>
                        <div class="col-md-2 text-center pL-0">
                            <select class="form-control" id="job-title-select">
                                <option value="">選択</option>
                                @foreach($jobTitles as $ret)
                                    <option value="{{ $ret->id }}">{{ $ret->job_title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="text-right pX-5">
                            <label class="form-label" style="margin-top: 5px; font-weight: bolder">更新日</label>
                        </div>
                        <div class="col-md-2 text-center pL-0">
                            <select class="form-control" id="search_date">
                                <option value="">年月</option>
                                @foreach($dateArray as $date)
                                    <option value="{{ $date['key'] }}">{{ $date['value'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="text-right pX-5">
                            <label class="form-label" style="margin-top: 5px; font-weight: bolder">検索</label>
                        </div>
                        <div class="col-md-4 text-center pL-0">
                            <input type="text" class="form-control pR-25" id="keyword" placeholder="転職者名、人材紹介会社名、求人タイトルのいずれかで検索">
                            <i class="ti-search place"></i>
                        </div>

                        <div class="col-auto text-center pL-0">
                            <button class="btn cur-p btn-search pX-30 form-control" nowrap>検索</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="bgc-white bd bdrs-3 p-20">
                <div class="c-grey-900 mB-10" id="count-div">
                    <div class="row">
                        <div class="col-md-8 pT-5" id="status_count">

                        </div>
                        <div class="col-md-4 mB-0">
                            <select class="form-control sort_recruit">
                                <option value="1">応募の新しい順</option>
                                <option value="2">応募の古い順</option>
                                <option value="3">選考の進んでいる順</option>
                                <option value="4">選考の進んでいない順</option>
                            </select>
                        </div>
                    </div>
                </div>

                <table id="dataTable-recruit" class="table table-striped table-bordered dataTable" cellspacing="0">
                    <thead>
                        <tr role="row">
                            <th>応募者名</th>
                            <th>人材紹介会社</th>
                            <th>求人タイトル</th>
                            <th>応募</th>
                            <th>書類確認</th>
                            <th>書類選考</th>
                            <th>筆記/webテスト</th>
                            <th>面談</th>
                            <th>１次面接</th>
                            <th>２次面接</th>
                            <th>３次面接</th>
                            <th>４次面接</th>
                            <th>５次面接</th>
                            <th>最終選考</th>
                            <th>採用</th>
                            <th>入社確認</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('js')

<script type="text/javascript" src="{{ url('js/company/datatable-recruit.js') }}"></script>
<script>
    let g_baseURL = '{{ url('') }}';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

@endsection

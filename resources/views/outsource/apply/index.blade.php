@extends('layouts.outsource')

@section('title', 'inCulエージェント 業務委託案件の提案者選考一覧')

@section('breadcrumbs')

    <small>
        <a href="{{ route('outsource.home') }}">inCulエージェント管理画面TOP</a>
    </small>

@endsection

@section('css')
<style>

.outsource_status {
    cursor: pointer;
    /*border: 1px solid #959595;*/
    border-radius: 3px;
    padding: 3px 2px 3px 10px;
    margin-right: 5px;
    color: #999;
    font-size: 13px;
}

.outsource_status:hover {
    opacity: 0.5;
}

.outsource_status.bold {
    font-size: 14px;
    font-weight: bold;
    color: black;
    /*background: #c8e9fb;
    color: #004b74;
    border-color: #004b74;*/
}

.sort_outsource {
    float: right;
}

.dataTables_scrollHeadInner table, #dataTable-outsource {
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

td span.blue {
    color: #2B7CBF;
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

tr.expand td .first-line .red {
    line-height: 2.5;
}

tr.expand td .second-line.bg-blue {
    line-height: 1.8;
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
            参画者の提案・選考状況一覧
        </h3>

        <div class="row">
            <div class="col-md-12">
                <div class="bgc-white bd bdrs-3 p-20 mB-10">
                    <div class="row" id="search_box">
                        <div class="text-right pX-5">
                            <label class="form-label" style="margin-top: 5px; font-weight: bolder">参画者で絞込み</label>
                        </div>
                        <div class="col-md-2 text-center pL-0">
                            <input type="text" class="form-control pR-25" id="job-seeker" placeholder="参画者名">
                            <i class="ti-search place"></i>
                        </div>

                        <div class="text-right pX-5">
                            <label class="form-label" style="margin-top: 5px; font-weight: bolder">担当で絞込み</label>
                        </div>
                        <div class="col-md-2 text-center pL-0">
                            <select class="form-control" id="outsource-user-select">
                                <option value="">選択</option>
                                @foreach($outsourceCompanyUsers as $outsource_company_user)
                                    <option value="{{ $outsource_company_user->id }}" {{ $outsource_company_user->id == $user->outsource_user->id ? 'selected' : '' }}>{{ $outsource_company_user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="text-right pX-5">
                            <label class="form-label" style="margin-top: 5px; font-weight: bolder">フリーワード</label>
                        </div>
                        <div class="col-md-4 text-center pL-0">
                            <input type="text" class="form-control pR-25" id="keyword" placeholder="求人企業名、案件タイトルのいずれかで検索">
                            <i class="ti-search place"></i>
                        </div>

                        <div class="col-auto text-center pL-0">
                            <button class="btn cur-p btn-search pX-30 form-control" nowrap>検索</button>
                        </div>
                        <div class="col-auto text-center pL-0">
                            <button class="btn cur-p btn-reset btn-outline-secondary form-control" nowrap>全ての条件を解除する</button>
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
                            <select class="form-control sort_outsource">
                                <option value="1">エントリーの新しい順</option>
                                <option value="2">エントリーの古い順</option>
                                <option value="3">選考の進んでいる順</option>
                                <option value="4">選考の進んでいない順</option>
                            </select>
                        </div>
                    </div>
                </div>

                <table id="dataTable-outsource" class="table table-striped table-bordered dataTable" cellspacing="0">
                    <thead>
                        <tr role="row">
                            <th>参画者</th>
                            <th>求人企業名<br/>営業担当</th>
                            <th>案件タイトル</th>
                            <th>提案状況</th>
                            <th>書類確認</th>
                            <th>書類選考</th>
                            <th>１次面談</th>
                            <th>２次面談</th>
                            <th>３次面談</th>
                            <th>最終選考</th>
                            <th>契約</th>
                            <th>参画確認</th>
                            <th>現況/請求</th>
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

<script type="text/javascript" src="{{ url('js/outsource/datatable-apply.js') }}"></script>
<script>
    let g_baseURL = '{{ url('') }}';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function() {
        var initials = [
        <?php foreach($outsourceJobSeekers as $job_seeker) {
            echo "'$job_seeker',";
        } ?>
        ""];

        autocomplete(document.getElementById("job-seeker"), initials);

        $("#search_box .btn-reset").click(function() {
            $("#job-seeker").val('');
            $("#outsource-user-select").val('');
            $("#keyword").val('');
        });
    });
</script>

@endsection

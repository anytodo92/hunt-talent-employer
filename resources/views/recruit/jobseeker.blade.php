@extends('layouts.recruit')

@section('title', 'inCulエージェント 人材紹介転職者一覧・詳細・編集')

@section('breadcrumbs')

	<small>
		<a href="{{ route('recruit.home') }}">inCulエージェント管理画面TOP</a>
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
}

.recruit_status:hover {
	opacity: 0.5;
}

.recruit_status.bold {
	font-weight: bold;
    /*background: #c8e9fb;
	color: #004b74;
    border-color: #004b74;*/
}

.sort_recruit {
	float: right;
}

.dataTables_scrollHeadInner table, #dataTable-recruit{
	margin-bottom: 0 !important;
}
th, td {
	vertical-align: middle !important;
	text-align: center !important;
	white-space: nowrap !important;
}

td {

	padding: 0 !important;
}

td .padding-box {
	padding: 15px 20px !important;
}

td a {
    color: #212529;
}

td p {
	font-size: 16px;
	line-height: 1.5;
	margin-bottom: 0.5em;
	white-space: nowrap;
}

td p.bold {
	font-weight: bold;
}

td p em {
	font-style: unset;
	font-weight: bold;
}

td span {
	font-size: 12px;
	line-height: 1;
	white-space: nowrap;
}

td span.bold{
	font-weight: bold;
	font-size: 15px;
}

td .link-detail {
    padding-top: 5px;
    font-size: 13px;
}

td .first-line .red{
	color: red;
	line-height: 2.5;
	margin-bottom: 0;
}

td .second-line.bg-blue{
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

</style>
@endsection

@section('content')
	<div id="mainContent">
		<h3 class="c-grey-900 mT-20 mB-30">
			<!-- <img src="/assets/static/images/logo.png">求人情報一覧 -->
            転職者一覧
		</h3>
		<div class="row">
			<div class="col-md-12">
				<div class="bgc-white bd bdrs-3 p-20 mB-20">
					<div class="row">
                        <div class="col-md-1 text-center">
                            <label class="form-label" style="margin-top: 5px; font-weight: bolder">検索：</label>
                        </div>
                        <div class="col-md-2 text-center">
                            <input type="text" class="form-control" id="keyword" value="{{ $keywword }}"placeholder="転職者名、推薦文、メモで絞込み">
                        </div>
						<div class="col-md-1 text-center">
							<label class="form-label" style="margin-top: 5px; font-weight: bolder">担当で絞込み</label>
						</div>
						<div class="col-md-2 text-center">
							<select class="form-control" id="search_username">
                                @foreach($userNames as $userName)
                                    <option>{{ $userName['name'] }}</option>
                                @endforeach
							</select>
						</div>

						<div class="col-md-1 text-center">
							<label class="form-label" style="margin-top: 5px; font-weight: bolder">ステータスで絞込み</label>
						</div>
						<div class="col-md-2 text-center">
							<select class="form-control" id="search_date">
                                @foreach($status as $ret)
                                    <option>{{ $ret }}</option>
                                @endforeach
							</select>
						</div>

                        <div class="col-md-1 text-center">
                            <div class="form-check">
                                <label class="form-label form-check-label">
                                    <input class="form-check-input" type="checkbox">送客者のみを表示
                                </label>
                            </div>
                        </div>

                        <div class="col-md-1 text-center">
                            <button class="btn cur-p btn-outline-dark btn-color" id="recruit_cancel_button">全ての条件を解除する</button>
                        </div>

						<div class="col-md-1 text-center">
							<button class="btn cur-p btn-primary btn-color" id="recruit_new_button">転職者を新規登録</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
        <div class="col-md-12">
            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <div class="c-grey-900 mB-20" id="count-div">
                    <div class="row">
                        <div class="col-md-8" style="padding-top: 10px" id="status_count">

                        </div>
                    </div>
                </div>
                <table id="dataTable-recruit" class="table table-striped table-bordered dataTable" cellspacing="0" width="100%" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
                    <thead>
                        <tr role="row">
                            <th>転職者名</th>
                            <th>担当者</th>
                            <th>年齢</th>
                            <th>ステータス</th>
                            <th>直近の選考状況</th>
                            <th>求人票送付数</th>
                            <th>応募総数</th>
                            <th>送客／自社登録</th>
                            <th>勤務可能時期</th>
                            <th>メモ</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
	</div>

    <div id="search-result" class="hide">
        <div class="title">
            <strong>転職者情報詳細</strong>
            <a href="javascript:void(0)" class="btn" data-cl="search-result-hide" id="btnClose"><i class="ti-close"></i></a>
        </div>
        <div class="content">
            <div class="card mb-2 card__tabs">
                <div class="card-body">
                    <div class="card-header" id="tab_header">
                        <ul class="nav nav-tabs-side" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <span class="ml-3">ステータス内容</span>
                                <button class="btn btn-info ml-5" id="btn_edit-tab" type="button" role="tab">編集</button>
                                <span class="btn ml-5"><i class="ti-trash"></i></span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body" id="app">
                        <div class="tab-content" id="myTabContent">
                            <!-- 詳細 -->
                            <div class="tab-pane fade show active seeker-tab" id="seeker" role="tabpanel">
                                <div class="card-body">
                                    <div class="profile">
                                        <figure class="figure">
                                            <img class="img" src="{{ asset('/storage/no_image.png') }}" alt="">
                                        </figure>
                                        <div class="name-group">
                                            <p class="label" id="detail_name"></p>
                                            <p class="last-name" id="detail_name_kana"></p>
                                            <p class="first-name" id="detail_email"></p>
                                        </div>
                                    </div>
                                    <div class="form-check" style="padding-left: 120px;">
                                        <label class="form-label form-check-label">
                                            <input class="form-check-input" type="checkbox">
                                            「面談・面接リマインドメール」自動送信設定
                                        </label>
                                    </div>
                                    <div class="text-group" style="padding-left: 80px;">
                                        <p class="label" id="detail_phone_number"></p>
                                        <p class="label" id="detail_address"></p>
                                        <p class="label" id="detail_closest_station"></p>
                                    </div>
                                    <textarea cols="5" class="form-control" placeholder=" 転職者メモ（社内共有）" id="detail_job_seeker_memo"></textarea>
                                    <p class="label text-right" id="detail_update_at">前回更新：2021.10.10 担当者名</p>
                                    <div class="row mt-3 justify-content-center">
                                        <button class="btn btn-outline-dark ml-2">キャンセル</button>
                                        <button class="btn btn-primary ml-2">変更する</button>
                                    </div>
                                </div>
                                <div class="card mb-2 mt-2">
                                    <div class="card-header">
                                        <span class="text-left">紹介した求人</span>
                                        <span class="text-right float-right">全ての選考一覧 >></span>
                                    </div>
                                    <div class="card-body" id="detail_job_list">
                                        <job-list ref="jobList"
                                        ></job-list>
                                        <div class="row mt-2 justify-content-center">
                                            <button class="btn btn-primary">転職者に適合する求人を検索</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-2">
                                    <div class="card-header">
                                        <span class="text-left">担当とのキャリア面談履歴</span>
                                    </div>
                                    <div class="card-body">
                                        <interview-schedule ref="interviewSchedule"
                                        ></interview-schedule>
                                        <div class="row mt-3 justify-content-center">
                                            <button class="btn btn-primary" id="btn_detail_add_schedule" onclick="showAddScheduleModal()">＋面談予定を追加</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-2">
                                    <div class="card-header">
                                        <span class="text-left">キャリア・詳細情報</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="profile-detail-group">
                                            <div class="text-line">
                                                <p class="line-head">年齢</p>
                                                <span></span>
                                                <p class="line-content" id="detail_age"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">性別</p>
                                                <span></span>
                                                <p class="line-content" id="detail_sex"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">最終学歴</p>
                                                <span></span>
                                                <p class="line-content" id="detail_final_edcuation"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">現在までの就業社数</p>
                                                <span></span>
                                                <p class="line-content" id="detail_working_company_number"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">国籍と日本語レベル</p>
                                                <span></span>
                                                <p class="line-content" id="detail_nationality"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">英語レベル</p>
                                                <span></span>
                                                <p class="line-content" id="detail_japanese_level"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">中国語レベル</p>
                                                <span></span>
                                                <p class="line-content" id="detail_chinese_level"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">希望職種</p>
                                                <span></span>
                                                <p class="line-content" id="detail_desired_job_category"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">希望業界</p>
                                                <span></span>
                                                <p class="line-content" id="detail_desired_industry"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">経験職種</p>
                                                <span></span>
                                                <p class="line-content" id="detail_experience_job_category"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">経験業界</p>
                                                <span></span>
                                                <p class="line-content" id="detail_experience_industry"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">現在の雇用形態
                                                </p>
                                                <span></span>
                                                <p class="line-content" id="detail_employment_status"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">希望給与</p>
                                                <span>年収</span>
                                                <p class="line-content" id="detail_annual_income"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head"></p>
                                                <span>月給</span>
                                                <p class="line-content" id="detail_desired_income"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">現在年収</p>
                                                <span></span>
                                                <p class="line-content" id="detail_current_annual_income"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">希望勤務地</p>
                                                <span></span>
                                                <p class="line-content" id="detail_suggested_working_place"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">在宅勤務</p>
                                                <span></span>
                                                <p class="line-content" id="detail_home_working"></p>
                                            </div>
                                            <div class="text-line">
                                                <p class="line-head">勤務可能時期</p>
                                                <span></span>
                                                <p class="line-content" id="detail_workable_date"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-2">
                                    <div class="card-header">
                                        <span class="text-left">特徴・訴求ポイントマッチング</span>
                                    </div>
                                    <div class="card-body">
                                        <p class="text" id="detail_feature_desired"></p>
                                    </div>
                                </div>

                                <div class="card mb-2">
                                    <div class="card-header">
                                        <span class="text-left">履歴書・職務経歴書・その他</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mt-3 justify-content-center">
                                            <button class="btn btn-primary" id="detail_file_upload">＋ その他書類をアップロード</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-2">
                                    <div class="card-header">
                                        <span class="text-left">推薦文</span>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" rows="10" placeholder="推薦文"></textarea>
                                        <div class="row mt-3 justify-content-center">
                                            <button class="btn btn-outline-dark">キャンセル</button>
                                            <button class="btn btn-primary">変更する</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 編集 -->
                            <div class="tab-pane fade edit-tab" id="edit" role="tabpanel">
                                <div class="card-header mt-2">
                                    転職者情報編集
                                </div>
                                <div class="card-body">
                                    <!-- 氏名 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">氏名</label>
                                        </div>
                                        <div class="row col-md-8">
                                            <input type="text" class="col-md-4 form-control mr-3" id="edit_last_name">
                                            <input type="text" class="col-md-4 form-control" id="edit_first_name">
                                        </div>
                                    </div>
                                    <!-- 氏名（かな） -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">氏名（かな）</label>
                                        </div>
                                        <div class="row col-md-8">
                                            <input type="text" class="col-md-4 form-control mr-3" id="edit_last_name_kana">
                                            <input type="text" class="col-md-4 form-control" id="edit_first_name_kana">
                                        </div>
                                    </div>
                                    <!-- メールアドレス -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">メールアドレス</label>
                                        </div>
                                        <div class="row col-md-8">
                                            <input type="text" class="col-md-10 form-control" id="edit_email">
                                        </div>
                                    </div>
                                    <!-- 電話番号 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">電話番号</label>
                                        </div>
                                        <div class="row col-md-8">
                                            <input type="text" class="col-md-10 form-control" id="edit_phone_number">
                                        </div>
                                    </div>
                                    <!-- 現住所 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">現住所</label>
                                        </div>
                                        <div class="col-md-8 p-0">
                                            <select class="col-md-5 form-control" id="edit_prefecture_id">
                                                <option>--都道府県▼--</option>
                                                @foreach(g_enum('prefectures') as $idx => $prefectures)
                                                    <option value="{{ $idx }}">{{ $prefectures }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" class="col-md-10 form-control" id="edit_address">
                                        </div>
                                    </div>
                                    <!-- 路線・最寄駅 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">路線・最寄駅</label>
                                        </div>
                                        <div class="col-md-8 p-0">
                                            <input type="text" class="col-md-10 form-control" id="edit_closest_station">
                                            <label class="col-md-10">※駅名を入力いただき該当の路線/駅をご選択ください。</label>
                                        </div>
                                    </div>
                                    <!-- 生年月日 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">生年月日</label>
                                        </div>
                                        <div class="row col-md-8 align-items-center">
                                            <input type="date" class="form-control col-md-10" id="edit_birthday">
                                        </div>
                                    </div>
                                    <!-- 年齢 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">年齢</label>
                                        </div>
                                        <div class="row col-md-8 align-items-center">
                                            <input type="text" class="col-md-5 form-control" id="edit_age">歳
                                        </div>
                                    </div>
                                    <!-- 性別 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">性別</label>
                                        </div>
                                        <div class="row col-md-8 align-items-center">
                                            <div class="col-md-4">
                                                <input type="radio" name="sex" id="edit_sex_man">
                                                <label for="edit_sex_man">男性</label>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="radio" name="sex" id="edit_sex_woman">
                                                <label for="edit_sex_woman">女性</label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 最終学歴 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">最終学歴</label>
                                        </div>
                                        <div class="row col-md-8 align-items-center">
                                            <select class="col-md-8 form-control" id="edit_final_edcuation">
                                                <option>--選択してください--</option>
                                                @foreach(g_enum('job_seekers_final_education') as $idx => $final)
                                                    <option value={{$idx}}>{{ $final }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- 現在までの就業社数 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">現在までの就業社数</label>
                                        </div>
                                        <div class="row col-md-8 align-items-center">
                                            <select class="col-md-3 form-control" id="edit_working_company_number">
                                                <option>--選択してください--</option>
                                                @foreach(g_enum('job_seekers_working_company_number') as $idx => $work)
                                                    <option value={{$idx}}>{{ $work }}</option>
                                                @endforeach
                                            </select>社
                                        </div>
                                    </div>
                                    <!-- 国籍 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">国籍</label>
                                        </div>
                                        <div class="row col-md-8 align-items-center">
                                            <select class="col-md-5 form-control" id="edit_nationality">
                                                <option>--選択してください--</option>
                                                @foreach(g_enum('job_seekers_nationality') as $idx => $nation)
                                                    <option value={{$idx}}>{{ $nation }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- 日本語レベル -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">日本語レベル</label>
                                        </div>
                                        <div class="row col-md-8 align-items-center">
                                            <select class="col-md-8 form-control" id="edit_japanese_level">
                                                <option>--選択してください--</option>
                                                @foreach(g_enum('job_seekers_language_level') as $idx => $lang)
                                                    <option value={{$idx}}>{{ $lang }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- 英語レベル -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">英語レベル</label>
                                        </div>
                                        <div class="row col-md-8 align-items-center">
                                            <select class="col-md-8 form-control" id="edit_english_level">
                                                <option>--選択してください--</option>
                                                @foreach(g_enum('job_seekers_language_level') as $idx => $lang)
                                                    <option value={{$idx}}>{{ $lang }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- 中国語レベル -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">中国語レベル</label>
                                        </div>
                                        <div class="row col-md-8 align-items-center">
                                            <select class="col-md-8 form-control" id="edit_chinese_level">
                                                <option>--選択してください--</option>
                                                @foreach(g_enum('job_seekers_language_level') as $idx => $lang)
                                                    <option value={{$idx}}>{{ $lang }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- 希望職種 -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label class="ml-4">希望職種</label>
                                        </div>
                                        <div class="col-md-8 align-items-center p-0">
                                            <select class="col-md-5 form-control"></select>
                                            <select class="col-md-8 form-control"></select>
                                        </div>
                                    </div>
                                    <!-- ＋ 追加する -->
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                        </div>
                                        <div class="col-md-8 align-items-center p-0">
                                            <button class="btn btn-primary col-md-10" id="btn_edit_desired_ind">＋ 追加する</button>
                                        </div>
                                    </div>

                                    <!-- 希望業界 -->
                                    <div id="edit_desired_ind_div" style="content-visibility: hidden">
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">希望業界</label>
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <select class="col-md-5 form-control"></select>
                                                <select class="col-md-8 form-control"></select>
                                            </div>
                                        </div>
                                        <!-- ＋ 追加する -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <button class="btn btn-primary col-md-10" id="btn_edit_exp_occ">＋ 追加する</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 経験職種 -->
                                    <div id="edit_exp_occ_div" style="content-visibility: hidden">
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">経験職種</label>
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <select class="col-md-5 form-control"></select>
                                                <select class="col-md-8 form-control"></select>
                                                <select class="col-md-5 form-control"></select>
                                                <br>
                                                <select class="col-md-5 form-control"></select>
                                                <select class="col-md-8 form-control"></select>
                                                <select class="col-md-5 form-control"></select>
                                            </div>
                                        </div>
                                        <!-- ＋ 追加する -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <button class="btn btn-primary col-md-10" id="btn_edit_exp_ind">＋ 追加する</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 経験業界 -->
                                    <div id="edit_exp_ind_div" style="content-visibility: hidden">
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">経験業界</label>
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <select class="col-md-5 form-control"></select>
                                                <select class="col-md-8 form-control"></select>
                                                <select class="col-md-5 form-control"></select>
                                                <br>
                                                <select class="col-md-5 form-control"></select>
                                                <select class="col-md-8 form-control"></select>
                                                <select class="col-md-5 form-control"></select>
                                            </div>
                                        </div>
                                        <!-- ＋ 追加する -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <button class="btn btn-primary col-md-10" id="btn_edit_cur_emp">＋ 追加する</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 現在の雇用形態 -->
                                    <div id="edit_cur_emp_div" style="content-visibility: hidden">
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">現在の雇用形態</label>
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <select class="col-md-8 form-control"></select>
                                            </div>
                                        </div>
                                        <!-- 希望給与 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">希望給与</label>
                                            </div>
                                            <div class="col-md-8 align-items-center">
                                                <div class="row align-items-center">
                                                    <div class="col-md-4 align-items-center">
                                                        <input type="radio">年収
                                                    </div>
                                                    <div class="row col-md-8 align-items-center">
                                                        <input type="text" class="col-md-10 form-control" placeholder="半角数字">万円
                                                    </div>
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="row col-md-4 align-items-center m-0">
                                                        <input type="radio">
                                                        <select class="col-md-9 form-control"></select>
                                                    </div>
                                                    <div class="row col-md-8 align-items-center">
                                                        <input type="text" class="col-md-10 form-control" placeholder="半角数字">円
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- 現在年収 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">現在年収</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center">
                                                <input type="text" class="col-md-8 form-control" placeholder="半角数字">万円
                                            </div>
                                        </div>
                                        <!-- 希望勤務地 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">希望勤務地</label>
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <select class="col-md-5 form-control"></select>
                                                <select class="col-md-5 form-control"></select>
                                                <select class="col-md-5 form-control"></select>
                                            </div>
                                        </div>
                                        <!-- ＋ 追加する -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <button class="btn btn-primary col-md-10" id="btn_edit_work_remote">＋ 追加する</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 在宅勤務 -->
                                    <div id="edit_work_remote_div" style="content-visibility: hidden">
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">在宅勤務</label>
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <select class="col-md-8 form-control"></select>
                                            </div>
                                        </div>

                                        <!-- 特徴・希望 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">特徴・希望</label>
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input">正社員経験なしOKの仕事
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input">上場企業で働きたい
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input">ベンチャー企業で・・・・
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input">・・・・
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ステータス -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">ステータス</label>
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <select class="col-md-8 form-control"></select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col mt-5 text-center">
                                        <button class="btn btn-outline-dark">キャンセル</button>
                                        <button class="btn btn-primary">変更する</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 新規登録 -->
                            <div class="tab-pane fade new-tab" id="new" role="tabpanel">
                                <div class="card mt-2">
                                    <div class="card-header mt-2">
                                        転職者情報編集
                                    </div>
                                    <div class="card-body">
                                        <!-- 氏名 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">氏名</label>
                                            </div>
                                            <div class="row col-md-8">
                                                <input type="text" class="col-md-4 form-control mr-3" id="new_last_name" name="new_last_name">
                                                <input type="text" class="col-md-4 form-control" id="new_first_name" name="new_first_name">
                                            </div>
                                        </div>
                                        <!-- 氏名（かな） -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">氏名（かな）</label>
                                            </div>
                                            <div class="row col-md-8">
                                                <input type="text" class="col-md-4 form-control mr-3" id="new_last_name_kana" name="new_last_name_kana">
                                                <input type="text" class="col-md-4 form-control" id="new_first_name_kana" name="new_first_name_kana">
                                            </div>
                                        </div>
                                        <!-- メールアドレス -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">メールアドレス</label>
                                            </div>
                                            <div class="row col-md-8">
                                                <input type="text" class="col-md-10 form-control" id="new_email" name="new_email">
                                            </div>
                                        </div>
                                        <!-- 電話番号 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">電話番号</label>
                                            </div>
                                            <div class="row col-md-8">
                                                <input type="text" class="col-md-10 form-control" id="new_phone_number" name="new_phone_number">
                                            </div>
                                        </div>
                                        <!-- 現住所 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">現住所</label>
                                            </div>
                                            <div class="col-md-8 p-0">
                                                <select class="col-md-5 form-control" id="new_prefecture_id" name="new_prefecture_id">
                                                    option>--都道府県▼--</option>
                                                    @foreach(g_enum('prefectures') as $idx => $prefectures)
                                                        <option value="{{ $idx }}">{{ $prefectures }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" class="col-md-10 form-control">
                                            </div>
                                        </div>
                                        <!-- 路線・最寄駅 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">路線・最寄駅</label>
                                            </div>
                                            <div class="col-md-8 p-0">
                                                <input type="text" class="col-md-10 form-control" id="new_closest_station" name="new_closest_station">
                                                <label class="col-md-10">※駅名を入力いただき該当の路線/駅をご選択ください。</label>
                                            </div>
                                        </div>
                                        <!-- 生年月日 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">生年月日</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center">
                                                <input type="date" class="form-control col-md-10">
                                            </div>
                                        </div>
                                        <!-- 年齢 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">年齢</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center">
                                                <input type="text" class="col-md-5 form-control" id="new_age" name="new_age">歳
                                            </div>
                                        </div>
                                        <!-- 性別 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">性別</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center">
                                                <div class="col-md-4">
                                                    <input type="radio" name="new_sex" id="new_sex_man">
                                                    <label for="edit_sex_man">男性</label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="radio" name="new_sex" id="new_sex_woman">
                                                    <label for="edit_sex_woman">女性</label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- 最終学歴 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">最終学歴</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center">
                                                <select class="col-md-8 form-control" id="new_final_edcuation" name="new_final_edcuation">
                                                    <option>--選択してください--</option>
                                                    @foreach(g_enum('job_seekers_final_education') as $idx => $final)
                                                        <option value={{$idx}}>{{ $final }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!-- 現在までの就業社数 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">現在までの就業社数</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center">
                                                <select class="col-md-3 form-control" id="new_working_company_number" name="new_working_company_number">
                                                    <option>--選択してください--</option>
                                                    @foreach(g_enum('job_seekers_working_company_number') as $idx => $work)
                                                        <option value={{$idx}}>{{ $work }}</option>
                                                    @endforeach
                                                </select>社
                                            </div>
                                        </div>
                                        <!-- 国籍 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">国籍</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center">
                                                <select class="col-md-5 form-control" id="new_nationality" name="new_nationality">
                                                    <option>--選択してください--</option>
                                                    @foreach(g_enum('job_seekers_nationality') as $idx => $nation)
                                                        <option value={{$idx}}>{{ $nation }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!-- 日本語レベル -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">日本語レベル</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center" id="new_japanese_level" name="new_japanese_level">
                                                <select class="col-md-8 form-control">
                                                    <option>--選択してください--</option>
                                                    @foreach(g_enum('job_seekers_language_level') as $idx => $lang)
                                                        <option value={{$idx}}>{{ $lang }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!-- 英語レベル -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">英語レベル</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center">
                                                <select class="col-md-8 form-control" id="new_english_level" name="new_english_level">
                                                    <option>--選択してください--</option>
                                                    @foreach(g_enum('job_seekers_language_level') as $idx => $lang)
                                                        <option value={{$idx}}>{{ $lang }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!-- 中国語レベル -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">中国語レベル</label>
                                            </div>
                                            <div class="row col-md-8 align-items-center">
                                                <select class="col-md-8 form-control" id="new_chinese_level" name="new_chinese_level">
                                                    <option>--選択してください--</option>
                                                    @foreach(g_enum('job_seekers_language_level') as $idx => $lang)
                                                        <option value={{$idx}}>{{ $lang }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!-- 希望職種 -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label class="ml-4">希望職種</label>
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <select class="col-md-5 form-control"></select>
                                                <select class="col-md-8 form-control"></select>
                                            </div>
                                        </div>
                                        <!-- ＋ 追加する -->
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                            </div>
                                            <div class="col-md-8 align-items-center p-0">
                                                <button class="btn btn-primary col-md-10" id="btn_new_desired_ind">＋ 追加する</button>
                                            </div>
                                        </div>

                                        <!-- 希望業界 -->
                                        <div id="new_desired_ind_div" style="content-visibility: hidden">
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">希望業界</label>
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <select class="col-md-5 form-control"></select>
                                                    <select class="col-md-8 form-control"></select>
                                                </div>
                                            </div>
                                            <!-- ＋ 追加する -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <button class="btn btn-primary col-md-10" id="btn_new_exp_occ">＋ 追加する</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 経験職種 -->
                                        <div id="new_exp_occ_div" style="content-visibility: hidden">
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">経験職種</label>
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <select class="col-md-5 form-control"></select>
                                                    <select class="col-md-8 form-control"></select>
                                                    <select class="col-md-5 form-control"></select>
                                                    <br>
                                                    <select class="col-md-5 form-control"></select>
                                                    <select class="col-md-8 form-control"></select>
                                                    <select class="col-md-5 form-control"></select>
                                                </div>
                                            </div>
                                            <!-- ＋ 追加する -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <button class="btn btn-primary col-md-10" id="btn_new_exp_ind">＋ 追加する</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 経験業界 -->
                                        <div id="new_exp_ind_div" style="content-visibility: hidden">
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">経験業界</label>
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <select class="col-md-5 form-control"></select>
                                                    <select class="col-md-8 form-control"></select>
                                                    <select class="col-md-5 form-control"></select>
                                                    <br>
                                                    <select class="col-md-5 form-control"></select>
                                                    <select class="col-md-8 form-control"></select>
                                                    <select class="col-md-5 form-control"></select>
                                                </div>
                                            </div>
                                            <!-- ＋ 追加する -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <button class="btn btn-primary col-md-10" id="btn_new_cur_emp">＋ 追加する</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 現在の雇用形態 -->
                                        <div id="new_cur_emp_div" style="content-visibility: hidden">
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">現在の雇用形態</label>
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <select class="col-md-8 form-control"></select>
                                                </div>
                                            </div>
                                            <!-- 希望給与 -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">希望給与</label>
                                                </div>
                                                <div class="col-md-8 align-items-center">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-4 align-items-center">
                                                            <input type="radio">年収
                                                        </div>
                                                        <div class="row col-md-8 align-items-center">
                                                            <input type="text" class="col-md-10 form-control" placeholder="半角数字">万円
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center">
                                                        <div class="row col-md-4 align-items-center m-0">
                                                            <input type="radio">
                                                            <select class="col-md-9 form-control"></select>
                                                        </div>
                                                        <div class="row col-md-8 align-items-center">
                                                            <input type="text" class="col-md-10 form-control" placeholder="半角数字">円
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- 現在年収 -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">現在年収</label>
                                                </div>
                                                <div class="row col-md-8 align-items-center">
                                                    <input type="text" class="col-md-8 form-control" placeholder="半角数字">万円
                                                </div>
                                            </div>
                                            <!-- 希望勤務地 -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">希望勤務地</label>
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <select class="col-md-5 form-control"></select>
                                                    <select class="col-md-5 form-control"></select>
                                                    <select class="col-md-5 form-control"></select>
                                                </div>
                                            </div>
                                            <!-- ＋ 追加する -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <button class="btn btn-primary col-md-10" id="btn_new_work_remote">＋ 追加する</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 在宅勤務 -->
                                        <div id="new_work_remote_div" style="content-visibility: hidden">
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">在宅勤務</label>
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <select class="col-md-8 form-control"></select>
                                                </div>
                                            </div>

                                            <!-- 特徴・希望 -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">特徴・希望</label>
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input">正社員経験なしOKの仕事
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input">上場企業で働きたい
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input">ベンチャー企業で・・・・
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input">・・・・
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ステータス -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <label class="ml-4">ステータス</label>
                                                </div>
                                                <div class="col-md-8 align-items-center p-0">
                                                    <select class="col-md-8 form-control"></select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 履歴書・職務経歴書・その他 -->
                                <div class="card mt-2" id="new-fileupload-div">
                                    <div class="card-header">
                                        履歴書・職務経歴書・その他
                                    </div>
                                    <div class="card-body mt-2">
                                        <div class="row mt-2">
                                            <div class="col-md-3 upload-title">
                                                <p class="text ml-3">履歴書</p>
                                            </div>
                                            <div class="col-md-9 upload-path">
                                                <figure class="figure">
                                                    <img class="img" alt="">
                                                </figure>
                                                <div class="name">asdasdfasfdsa</div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-3 upload-title">
                                                <p class="text ml-3">職務
                                                    経歴書</p>
                                            </div>
                                            <div class="col-md-9 upload-path">
                                                <figure class="figure">
                                                    <img class="img" alt="">
                                                </figure>
                                                <div class="name">asdasdfasfdsa</div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-3 upload-title">
                                                <p class="text ml-3">その他</p>
                                            </div>
                                            <div class="col-md-9 upload-path">
                                                <figure class="figure">
                                                    <img class="img" alt="">
                                                </figure>
                                                <div class="name">asdasdfasfdsa</div>
                                            </div>
                                        </div>
                                        <div class="row mt-5 justify-content-center" id="uploadDiv">
                                            <button class="btn btn-primary" id="btn_new_upload_other">＋ その他書類をアップ</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 推薦文 -->
                                <div class="card mt-2">
                                    <div class="card-header">
                                        推薦文
                                    </div>
                                    <div class="card-body mt-2">
                                        <textarea class="form-control" rows="10" placeholder="推薦文"></textarea>
                                    </div>
                                </div>

                                <!-- 転職者メモ -->
                                <div class="card mt-2">
                                    <div class="card-header">
                                        転職者メモ
                                    </div>
                                    <div class="card-body mt-2">
                                        <textarea class="form-control" rows="10" placeholder="推薦文"></textarea>
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input">登録する転職者はインクルエージェントを通して職業斡旋を受けることに同意していることを保証する。
                                            </label>
                                        </div>
                                        <div class="col mt-5 text-center">
                                            <button class="btn btn-primary" style="height: 56px;">登録して<br>別の転職者を登録する</button>
                                            <button class="btn btn-primary" style="height: 56px;">登録して</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>.

    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="addScheduleModalLabel">担当とのキャリア面談予定の登録</h5>
                </div>
                <div class="modal-body">
                    転職者との面談日程を登録します。
                    <div class="row mt-2">
                        <label class="col-md-3">面談日</label>
                        <input type="date" class="form-control col-md-7" id="detail_add_schedule_date" placeholder="日付を選択">
                    </div>
                    <div class="row mt-2 align-items-center">
                        <label class="col-md-3">時間</label>
                        <input type="time" class="form-control col-md-3" id="detail_add_schedule_time_from">~
                        <input type="time" class="form-control col-md-3" id="detail_add_schedule_time_to">
                    </div>
                    <div class="row mt-3">
                        <label class="col-md-3">面談者</label>
                        <select class="col-md-3 form-control" id="detail_add_schedule_interviewer" name="detail_add_schedule_interviewer">
                            <option>--選択してください--</option>
                            @foreach($userNames as $userName)
                                <option>{{ $userName['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" onclick="saveSchedule()">登録する</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')

<script>
</script>

<script src="{{ url('js/jobseeker/datatable-jobseeker.js') }}"></script>
<script src="{{ url('js/jobseeker/jobseeker_detail.js') }}"></script>
<script>
    let enumJobSeekersFeatureDesired = @json(g_enum('job_seekers_feature_desired'));
    let enumJobSeekersSex = @json(g_enum('sex'));
    let enumJobSeekersFinalEducation = @json(g_enum('job_seekers_final_education'));
    let enumJobSeekersWorkingCompanyNumber = @json(g_enum('job_seekers_working_company_number'));
    let enumJobSeekersNationality = @json(g_enum('job_seekers_nationality'));
    let enumJobSeekersLanguageLevel = @json(g_enum('job_seekers_language_level'));
    let enumJobSeekersDesiredIncomeType = @json(g_enum('job_seekers_desired_income_type'));
    let enumJobSeekersHomeWorking = @json(g_enum('job_seekers_home_working'));
</script>
<script>
    let g_baseURL = '{{ url('') }}';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#btnClose").click(function(){
        $(this).addClass("active");
        $(this).parent().parent().addClass("hide");
        $(this).parent().parent().parent().addClass($(this).data("cl"));

        $("#new_desired_ind_div").css({contentVisibility : "hidden"});
        $("#new_exp_occ_div").css({contentVisibility : "hidden"});
        $("#new_exp_ind_div").css({contentVisibility : "hidden"});
        $("#new_cur_emp_div").css({contentVisibility : "hidden"});
        $("#new_work_remote_div").css({contentVisibility : "hidden"});

        $("#edit_desired_ind_div").css({contentVisibility : "hidden"});
        $("#edit_exp_occ_div").css({contentVisibility : "hidden"});
        $("#edit_exp_ind_div").css({contentVisibility : "hidden"});
        $("#edit_cur_emp_div").css({contentVisibility : "hidden"});
        $("#edit_work_remote_div").css({contentVisibility : "hidden"});

        $('#tab_header').show();
        $('#seeker').addClass("show active");
        $('#edit').removeClass("show active");
        $('#new').removeClass("show active");
    });


    $("#recruit_new_button").click(function () {
        $('#search-result').removeClass("hide");
        $('#tab_header').hide();
        $('#seeker').removeClass("show active");
        $('#edit').removeClass("show active");
        $('#new').addClass("show active");

    });

    $("#btn_edit-tab").click(function () {
        $('#search-result').removeClass("hide");
        $('#tab_header').show();
        $('#seeker').removeClass("show active");
        $('#edit').addClass("show active");
        $('#new').removeClass("show active");

    });

    $("#btn_new_desired_ind").click(function () {
        $("#new_desired_ind_div").css({contentVisibility : "visible"});
    });
    $("#btn_new_exp_occ").click(function () {
        $("#new_exp_occ_div").css({contentVisibility : "visible"});
    });
    $("#btn_new_exp_ind").click(function () {
        $("#new_exp_ind_div").css({contentVisibility : "visible"});
    });
    $("#btn_new_cur_emp").click(function () {
        $("#new_cur_emp_div").css({contentVisibility : "visible"});
    });
    $("#btn_new_work_remote").click(function () {
        $("#new_work_remote_div").css({contentVisibility : "visible"});
    });

    $("#btn_edit_desired_ind").click(function () {
        $("#edit_desired_ind_div").css({contentVisibility : "visible"});
    });
    $("#btn_edit_exp_occ").click(function () {
        $("#edit_exp_occ_div").css({contentVisibility : "visible"});
    });
    $("#btn_edit_exp_ind").click(function () {
        $("#edit_exp_ind_div").css({contentVisibility : "visible"});
    });
    $("#btn_edit_cur_emp").click(function () {
        $("#edit_cur_emp_div").css({contentVisibility : "visible"});
    });
    $("#btn_edit_work_remote").click(function () {
        $("#edit_work_remote_div").css({contentVisibility : "visible"});
    });

    $("#btn_new_upload_other").click(function () {
        let uploadHtml = '<div class="row mt-2">' +
                            '<div class="col-md-3 upload-title">' +
                                '<p class="text ml-3">その他</p>' +
                            '</div>' +
                            '<div class="col-md-9 upload-path">' +
                                '<figure class="figure">' +
                                    '<img class="img" alt="">' +
                                '</figure>' +
                                '<div class="name">asdasdfasfdsa</div>' +
                            '</div>' +
                        '</div>';
        $("#uploadDiv").before(uploadHtml);
    });

    function showAddScheduleModal() {
        $('#addScheduleModal').modal('show');
    }

    function saveSchedule() {
        let detailAddScheduleDate = $('#detail_add_schedule_date').val();
        let detailAddScheduleTimeFrom = $('#detail_add_schedule_time_from').val();
        let detailAddScheduleTimeTo = $('#detail_add_schedule_time_to').val();
        let detailAddScheduleInterviewer = $('#detail_add_schedule_interviewer').val();

        if (detailAddScheduleDate.length == 0) {
            alert('Please select date.');
        } else if (detailAddScheduleTimeFrom.length == 0) {
            alert('Please select first time.');
        } else if (detailAddScheduleTimeTo.length == 0) {
            alert('Please select second time.');
        } else if (detailAddScheduleInterviewer == '--選択してください--') {
            alert('Please select interviewer.');
        } else {
            let postParam = {
                jobSeekerID : g_jobseekerID,
                scheduleDate : detailAddScheduleDate,
                scheduleTimeFrom : detailAddScheduleTimeFrom,
                scheduleTimeTo : detailAddScheduleTimeTo,
                scheduleInterviewer : detailAddScheduleInterviewer,
            };

            $.ajax({
                type : "post",
                url : '/agent/jobseeker/saveSchedule',
                data : postParam,
                success : function (data) {
                    if (data.success == true) {
                        alert('You have successfully save schedule.')
                        $('#addScheduleModal').modal('hide');
                    }
                }, error : function (data) {
                    console.log(data);
                }
            });
        }
    }
</script>

@endsection

@extends('layouts.company')

@section('title', 'inCulエージェント管理画面TOP')

@section('breadcrumbs')

    <small>
        <a href="{{ route('company.home') }}">inCulエージェント管理画面TOP</a>
    </small>

@endsection

@section('content')

    <div id="mainContent">
        <h3 class="c-grey-900 mT-20 mB-30">
            <!-- <img src="/assets/static/images/logo.png"> -->
            求人情報一覧
            <div class="recruit_buttons">
                <a class="btn cur-p btn-primary btn-color recruit_btn" href="{{ route('company.job_add', ['type' => 'J']) }}">新規求人を作成</a>
                <a class="btn cur-p btn-primary btn-color recruit_btn" href="{{ route('company.job_add', ['type' => 'G']) }}">新規業務委託案件を作成</a>
            </div>
        </h3>

        <div style="clear: both; "></div>
        <input type="hidden" id="search_url" value="{{route('company.job_list_search',['type'=>':type'])}}?keyword=:keyword&open_status=:status"/>
        <input type="hidden" id="count_url" value="{{route('company.job_list_count',['type'=>':type'])}}?keyword=:keyword&open_status=:status"/>

        <div class="row">
            <div class="col-md-12">
                <div class="bgc-white bd bdrs-3 p-20 mB-20">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <div class="form-check" style="margin: 5px"><label class="form-label form-check-label"><input class="form-check-input" type="checkbox" id="chk_1" checked>人材紹介の求人</label></div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="form-check" style="margin: 5px"><label class="form-label form-check-label"><input class="form-check-input" type="checkbox" id="chk_2" checked>業務委託案件</label></div>
                        </div>

                        <div class="col-md-2 text-center">
                            <input type="text" class="form-control" id="keyword">
                        </div>

                        <div class="col-md-1 text-center">
                            <label class="form-label" for="searchStatus" style="margin-top: 5px; font-weight: bolder">ステータス</label>
                        </div>
                        <div class="col-md-2 text-center">
                            <select class="form-control" id="searchStatus">
                                <option value="0">全て</option>
                                <option value="1">公開中</option>
                                <option value="2">申請中</option>
                                <option value="3">募集停止中</option>
                                <option value="4">下書き</option>
                            </select>
                        </div>



                        <div class="col-md-3 text-center">
                            <button class="btn cur-p btn-primary btn-color" id="search_button">&nbsp;&nbsp;&nbsp;検索&nbsp;&nbsp;&nbsp;</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="bgc-white bd bdrs-3 p-20 mB-20">

                    <div class="c-grey-900 mB-20" id="count-div">
                        @foreach($count_open_status as $id => $count)
                            <span @if($id==0) style="font-weight: bold" @endif class="search_status" data-id="{{ $id }}">{{ isset($str_open_status[$id]) ? $str_open_status[$id] : '' }}&nbsp;{{ $count }}&nbsp;&nbsp;</span>
                        @endforeach
                    </div>

                    <table id="dataTable2" class="table table-striped table-bordered dataTable" cellspacing="0" width="100%" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
                        <thead>
                        <tr role="row">
                            <th class="dt-center dv-center">求人／案件</th>
                            <th>公開状況</th>
                            <th class="dt-center">更新日<br>(作成日)</th>
                            <th>選考中</th>
                            <th style="border-right: 0"></th>
                            <th style="border-left: 0"></th>

                        </tr>
                        </thead>
                        <tbody>
                        @foreach($result as $ret)
                            <tr role="row">
                                <td>
                                    <div class="recruit_title_td">
                                        <div class="left">
                                            <img src="{{ $ret['image_main'] }}"/>
                                        </div>
                                        <div class="right">
                                            <p class="title">{{ $ret['title'] }}</p>
                                            <p>{{ $ret['full_category'] }}</p>
                                            <p>{{ $ret['type'] }} 求人ID：{{ $ret['id_text'] }}</p>
                                        </div>
                                    </div>

                                </td>
                                <td class="sorting_1">
                                    {{ $ret['open_status_text'] }}

                                    @if($ret['open_status'] == 3 || $ret['open_status'] == 4)
                                        @if($ret['t'] == 'J')
                                        <br><a href="#" data-bs-toggle="modal" data-bs-target="#recruitPublicModal" data-id="{{ $ret['id'] }}" data-type="{{ $ret['t'] }}" data-title="{{ $ret['title'] }}" data-full_category="{{ $ret['full_category'] }}" data-method="{{ $ret['method'] }}" data-income="{{ $ret['income'] }}" data-ideal_income="{{ $ret['ideal_income'] }}" data-refund="{{
                                        $ret['refund'] }}" data-fixed_reward ="{{ $ret['fixed_reward'] }}" class="recruitPublicModal">公開</a>
                                        @elseif($ret['t'] == 'G')
                                        <br><a href="#" data-bs-toggle="modal" data-bs-target="#outsourcePublicModal" data-id="{{ $ret['id'] }}" data-type="{{ $ret['t'] }}" data-title="{{ $ret['title'] }}" data-full_category="{{ $ret['full_category'] }}" data-unit_price="{{ $ret['unit_price'] }}" class="outsourcePublicModal">公開</a>
                                        @endif
                                    @elseif($ret['open_status'] == 1)
                                        <br><a href="#" data-bs-toggle="modal" data-bs-target="#stopModal" class="stopRecruit" data-id="{{ $ret['id'] }}" data-type="{{ $ret['t'] }}">募集停止</a>
                                    @endif
                                </td>
                                <td>
                                    <p>{{ $ret['updated_at_date'] }}</p>
                                    (<span>{{ $ret['created_at_date'] }}作成</span>)
                                </td>
                                <td>{{ $ret['selection_count'] }}</td>
                                <td>
                                    <a href="{{ route('company.job_edit', ['type' => $ret['t'], 'id' => $ret['id']]) }}" class="job_list_edit @if($ret['open_status'] == 2) job_list_disabled @endif">編集</a>

                                </td>
                                <td><a href="{{ route('company.job_show', ['type' => $ret['t'], 'id' => $ret['id']]) }}">表示</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('partials.recruit_public_modal')
    @include('partials.outsource_public_modal')

    <div class="modal fade" id="stopModal" tabindex="-1" aria-labelledby="stopModalLabel" aria-hidden="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="stopModalLabel">募集停止</h5>
                </div>
                <div class="modal-body">本当に募集停止しますか。</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <input type="hidden" id="stopRecruitId" />
                    <input type="hidden" id="stopType" />
                    <button type="button" class="btn btn-primary" onclick="stopFunc();">募集停止する</button>
                </div>
            </div>
        </div>
    </div>

@endsection





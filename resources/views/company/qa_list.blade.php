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
            求人や案件情報に対するQ&A（質問と回答）
        </h3>

        <input type="hidden" id="search_url" value="{{route('company.qa_list_search')}}?sort=:sort&type=:type&status=:status"/>
        <input type="hidden" id="count_url" value="{{route('company.qa_list_count')}}?sort=:sort&type=:type&status=:status"/>

        <div class="row">
            <div class="col-md-12">
                <div class="bgc-white bd bdrs-3 p-20 mB-20">
                    <div class="row mB-20 mT-20">
                        <div class="col-md-3">
                            <div class="c-grey-900 mB-20" id="count-div">
                                @foreach($count_question_status as $id => $count)
                                    <span class="qa_search_status @if($id==0) qa_search_status_selected @endif" data-id="{{ $id }}">{{ isset($str_question_status[$id]) ? $str_question_status[$id] : '' }}&nbsp;{{ $count }}&nbsp;&nbsp;</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-3"></div>
                        <div class="col-md-1 text-center"><label class="form-label" for="qaSearchSort" style="margin-top: 5px; font-weight: bolder">並び替え</label></div>

                        <div class="col-md-2">
                            <select class="form-control" id="qaSearchSort">
                                <option value="0">投稿日（新しい順）</option>
                                <option value="1">投稿日（古い順）</option>
                                <option value="2">回答日（新しい順）</option>
                                <option value="3">回答日（古い順）</option>
                            </select>
                        </div>

                        <div class="col-md-1 text-center"><label class="form-label" for="qaSearchType" style="margin-top: 5px; font-weight: bolder">質問の種類</label></div>
                        <div class="col-md-2">

                            <select class="form-control" id="qaSearchType">
                                <option value="0">全て</option>
                                <option value="2">求人情報に関する質問</option>
                                <option value="3">業務委託案件に関する質問</option>
                                <option value="1">企業に関する質問</option>
                            </select>
                        </div>
                    </div>



                    <div style="clear: both"></div>
                    <table id="dataTable3" class="table table-striped dataTable" cellspacing="0" width="100%" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
                        <thead style="display: none">
                        <tr role="row"><th></th></tr>
                        </thead>
                        <tbody>
                        @foreach($result as $ret)
                            <tr role="row">
                                <td>
                                    <div id="td-{{ $ret['id'] }}">
                                        <div class="qa_table_cell_container">

                                        <div class="qa_table_accordion">
                                            <input type="checkbox" @if($ret['status'] == 2) checked @endif>
                                            <i class="arrow"></i>

                                            <div class="qa_title">
                                                <div class="qa_title_status">
                                                    <div class="qa_title_status_panel @if($ret['status'] == 1) qa_title_status_panel_waiting @else qa_title_status_panel_finish @endif">{{ $str_question_status[$ret['status']] ? $str_question_status[$ret['status']] : '' }}</div>
                                                    <div class="qa_title_status_text">{{ $ret['ago'] }}</div>
                                                </div>
                                                <div class="qa_title_container">
                                                    <p class="qa_title_type">【{{ $ret['offer_info_type_name'] }}】</p>
                                                    <p class="qa_title_title">{{ $ret['question_title'] }}</p>
                                                    <p class="qa_title_job_title">
                                                        <a href="{{ route('company.job_qa', ['type' => $ret['job_type'], 'id' => $ret['job_id']]) }}">
                                                            <i class="ti-pencil-alt"></i>
                                                            {{ $ret['job_title'] }}
                                                        </a>
                                                    </p>
                                                </div>

                                            </div>

                                            @if($ret['status'] == 2)
                                                <div class="qa_contents">
                                                    <div class="qa_contents_item">
                                                        <div class="qa_contents_item_logo">
                                                            <img src="{{ $ret['question_logo'] }}" onerror="onImgErr(this)">
                                                        </div>
                                                        <div class="qa_contents_item_container">
                                                            <p class="qa_contents_item_company_title">{{ $ret['question_name'] }}</p>
                                                            <p class="qa_contents_item_datetime">投稿日：{{ date('Y.m.d H:i', strtotime($ret['question_datetime'])) }}</p>
                                                            <span class="qa_contents_item_status">{{ $ret['question_type_name'] }}</span>
                                                            <p class="qa_contents_item_text">
                                                                <?php echo nl2br($ret['question_content']) ?>
                                                            </p>

                                                        </div>
                                                    </div>

                                                    <div class="qa_contents_item qa_contents_even">
                                                        <div class="qa_contents_item_logo">
                                                            <img src="{{ $ret['answer_logo'] }}" onerror="onImgErr(this)">
                                                        </div>
                                                        <div class="qa_contents_item_container qa_answer_item_container">
                                                            <p class="qa_contents_item_company_title">{{ $ret['answer_name'] }}</p>
                                                            <p class="qa_contents_item_datetime qa_answer_item_datetime">回答日：{{ $ret['answer_datetime'] ? date('Y.m.d H:i', strtotime($ret['answer_datetime'])) : 'ー' }}</p>
                                                            <p class="qa_contents_item_text">
                                                                <?php echo nl2br($ret['answer_content']) ?>
                                                            </p>

                                                        </div>
                                                    </div>

                                                </div>
                                            @elseif($ret['status'] == 1)
                                                <div class="qa_contents">
                                                    <div class="qa_contents_item">
                                                        <div class="qa_contents_item_logo">
                                                            <img src="{{ $ret['question_logo'] }}" onerror="onImgErr(this)">
                                                        </div>
                                                        <div class="qa_contents_item_container">
                                                            <p class="qa_contents_item_company_title">{{ $ret['question_name'] }}</p>
                                                            <p class="qa_contents_item_datetime">投稿日：{{ date('Y.m.d H:i', strtotime($ret['question_datetime'])) }}</p>
                                                            <span class="qa_contents_item_status">{{ $ret['question_type_name'] }}</span>
                                                            <p class="qa_contents_item_text">
                                                                <?php echo nl2br($ret['question_content']) ?>
                                                            </p>

                                                        </div>
                                                    </div>

                                                    <div class="qa_contents_item qa_contents_even">
                                                        <div class="qa_contents_item_logo">
                                                            <img src="{{ $ret['answer_logo'] }}" onerror="onImgErr(this)">
                                                        </div>
                                                        <div class="qa_contents_item_container qa_answer_item_container">
                                                            <p class="qa_contents_item_company_title">{{ $ret['answer_name'] }}</p>
                                                            <p class="qa_contents_item_datetime qa_answer_item_datetime">回答日：ー</p>
                                                        </div>

                                                        @if($ret['reject'] == 1)
                                                            <div class="qa_contents_item_warning">
                                                                <span class="qa_contents_item_warning_symbol">!</span>
                                                                質問の回答をお願い致します。
                                                            </div>

                                                            <div class="qa_contents_item_input">
                                                                <textarea class="form-control answer_content" rows="3" onkeyup="countChar(this)"></textarea>
                                                                <div class="qa_contents_item_text_count"><span class="qa_contents_item_text_count_span">0</span>/2000</div>

                                                                <div class="qa_contents_item_input_label">・他エージェントにも公開されるため、「会社名」「個人名」「電話番号」、その他個人情報の記載はお控えください。</div>

                                                                <div class="qa_contents_button_container text-center">
{{--                                                                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#answerModal" data-id="{{ $ret['id'] }}" data-answer_person_id="{{ $answer_person_id }}" class="answerModal qa_contents_button">送信する</a>--}}
                                                                    <a href="javascript:void(0)" data-id="{{ $ret['id'] }}" data-answer_person_id="{{ $answer_person_id }}" class="answerModal qa_contents_button">送信する</a>
                                                                </div>
                                                                <div class="qa_contents_reject_container">
                                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#rejectQuestionModal" data-id="{{ $ret['id'] }}" class="rejectQuestionModal">質問への回答を拒否する</a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>

                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>


        <div class="modal fade" id="answerModal" tabindex="-1" aria-labelledby="answerModalLabel" aria-hidden="true" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="answerModalLabel">回答する</h5>
                    </div>
                    <div class="modal-body">回答しますか。</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <input type="hidden" id="answerId" />
                        <input type="hidden" id="answerPersonId" />
                        <input type="hidden" id="answerContent" />
                        <button type="button" class="btn btn-primary" onclick="answerFunc();">送信する</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="rejectQuestionModal" tabindex="-1" aria-labelledby="rejectQuestionModalLabel" aria-hidden="true" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="rejectQuestionModalLabel">質問への回答を拒否</h5>
                    </div>
                    <div class="modal-body">質問への回答を拒否しますか。</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <input type="hidden" id="rejectQuestionId" />
                        <button type="button" class="btn btn-primary" onclick="rejectFunc();">拒否する</button>
                    </div>
                </div>
            </div>
        </div>

    </div>


@endsection








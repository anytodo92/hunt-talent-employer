<template>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 1">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    {{ selectionText }}の結果を選択してください。<br>
                    {{ outsourceApplyMgt.last_selection_flow_number == 3 ? '選考' : '面談' }}から{{ pastDays }}日が経過しています。
                </p>
            </div>
            <p class="text">条件交渉が必要な場合はタイムラインのメッセージ上で交渉してください。</p>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showNotAdoptedModal()"><b>見送り</b></button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showPassSelectionModal()" v-show="NextSelections.length > 0">選考通過<br/>次の選考へ</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmHireModal()">契約する</button>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 2">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">{{ selectionText }}の候補日、及び面談詳細を設定してください。</p>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRequireScheduleModal()">候補者に候補日を提示してもらう</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="linkCalendar(2)">候補日を提示する</button>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 3">
        <div v-if="InterviewSchedules.length == 0" class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    候補者からの日程調整の入力待ちです。<br>
                    日程が届くまで今しばらくお待ちください。
                </p>
            </div>
        </div>
        <div v-else class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    候補者から日程が届きました。<br>
                    確認して日程を確定してください。
                </p>
            </div>
            <div>
                <p class="text pY-10 pL-10">
                    <span class="icon-holder"><i class="ti-calendar"></i> </span>
                    <span class="pL-10">提示された日程候補</span>
                </p>
                <p class="text pL-30" style="color: #2B7CBF;">
                    <div v-for="(schedule, index) in InterviewSchedules" v-html="formatInterviewDate(schedule.interview_candidates_date, schedule.interview_candidates_from, schedule.interview_candidates_to, schedule.interview_date_type)" />
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-primary" @click="linkCalendar(1)">日程を選択する</button>
                </div>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 4">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    候補者へ提示した日程調整の回答待ちです。<br>
                    回答が届くまで今しばらくお待ちください。
                </p>
            </div>
            <div v-show="InterviewSchedules.length > 0">
                <p class="text pY-10 pL-10">
                    <span class="icon-holder"><i class="ti-calendar"></i> </span>
                    <span class="pL-10">提示した日程候補</span>
                </p>
                <p class="text pL-30" style="color: #2B7CBF;">
                    <div v-for="(schedule, index) in InterviewSchedules" v-html="formatInterviewDate(schedule.interview_candidates_date, schedule.interview_candidates_from, schedule.interview_candidates_to, schedule.interview_date_type)" />
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <a class="link pL-30" style="cursor:pointer;font-weight:bold" :href="calendarLink">▶カレンダー(予定表)を確認する</a>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 6">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">{{ selectionText }}の詳細および日程について設定してください。</p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-primary" @click="showInterviewModal()">{{ selectionText }}詳細と日程の設定</button>
                </div>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 7">
        <div class="card-body">
            <div class="prompt prompt--success">
                <p class="icon">!</p>
                <p class="text">
                    以下の日程で{{ selectionText }}の日時が確定しました。<br/>
                    <em>{{ confirmedDate }}</em><br/>
                    <div class="mT-10">※変更する場合はタイムラインより業務委託会社へご連絡をお願い致します。</div>
                </p>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 8">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    オファー（内定）を送信しました。<br>
                    ご契約条件の提示をお願い致します。
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-primary" @click="showJoinConditionModal()">契約条件を提示する</button>
                </div>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 9">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    以下の契約条件を提示しています。<br>
                    条件を交渉する場合はタイムラインのチャットから行っていただき、こちらから変更をお願い致します。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">提示した契約条件</span>
                </p>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">単価（円）</p>
                <div class="jcondition_detail_content">
                    <p> {{ this.contractTerms.length > 0 ? this.$enumUnitPrices[this.contractTerms[0].unit_price] + ' ' + formatDigits(this.contractTerms[0].unit_price_amount) : 0 }} 円</p>
                </div>
            </div>
            <div class="row mX-20 mY-5" v-show="this.contractTerms.length > 0 && this.contractTerms[0].unit_price == 1">
                <p class="jcondition_detail_title">清算時間（月）</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.contractTerms.length > 0 ? (this.contractTerms[0].pay_off_start) + '～' + (this.contractTerms[0].pay_off_end) : '' }} 時間</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">想定稼働日数/週</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.contractTerms.length > 0 ? this.contractTerms[0].estimated_working_days_week : '' }} 日</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">特記事項（その他条件）</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.contractTerms.length > 0 ? this.contractTerms[0].special_notes : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">参画開始日</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.contractTerms.length > 0 ? formatDate(this.contractTerms[0].joining_start_date) : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">返答期限</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.contractTerms.length > 0 ? formatDate(this.contractTerms[0].reply_deadline) : '' }}</p>
                </div>
            </div>
            <div class="row mT-20 pX-20 fRight">
                <a class="link pL-10 cur-p bold fRight" @click="showJoinConditionModal()">▶契約条件を変更する</a>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 10">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    以下の契約条件を提示しています。<br>
                    条件を交渉する場合はタイムラインのチャットから行っていただき、こちらから変更をお願い致します。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">前回提示した契約条件と変更後の契約条件</span>
                </p>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">単価（円）</p>
                <div class="jcondition_detail_content" v-if="this.contractTerms.length > 1 && this.contractTerms[0].unit_price_amount != this.contractTerms[1].unit_price_amount">
                    <p>＜変更前＞ {{ this.$enumUnitPrices[this.contractTerms[1].unit_price] + ' ' + formatDigits(this.contractTerms[1].unit_price_amount) }} 円</p>
                    <p class="text-primary">＜変更後＞ {{ this.$enumUnitPrices[this.contractTerms[0].unit_price] + ' ' + formatDigits(this.contractTerms[0].unit_price_amount) }} 円</p>
                </div>
                <div class="jcondition_detail_content" v-else>
                    <p> {{ this.contractTerms.length > 0 ? this.$enumUnitPrices[this.contractTerms[0].unit_price] + ' ' + formatDigits(this.contractTerms[0].unit_price_amount) : 0 }} 円</p>
                </div>
            </div>
            <div class="row mX-20 mY-5" v-show="this.contractTerms.length > 0 && this.contractTerms[0].unit_price == 1">
                <p class="jcondition_detail_title">清算時間（月）</p>
                <div class="jcondition_detail_content" v-if="this.contractTerms.length > 1 && this.contractTerms[0].pay_off_start != this.contractTerms[1].pay_off_start">
                    <p>＜変更前＞ {{ (this.contractTerms[1].pay_off_start) + '～' + (this.contractTerms[1].pay_off_end) }} 時間</p>
                    <p class="text-primary">＜変更後＞ {{ (this.contractTerms[0].pay_off_start) + '～' + (this.contractTerms[0].pay_off_end) }} 時間</p>
                </div>
                <div class="jcondition_detail_content" v-else>
                    <p>{{ this.contractTerms.length > 0 ? (this.contractTerms[0].pay_off_start) + '～' + (this.contractTerms[0].pay_off_end) : '' }} 時間</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">想定稼働日数/週</p>
                <div class="jcondition_detail_content" v-if="this.contractTerms.length > 1 && this.contractTerms[0].estimated_working_days_week != this.contractTerms[1].estimated_working_days_week">
                    <p>＜変更前＞ {{ this.contractTerms[1].estimated_working_days_week }} 日</p>
                    <p class="text-primary">＜変更後＞ {{ this.contractTerms[0].estimated_working_days_week }} 日</p>
                </div>
                <div class="jcondition_detail_content" v-else>
                    <p>{{ this.contractTerms.length > 0 ? this.contractTerms[0].estimated_working_days_week : '' }} 日</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">特記事項（その他条件）</p>
                <div class="jcondition_detail_content" v-if="this.contractTerms.length > 1 && this.contractTerms[0].special_notes != this.contractTerms[1].special_notes">
                    <p>＜変更前＞ {{ this.contractTerms[1].special_notes }}</p>
                    <p class="text-primary">＜変更後＞ {{ this.contractTerms[0].special_notes }}</p>
                </div>
                <div class="jcondition_detail_content" v-else>
                    <p>{{ this.contractTerms.length > 0 ? this.contractTerms[0].special_notes : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">参画開始日</p>
                <div class="jcondition_detail_content" v-if="this.contractTerms.length > 1 && this.contractTerms[0].joining_start_date != this.contractTerms[1].joining_start_date">
                    <p>＜変更前＞ {{ formatDate(this.contractTerms[1].joining_start_date) }}</p>
                    <p class="text-primary">＜変更後＞ {{ formatDate(this.contractTerms[0].joining_start_date) }}</p>
                </div>
                <div class="jcondition_detail_content" v-else>
                    <p>{{ this.contractTerms.length > 0 ? formatDate(this.contractTerms[0].joining_start_date) : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">返答期限</p>
                <div class="jcondition_detail_content" v-if="this.contractTerms.length > 1 && this.contractTerms[0].reply_deadline != this.contractTerms[1].reply_deadline">
                    <p>＜変更前＞ {{ formatDate(this.contractTerms[1].reply_deadline) }}</p>
                    <p class="text-primary">＜変更後＞ {{ formatDate(this.contractTerms[0].reply_deadline) }}</p>
                </div>
                <div class="jcondition_detail_content" v-else>
                    <p>{{ this.contractTerms.length > 0 ? formatDate(this.contractTerms[0].reply_deadline) : '' }}</p>
                </div>
            </div>
            <div class="row mT-20 pX-20 fRight">
                <a class="link pL-10 cur-p bold fRight" @click="showJoinConditionModal()">▶契約条件を変更する</a>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 12">
        <div class="card-body">
            <div class="prompt prompt--success">
                <p class="icon orange no-background pX-0">〇</p>
                <p class="text mY-0">
                    <em>契約成立</em><br>
                    <span>参画開始日：{{ confirmedDate }}</span>
                </p>
            </div>
            <div class="row mT-20 pX-20 fRight">
                <a class="link pL-10 cur-p bold fRight" @click="showChangeStartDateModal()">▶参画開始日変更</a>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 13">
        <div class="card-body">
            <div class="prompt prompt--success">
                <p class="icon orange no-background pX-0">〇</p>
                <p class="text mY-5">
                    <em>参画中</em><br>
                    <span>参画開始日：{{ confirmedDate }}</span>
                </p>
            </div>
            <div class="row mT-20 pX-20 fRight">
                <a class="link pL-10 cur-p bold fRight" @click="showFinishContractModal()">▶参画終了申請</a>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 14">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="icon">!</p>
                <p class="text mY-10">
                    以下の日程で参画終了となります。<br>
                    修正または継続する場合は申請の取消をしてください。<br>
                    <b>{{ confirmedDate }}</b>
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-primary" @click="showRejectFinishModal()">参画終了申請の取消</button>
                </div>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 15">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="icon">!</p>
                <p class="text mY-10">
                    業務委託/SES企業様より以下の日程で「参画終了申請」が届いています。<br>
                    確認されましたら確認済みボタンの押下をお願いします。<br>
                    （求人企業様への確認や連絡事項がある場合はタイムラインのチャットよりご連絡をお願い致します）<br>
                    <span class="red">※確認済みボタンを押下しない場合でも日付経過後には自動的に参画終了となりますのでご注意ください。</span><br>
                    <b>{{ confirmedDate }}</b>
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-primary" @click="showConfirmFinishModal()">確認済み</button>
                </div>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 16">
        <div class="card-body">
            <div class="prompt prompt--reverse">
                <p class="icon">!</p>
                <p class="text mY-5">
                    <em>参画終了</em><br>
                    <span>終了日：{{ confirmedDate }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- begin modal -->
    <div class="modal fade" id="notAdoptedReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header no-border pB-0">
                    <h5 class="modal-title pT-20 w-100 text-sm-center">お見送りの最も大きい理由を１つ選択してください。<span class="badge">必須</span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pX-40 pY-10">

                    <div class="sub-content mT-0">
                        <div class="row sp_block mX-0">
                            <div v-for="(reason, index) in this.$enumOutsourceUnseatedReasons" class="col col-md-3 pX-0">
                                <div class="radio-container">
                                    <input class="" type="radio" name="unseated_reason" :id="'radio1-'+index" :value="index" @click="changeNotAdoptedReason(index)">
                                    <label class="form-check-label" :for="'radio1-'+index" style="white-space: break-spaces;">
                                        {{ reason }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sub-content mT-20" v-show="String(unseated_reason).length != 0">
                        <div class="text mX-0 mB-0">
                            <div class="modal-title modal-title--sub">{{ unseated_reason_text }}について当てはまるお見送り理由を1つ選択してください。<span class="badge">必須</span></div>
                        </div>
                        <div class="row sp_block mX-0">
                            <div v-for="(detail, index) in NotAdoptedDetailReasons" class="col col-md-3 pX-0">
                                <div class="radio-container">
                                    <input class="" type="radio" name="unseated_reason_sub" :id="'radio2-'+index" :value="index">
                                    <label class="form-check-label" :for="'radio2-'+index" style="white-space: break-spaces;">
                                        {{ detail }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="text mT-20 mX-10 mB-0 pB-5">お見送り理由の詳細があれば記入してください</div>
                        <textarea id="unseated_cause_detail" class="textarea box-shadow mX-0 p-10" rows="3" ></textarea>
                    </div>

                    <p class="text mT-10 mB-0">ご入力いただいた内容は、案件内容の改善や候補者とのミスマッチ防止に利用いたします。</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="col btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="col btn btn-primary" @click="submitNotAdoptedReason()">送信</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passSelectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="icon-holder"><i class="ti-check-box"></i> </span>
                    <h5 class="mL-10 modal-title">選考結果「選考通過」</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            次回の選考フェーズ<span class="badge">必須</span>
                        </div>
                        <select name="next_phase" class="box-shadow col-md-4 p-10">
                            <option value="">選択する</option>
                            <option v-for="selection in NextSelections" :value="selection.id">{{ selection.text }}</option>
                        </select>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            現状の評価<span class="badge">必須</span>
                        </div>
                        <select name="current_evaluation" class="box-shadow col-md-4 p-10">
                            <option value="">選択する</option>
                            <option v-for="(evaluation, index) in this.$enumOutsourceEvaluations" :value="index">{{ evaluation }}</option>
                        </select>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            評価点<span class="badge">必須</span>
                        </div>
                        <textarea name="evaluation_point" class="box-shadow col-md-8 p-10" rows="3"></textarea>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            懸念点<span class="badge">必須</span>
                        </div>
                        <textarea name="concern_point" class="box-shadow col-md-8 p-10" rows="3"></textarea>
                    </div>
                    <p class="text m-0 float-right ta-l dialog-comment">
                        紹介料： 無料<br/>
                        採用事務手数料： 無料<br/>
                        <span class="red">※エージェント及びフリーランス等との直接契約はできません。<br/>
                        inCulエージェント上での契約となります。
                        </span>
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="submitPassSelection()">確定して次へ進む</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmHireModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    選考状況を「オファー」に変更します。宜しいですか？
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="submitHire()">契約する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="interviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="icon-holder"><i class="ti-write"></i> </span>
                    <h5 class="mL-10 modal-title">{{ selectionText }}詳細の入力</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <div class="row mX-20 mY-10">
                        <div class="col-md-5 pL-0 pY-5">
                            {{ selectionText }}担当者名<span class="badge">必須</span>
                        </div>
                        <input class="box-shadow col-md-5 p-10" type="text" id="interviewer">
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-5 pL-0 pY-5">
                            {{ selectionText }}場所住所<span class="badge">必須</span>
                        </div>
                        <input class="box-shadow col-md-5 p-10" type="text" id="interview_address">
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-5 pL-0 pY-5">
                            持ち物<span class="badge">必須</span>
                        </div>
                        <input class="box-shadow col-md-5 p-10" type="text" id="belongings">
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-5 pL-0 pY-5">
                            緊急連絡先（参画者様のみご利用可）<span class="badge">必須</span>
                        </div>
                        <input class="box-shadow col-md-5 p-10" type="text" id="emergency_contact_address">
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-5 pL-0 pY-5">
                            その他特記事項<span class="badge blue">任意</span>
                        </div>
                        <textarea class="box-shadow col-md-7 p-10" rows="3" id="else_special_note"></textarea>
                    </div>
                </div>

                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-outline-secondary" @click="sendInterviewDetail(true)">面談詳細を登録して候補者に日程候補日を提示してもらう</button>
                    <button type="button" class="btn btn-primary" @click="sendInterviewDetail(false)">面談詳細を登録して日程を提示する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="requireScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header justify-content-start">
                    <span class="icon-holder"><i class="ti-calendar"></i> </span>
                    <h5 class="mL-10 modal-title">候補者に日程の希望日を提示してもらいます。</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    依頼すると、先に候補者より希望の日程を提示してもらい、その候補日の中から選んで日程を決めることができます。
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="sendInterviewSettingPersonType(true)">依頼する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="joiningConditionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <h5 class="mL-10 modal-title">契約する（条件を入力）</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <p class="mB-0">※交渉した場合はその結果をご入力ください。</p>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            単価（円）<span class="badge">必須</span>
                        </div>
                        <label class="col-md-8 pL-0">
                            <select name="unit_price" class="box-shadow col-md-3 p-10 mB-5 mR-5" @change="changeUnitPrice()">
                                <option value="">選択する</option>
                                <option v-for="(unit_price, index) in this.$enumUnitPrices" :value="index">{{ unit_price }}</option>
                            </select>
                            <input class="box-shadow col-md-5 p-10 hide-arrow" id="unit_price_amount" type="number" min="0" oninput="validity.valid||(value='');">円<br>
                            <span class="form-text mT-0">表示されている金額は候補者からの提案単価です。<br>
                            この単価よりも低くなると辞退される可能性が高くなりますのでご注意ください。</span>
                        </label>
                    </div>
                    <div class="row mX-20 mY-10" v-show="this.unit_price==1">
                        <div class="col-md-4 pL-0 pY-5">
                            清算時間（月）<span class="badge">必須</span>
                        </div>
                        <label class="col-md-8 pL-0">
                            <input class="box-shadow col-md-3 p-10" id="pay_off_start" type="text" autocomplete="off"> ～
                            <input class="box-shadow col-md-3 p-10" id="pay_off_end" type="text" autocomplete="off"> 時間
                        </label>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            想定稼働日数/週<span class="badge">必須</span>
                        </div>
                        <label class="col-md-8 pL-0">
                            週 <input class="box-shadow col-md-5 p-10" id="estimated_working_days_week" type="text" autocomplete="off"> 日
                        </label>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            特記事項（その他条件）
                        </div>
                        <textarea name="special_notes" class="box-shadow col-md-8 p-10" rows="3"></textarea>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            参画開始日<span class="badge">必須</span>
                        </div>
                        <div class="col-md-4 pL-0">
                            <input type="text" class="box-shadow col-md-12 p-10" id="joining_start_date" autocomplete="off" data-provide="datepicker">
                            <i class="ti-calendar place"></i>
                        </div>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            返答期限<span class="badge">必須</span>
                        </div>
                        <div class="col-md-4 pL-0">
                            <input type="text" class="box-shadow col-md-12 p-10" id="reply_deadline" autocomplete="off" data-provide="datepicker">
                            <i class="ti-calendar place"></i>
                        </div>
                    </div>
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="col btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="col btn btn-primary" @click="sendJoiningCondition()">送信</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeStartDateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header justify-content-start">
                    <span class="icon-holder"><i class="ti-calendar"></i> </span>
                    <h5 class="mL-10 modal-title">参画開始日の変更</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            参画開始日<span class="badge">必須</span>
                        </div>
                        <div class="col-md-5 pL-0">
                            <input type="text" class="box-shadow col-md-12 p-10" id="joining_scheduled_date" autocomplete="off" data-provide="datepicker">
                            <i class="ti-calendar place"></i>
                        </div>
                    </div>
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="col btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="col btn btn-primary" @click="sendChangeStartDate()">変更する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="finishContractModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="icon-holder"><i class="ti-write"></i> </span>
                    <h5 class="mL-10 modal-title">参画終了申請</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <p class="text ta-l mY-0">
                        現在参画中の参画者に対して参画終了の通知を行います。<br>
                        送信後、指定した日付で自動的に参画が終了しますのでお間違いのないようご注意ください。
                    </p>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            参画終了日<span class="badge">必須</span>
                        </div>
                        <div class="col-md-5 pL-0">
                            <input type="text" class="box-shadow col-md-12 p-10" id="joining_end_date" autocomplete="off" data-provide="datepicker">
                            <i class="ti-calendar place"></i>
                        </div>
                    </div>
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="col btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="col btn btn-primary" @click="sendRequestFinish()">送信</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmFinishModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    参画終了申請を同意します。宜しいですか？
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="sendConfirmFinishContract(true)">確認</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectFinishModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    参画終了申請を取消します。宜しいですか？
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="sendConfirmFinishContract(false)">確認</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end modal -->

</template>

<script>
import {useStore, mapState} from "vuex";
import $ from 'jquery';
import store from '../store';
import moment from 'moment-timezone'
moment.tz.setDefault('Asia/Tokyo')

let PromptEnum = { 1 : 'alert', 2: 'warning', 3 : 'success' };

//
$(function() {
});

export default {
    name: 'NextOperation',
    props: {
        serverTime: String,
        selectionFlow: String
    },
    setup() {
    },
    data() {
        return {
            nextOpType: 1,
            currentStep: 1,

            unit_price: 0,
            unseated_reason: '',
            unseated_reason_text: '',
            NotAdoptedDetailReasons: [],
        }
    },
    created() {

    },
    computed: {
        ...mapState({
            // arrow functions can make the code very succinct!
            //count: state => state.count,

            // passing the string value 'count' is same as `state => state.count`
            //countAlias: 'count',

            // to access local state with `this`, a normal function must be used
            //countPlusLocalState (state) {
            //    return state.count + this.localCount
            //}
            outsourceApplyMgt : state => state.outsourceApplyMgt,
            selectionResults : state => state.selectionResults,
            timelines : state => state.timelines,
            interviewSchedules : state => state.interviewSchedules,
            contractTerms : state => state.contractTerms,
        }),
        pastDays() {
            var current = new Date(this.serverTime);

            let currentStep = this.outsourceApplyMgt.last_selection_flow_number;
            const resultKey = this.$enumOutsourceApplyMgtSelectionResultKeys[currentStep];
            if (resultKey == null) {
                console.warn('NextOperation.vue/pastDays() currentStep is out of range.');
                return 0;
            }

            const selection_date = this.outsourceApplyMgt[resultKey+'_date'];
            if (selection_date == null) {
                console.warn('NextOperation.vue/pastDays() resultKey is out of range.');
                return 0;
            }
            var lastday = new Date(selection_date);

            return Math.ceil(Math.abs(current-lastday) / (1000*60*60*24)).toFixed(0);
        },
        selectionText() {
            let currentStep = this.outsourceApplyMgt.last_selection_flow_number;
            const result = this.$enumOutsourceSelectionFlows[currentStep];
            if (result == null) {
                console.warn('NextOperation.vue/selectionText() currentStep is out of range.');
                return '〇〇〇';
            }

            return result;
        },
        confirmedDate() {
            let currentStep = this.outsourceApplyMgt.last_selection_flow_number;
            const resultKey = this.$enumOutsourceApplyMgtSelectionResultKeys[currentStep];
            if (resultKey == null) {
                console.warn('NextOperation.vue/confirmedDate() currentStep is out of range.');
                return '';
            }

            let selection_date = '';
            const result = this.outsourceApplyMgt[resultKey];
            if (currentStep == 8) { // 契約
                if (result == 1/*1:参画予定日*/) {
                    selection_date = moment(new Date(this.outsourceApplyMgt.joining_scheduled_date)).format('YYYY年M月D日');
                }
                else if (result == 2/*2:契約条件提示・交渉（！要対応）*/ || result == 3/*3:契約条件同意待ち*/) {
                    if (this.contractTerms.length > 0) {
                        selection_date = moment(new Date(this.contractTerms[0].joining_start_date)).format('YYYY年M月D日');
                    }
                }
                return selection_date;
            }
            else if (currentStep == 9) { // 参画確認
                selection_date = moment(new Date(this.outsourceApplyMgt.joining_confirmation_start_date)).format('YYYY年M月D日');
                return selection_date;
            }

            else if (currentStep == 10) { // 現況
                if (this.outsourceApplyMgt.joining_end_date != null) {
                    selection_date = moment(new Date(this.outsourceApplyMgt.joining_end_date)).format('YYYY年M月D日');
                }
                else {
                    selection_date = moment(new Date(this.outsourceApplyMgt.joining_confirmation_start_date)).format('YYYY年M月D日');
                }
                return selection_date;
            }

            selection_date = this.outsourceApplyMgt[resultKey+'_date'];
            if (selection_date == null || selection_date == '') {
                return '';
            }
            let from_time = '00:00';
            let to_time = '00:00';
            if (currentStep <= 7) {
                selection_date = moment(new Date(selection_date)).format('YYYY年M月D日');

                for (var i=0; i<this.interviewSchedules.length; i++) {
                    if (this.interviewSchedules[i].interview_phase == currentStep && this.interviewSchedules[i].interview_date_type == 2) { // 2:確定した日(=◯)
                        let formated = moment(new Date(this.interviewSchedules[i].interview_candidates_date)).format('YYYY/M/D');
                        from_time = moment(new Date(formated + ' ' + this.interviewSchedules[i].interview_candidates_from)).format('HH:mm');
                        to_time = moment(new Date(formated + ' ' + this.interviewSchedules[i].interview_candidates_to)).format('HH:mm');
                        break;
                    }
                }

                selection_date = selection_date + ' ' + from_time + ' ～ ' + to_time;
            }
            else {
                selection_date = moment(new Date(selection_date)).format('YYYY年M月D日');
            }
            if (selection_date == null) {
                console.warn('NextOperation.vue/confirmedDate() resultKey is out of range.');
                return '';
            }

            return selection_date;
        },
        NextSelections() {
            let following_selections = [];
            const steps = this.selectionFlow.split(",");

            for (var i=0; i<steps.length; i++)
            {
                // 成約（オファー）の段階は表示しない。　契約することで「採用」段階に移行する。
                if (parseInt(steps[i]) >= 8) {
                    continue;
                }
                if (parseInt(steps[i]) > parseInt(this.outsourceApplyMgt.last_selection_flow_number))
                {
                    let selection = new Object();
                    selection.id=steps[i];
                    selection.text = this.$enumOutsourceSelectionFlows[selection.id];
                    following_selections.push(selection);
                }
            }
            return following_selections;
        },
        InterviewSchedules() {
            let interview_schedules = [];
            let curStep = this.outsourceApplyMgt.last_selection_flow_number;
            if (this.interviewSchedules == null)
            {
                return interview_schedules;
            }
            for (var i=0; i<this.interviewSchedules.length; i++)
            {
                if (this.interviewSchedules[i].interview_phase == curStep)
                {
                    interview_schedules.push(this.interviewSchedules[i]);
                }
            }
            return interview_schedules;
        },
        // >カレンダー（予定表）を確認する
        calendarLink() {
            let calendar1 = '/company/outsource/calendar1?id='+this.outsourceApplyMgt.id;
            let calendar2 = '/company/outsource/calendar2?id='+this.outsourceApplyMgt.id;

            let current_selection_result = this.getCurrentSelectionResult(this.outsourceApplyMgt.last_selection_flow_number);

            if (current_selection_result == null) {
                return calendar2;
            }

            // 1:求人企業
            if (current_selection_result.interview_setting_person_type == 1) {
                return calendar2;
            }
            // 2:候補者（※人材紹介会社担当者または業務委託会社担当者が管理する候補者）
            else if (current_selection_result.interview_setting_person_type == 2) {
                return calendar1;
            }

            return calendar2;
        },
        nextOpType() {

            let curStep = this.outsourceApplyMgt.last_selection_flow_number;
            let curStatus = this.getCurrentSelectionStatus(curStep);

            // 応募及び書類確認の選考は、詳細画面に表示しない。
            if (curStep == 1 || curStep == 2)
            {
                console.warn('NextOperation.vue/nextOpType() currentStep is unreachable.');
                return -1;
            }

            // 選考結果が送付済みの場合は、「選考結果の選択」ブタンを表示しない。（内定、通過、辞退、見送り）
            if (curStep <= 7/*最終面談*/ && (curStatus == 1 || curStatus == 2 || curStatus == 3 || curStatus == 4))
            {
                console.warn('NextOperation.vue/nextOpType() currentStep is not next-flow.');
                return -1;
            }

            // (1) 選考結果の選択
            if ((curStep == 3/*書類選考*/ && curStatus == 6/*選考結果未送付*/)
                || (curStep >= 4/*1次面談*/ && curStep <= 7/*最終面談*/ && curStatus == 8/*選考結果未送付*/))
            {
                return 1;
            }

            if (curStep >= 4/*1次面談*/ && curStep <= 7/*最終面談*/)
            {
                if (curStatus == 6/*6:日程未確定（！要対応）*/) {
                    let current_selection_result = this.getCurrentSelectionResult(curStep);

                    // (6) 〇〇面談の面談詳細および日程
                    if (current_selection_result == null) {
                        return 6;
                    }

                    if (current_selection_result.interviewer==null || current_selection_result.interview_address==null || current_selection_result.belongings==null || current_selection_result.emergency_contact_address==null) {
                        return 6;
                    }
                    else {
                        // (3) 候補者からの日程調整の入力待ち
                        if (current_selection_result.interview_setting_person_type == 2) {
                            return 3;
                        }
                        // (4) 候補者へ提示した日程調整の回答待ち
                        else if (current_selection_result.interview_setting_person_type == 1) {
                            return 4;
                        }
                        else {
                            // (2) 〇〇面談の候補日を指定する
                            return 2;
                        }
                    }
                }
                else if (curStatus == 7/*7:日程設定済み*/) {
                    // (7) 以下の日程で〇〇面談の日時が確定
                    return 7;
                }
                else {
                    return -1;
                }
            }

            // (5) 面談の詳細を入力
            // Nothing...

            // オファー・契約
            if (curStep == 8)
            {
                if (curStatus == 1) { // 1:参画予定日
                    // (12) ご採用おめでとうございます！
                    return 12;
                }
                else if (curStatus == 2) { // 2:契約条件提示・交渉（！要対応）
                    // (8) ご契約条件の提示をお願い致します。
                    return 8;
                }
                else if (curStatus == 3) { // 3:契約条件同意待ち

                    if (this.contractTerms == null || this.contractTerms.length == 0) {
                        // (8) ご契約条件の提示をお願い致します。
                        return 8;
                    }
                    else if (this.contractTerms.length == 1) {
                        // (9) 提示した契約条件に対する候補者からの返答待ち （変更なし）
                        return 9;
                    }
                    else {
                        // (10) 提示した契約条件に対する候補者からの返答待ち （変更後）
                        return 10;
                    }
                }
                else {
                    return -1;
                }
            }

            // 参画確認
            if (curStep == 9)
            {
                if (curStatus == 1) {   // 1:参画開始
                    // (13) 参画中
                    return 13;
                }
                else {  // 2:辞退 3:見送り
                    return -1;
                }
            }

            // 現況
            if (curStep == 10)
            {
                if (curStatus == 1) {   // 1:参画中
                    if (this.outsourceApplyMgt.joining_end_date != null) {
                        if (this.outsourceApplyMgt.joining_end_applicant == 1) { // 求人企業が終了申請した場合
                            // (14) 参画終了申請済み
                            return 14;
                        }
                        else { // 業務委託会社側が終了申請した場合
                            // (15) 参画終了申請が届く
                            return 15;
                        }
                    }
                    else {
                        // (13) 参画中
                        return 13;
                    }
                }
                else if (curStatus == 2) {   // 2:終了
                    // (16) 参画終了済み
                    return 16;
                }
                else {
                    return -1;
                }
            }

            return -1;
        }
    },
    watch: {

    },
    methods: {
        getCurrentSelectionStatus(currentStep) {
            const resultKey = this.$enumOutsourceApplyMgtSelectionResultKeys[currentStep];
            if (resultKey == null) {
                console.warn('NextOperation.vue/getCurrentSelectionStatus() currentStep is out of range.');
                return 0;
            }

            const result = this.outsourceApplyMgt[resultKey];
            if (result == null) {
                console.warn('NextOperation.vue/getCurrentSelectionStatus() resultKey is out of range.');
                return 0;
            }

            return result;
        },
        getCurrentSelectionResult(currentStep) {
            if (this.selectionResults == null)
            {
                return null;
            }
            for (var i=0; i<this.selectionResults.length; i++)
            {
                if (this.selectionResults[i].phase == currentStep)
                {
                    return this.selectionResults[i];
                }
            }
            return null;
        },
        formatInterviewDate(date, from, to, type) {
            // 4/2 金 13:00〜14:00
            let formated = moment(new Date(date)).format('YYYY/M/D');
            let monthday = moment(new Date(date)).format('M/D');
            let weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            let weekday = weekdays[moment(new Date(date)).day()];

            let from_time = moment(new Date(formated + ' ' + from)).format('HH:mm');
            let to_time = moment(new Date(formated + ' ' + to)).format('HH:mm');

            let comment = '';
            let style = '';

            switch (type)
            {
            case 2:
                comment = '◯';
                style = '';
                break;
            case 3:
                comment = '✖';
                style = 'color:grey';
                break;
            case 4:
                comment = 'NG';
                style = 'text-decoration: line-through; color:grey';
                break;
            }

            return '<span style="' + style + '">' + monthday + ' ' + weekday + ' ' + from_time + '〜' + to_time + '&nbsp;&nbsp;&nbsp;<span>' + comment + '</span></span>';
        },
        formatDigits(value) {
            if (value == null || value == undefined) {
                return '';
            }
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        formatDate(date) {
            // 2021年 1月 1日
            if (date == null || date == undefined) {
                return '';
            }
            return moment(new Date(date)).format('YYYY年M月D日');
        },
        // 見送り
        showNotAdoptedModal() {
            this.unseated_reason = '';

            $("#notAdoptedReasonModal input[name=unseated_reason]").filter(":checked").prop('checked', false);
            $("#notAdoptedReasonModal input[name=unseated_reason_sub]").filter(":checked").prop('checked', false);
            $("#notAdoptedReasonModal textarea[id=unseated_cause_detail]").val('');

            $('#notAdoptedReasonModal').modal('show');
        },
        // 選考通過 次の選考へ
        showPassSelectionModal() {
            $("#passSelectionModal select[name=next_phase]").val('');
            $("#passSelectionModal select[name=current_evaluation]").val('');
            $("#passSelectionModal textarea[name=evaluation_point]").val('');
            $("#passSelectionModal textarea[name=concern_point]").val('');

            $('#passSelectionModal').modal('show');
        },
        // 契約する
        showConfirmHireModal() {
            $('#confirmHireModal').modal('show');
        },
        // 面談詳細を入力する
        showInterviewModal() {
            var prevBelongs = '';
            if (this.selectionResults != null) {
                for (var i=this.selectionResults.length-1; i>=0; i--) {
                    if (this.selectionResults[i].next_phase == this.outsourceApplyMgt.last_selection_flow_number) {
                        prevBelongs = this.selectionResults[i].belongings;
                        break;
                    }
                }
            }

            $("#interviewModal input[id=interviewer]").val(this.$offerCompanyUser.name);
            $("#interviewModal input[id=interview_address]").val(this.$enumPrefectures[this.$recruitingCompany.prefecture] + ' ' + this.$recruitingCompany.address);
            $("#interviewModal input[id=belongings]").val(prevBelongs);    // -持ち物：前回の面談設定の際に入力した情報（※実装可否は要確認）
            $("#interviewModal input[id=emergency_contact_address]").val(this.$offerCompanyUser.phone_number);

            $('#interviewModal').modal('show');
        },
        // 候補日を提示してもらう
        showRequireScheduleModal() {
            $('#requireScheduleModal').modal('show');
        },
        // 契約条件を提示する
        showJoinConditionModal() {
            this.unit_price = 1;
            $("#joiningConditionModal select[name=unit_price]").val(this.unit_price);   // 月額単価
            $("#joiningConditionModal input[id=unit_price_amount]").val(this.outsourceApplyMgt.proposal_unit_price);
            $("#joiningConditionModal input[id=pay_off_start]").val(this.$offerInfo.pay_off_start);
            $("#joiningConditionModal input[id=pay_off_end]").val(this.$offerInfo.pay_off_end);
            $("#joiningConditionModal input[id=estimated_working_days_week]").val(this.$offerInfo.estimated_working_days_week);
            $("#joiningConditionModal textarea[name=special_notes]").val(this.$offerInfo.special_notes);

            $('#joiningConditionModal').modal('show');
        },
        // 参画開始日の変更
        showChangeStartDateModal() {
            $('#changeStartDateModal').modal('show');
        },
        // 参画終了申請
        showFinishContractModal() {
            $('#finishContractModal').modal('show');
        },
        // 参画終了申請 - 確認済み
        showConfirmFinishModal() {
            $('#confirmFinishModal').modal('show');
        },
        // 参画終了申請 - 取消
        showRejectFinishModal() {
            $('#rejectFinishModal').modal('show');
        },
        changeNotAdoptedReason(index) {
            if (this.unseated_reason != index) {
                $("input[name=unseated_reason_sub]").filter(":checked").prop('checked', false);
                this.unseated_reason = index;
                this.unseated_reason_text = this.$enumOutsourceUnseatedReasons[this.unseated_reason];
                if (this.unseated_reason_text.endsWith('合わなかった')) {
                    this.unseated_reason_text += '点';
                }
                this.NotAdoptedDetailReasons = this.$enumOutsourceUnseatedReasonSubs[this.unseated_reason];
            }
        },
        changeUnitPrice() {
            let index = $("#joiningConditionModal select[name=unit_price]").val();
            if (this.unit_price != index) {
                this.unit_price = index;
            }
        },
        // 見送り - 送信
        submitNotAdoptedReason() {
            let mainRadio        = $("#notAdoptedReasonModal input[name=unseated_reason]");
            let unseated_reason  = mainRadio.filter(":checked").val();

            let subRadio         = $("#notAdoptedReasonModal input[name=unseated_reason_sub]");
            let unseated_reason_sub  = subRadio.filter(":checked").val();

            let unseated_cause_detail = $("#notAdoptedReasonModal textarea[id=unseated_cause_detail]").val();

            if (unseated_reason == null || unseated_reason.trim() == '')
            {
                alert('お見送りの理由を選択ください。');
                return;
            }
            if (unseated_reason_sub == null || unseated_reason_sub.trim() == '')
            {
                alert('お見送りの理由を選択ください。');
                return;
            }

            //const store = useStore();
            store.commit('selection_not_adopted', {
                unseated_reason: unseated_reason,
                unseated_reason_sub: unseated_reason_sub,
                unseated_cause_detail: unseated_cause_detail
            });

            $('#notAdoptedReasonModal').modal('hide');
        },
        // 選考通過 次の選考へ - 確定して次へ進む
        submitPassSelection() {
            let next_phase         = $("#passSelectionModal select[name=next_phase]").val();
            let current_evaluation = $("#passSelectionModal select[name=current_evaluation]").val();
            let evaluation_point   = $("#passSelectionModal textarea[name=evaluation_point]").val();
            let concern_point      = $("#passSelectionModal textarea[name=concern_point]").val();

            if (next_phase == null || next_phase == '') {
                $("select[name=next_phase]").focus();
                alert('次回の選考フェーズを選択ください。');
                return;
            }
            if (current_evaluation == null || current_evaluation == '') {
                $("select[name=current_evaluation]").focus();
                alert('現状の評価を選択ください。');
                return;
            }
            if (evaluation_point == null || evaluation_point.trim() == '') {
                $("textarea[name=evaluation_point]").focus();
                alert('評価点をご入力してください。');
                return;
            }
            if (concern_point == null || concern_point.trim() == '') {
                $("textarea[name=concern_point]").focus();
                alert('懸念点をご入力してください。');
                return;
            }

            //const store = useStore();
            store.commit('selection_passed', {
                next_phase: next_phase,
                current_evaluation: current_evaluation,
                evaluation_point: evaluation_point,
                concern_point: concern_point
            });

            $('#passSelectionModal').modal('hide');
        },
        // 契約する - OK
        submitHire() {
            store.commit('selection_hired', {
            });
            $('#confirmHireModal').modal('hide');

            setTimeout(() => { this.showJoinConditionModal(); }, 500);
        },
        // 日程調整へ
        linkCalendar(type) {
            window.location.href = '/company/outsource/calendar'+type+'?id='+this.outsourceApplyMgt.id;
        },
        // 面談詳細を入力する - OK
        sendInterviewDetail(isApplicantSetSchedule) {

            let interviewer               = $("#interviewModal input[id=interviewer]").val();
            let interview_address         = $("#interviewModal input[id=interview_address]").val();
            let belongings                = $("#interviewModal input[id=belongings]").val();
            let emergency_contact_address = $("#interviewModal input[id=emergency_contact_address]").val();
            let else_special_note         = $("#interviewModal textarea[id=else_special_note]").val();

            if ((interviewer == null || interviewer.trim() == '') || 
                (interview_address == null || interview_address.trim() == '') || 
                (belongings == null || belongings.trim() == '') || 
                (emergency_contact_address == null || emergency_contact_address.trim() == '')) {
                alert('必須項目を入力してください。');
                return;
            }

            store.commit('interview_detail', {
                interviewer: interviewer,
                interview_address: interview_address,
                belongings: belongings,
                emergency_contact_address: emergency_contact_address,
                else_special_note: else_special_note,
            });

            $('#interviewModal').modal('hide');

            if (isApplicantSetSchedule) {
                this.showRequireScheduleModal();
            }
            else {
                this.linkCalendar(2);
            }
        },
        // 候補日を提示してもらう - OK
        sendInterviewSettingPersonType(isApplicantSetSchedule) {
            store.commit('interview_setting_person_type', {
                interview_setting_person_type: isApplicantSetSchedule ? 2 : 1   // 1:求人企業 2:候補者
            });
            $('#requireScheduleModal').modal('hide');
        },
        // 契約条件を提示する - 送信
        sendJoiningCondition() {
            let unit_price                  = $("#joiningConditionModal select[name=unit_price]").val();
            let unit_price_amount           = $("#joiningConditionModal input[id=unit_price_amount]").val();
            let pay_off_start               = $("#joiningConditionModal input[id=pay_off_start]").val();
            let pay_off_end                 = $("#joiningConditionModal input[id=pay_off_end]").val();
            let estimated_working_days_week = $("#joiningConditionModal input[id=estimated_working_days_week]").val();
            let special_notes               = $("#joiningConditionModal textarea[name=special_notes]").val();
            let joining_start_date          = $("#joiningConditionModal input[id=joining_start_date]").val();
            let reply_deadline              = $("#joiningConditionModal input[id=reply_deadline]").val();

            if (unit_price == "" || unit_price_amount == "" || estimated_working_days_week == "" || joining_start_date == "" || reply_deadline == "") {
                alert('必須項目を入力してください。');
                return;
            }
            if (unit_price == 1 && (pay_off_start == "" || pay_off_end == "")) {
                alert('必須項目を入力してください。');
                return;
            }

            store.commit('send_joining_condition', {
                unit_price: unit_price,
                unit_price_amount: unit_price_amount,
                pay_off_start: pay_off_start,
                pay_off_end: pay_off_end,
                estimated_working_days_week: estimated_working_days_week,
                special_notes: special_notes,
                joining_start_date: joining_start_date,
                reply_deadline: reply_deadline,
            });

            $('#joiningConditionModal').modal('hide');
        },
        // 参画開始日の変更 - OK
        sendChangeStartDate() {
            let joining_scheduled_date = $("#changeStartDateModal input[id=joining_scheduled_date]").val();
            if (joining_scheduled_date == "") {
                alert('参画開始予定日を入力してください。');
                return;
            }
            store.commit('change_start_date', {
                joining_scheduled_date: joining_scheduled_date
            });
            $('#changeStartDateModal').modal('hide');
        },
        // 参画終了申請
        sendRequestFinish() {
            let joining_end_date = $("#finishContractModal input[id=joining_end_date]").val();
            if (joining_end_date == "") {
                alert('参画終了日を入力してください。');
                return;
            }
            store.commit('send_finish_contract', {
                joining_end_date: joining_end_date
            });
            $('#finishContractModal').modal('hide');
        },
        // 参画終了申請 - OK
        // 参画終了申請の取消
        sendConfirmFinishContract(isAgree) {
            store.commit('agree_finish_contract', {
                isAgree: isAgree,
            });
            $('#confirmFinishModal').modal('hide');
            $('#rejectFinishModal').modal('hide');
        }
    },
    mounted() {
        this.currentStep = this.outsourceApplyMgt.last_selection_flow_number;
    },

}
</script>

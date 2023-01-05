<template>

    <div class="card mb-2" v-if="nextOpType == 1">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    {{ selectionText }}の結果を選択してください。<br>
                    {{ recruitApplyMgt.last_selection_flow_number == 3 ? '選考' : '面接' }}から{{ pastDays }}日が経過しています。
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showNotAdoptedModal()"><b>不採用</b></button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showPassSelectionModal()" v-show="NextSelections.length > 0">選考通過<br/>次の選考へ</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmHireModal()">内定する</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 2">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">{{ selectionText }}の候補日、及び面接詳細を設定してください。</p>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRequireScheduleModal()">候補者に候補日を提示してもらう</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="linkCalendar(2)">候補日を提示する</button>
            </div>
        </div>
    </div>

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

    <div class="card mb-2" v-if="nextOpType == 5">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">{{ selectionText }}の詳細を入力してください。</p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-primary" @click="showInterviewModal()">{{ selectionText }}の詳細を入力する</button>
                </div>
            </div>
        </div>
    </div>

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

    <div class="card mb-2" v-if="nextOpType == 7">
        <div class="card-body">
            <div class="prompt prompt--success">
                <p class="icon">!</p>
                <p class="text">
                    以下の日程で{{ selectionText }}の日時が確定しました。<br/>
                    <em>{{ confirmedDate }}</em><br/>
                    <div class="mT-10">※変更する場合はタイムラインより人材紹介会社へご連絡をお願い致します。</div>
                </p>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 8">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    入社手続きを進めてください。<br>
                    内定から{{ pastDays }}日が経過しています。
                </p>
            </div>
            <div class="row mY-10">
                <div class="col col-md-6">
                    <p class="w-100 h-100 ta-c d-flex justify-content-center align-items-center">入社条件の調整や入社前の説明など転職者との面談が必要な場合</p>
                </div>
                <div class="col col-md-6">
                    <p class="w-100 h-100 ta-c d-flex justify-content-center align-items-center bgc-blue-100">入社条件が決まっている場合</p>
                </div>
            </div>
            <div class="row mY-10">
                <div class="col col-md-6">
                    <button type="button" class="mT-5 w-100 h-100 btn btn-outline-secondary" @click="showIssueScheduleModal()">オファー面談をする<br>（日程を調整）</button>
                </div>
                <div class="col col-md-6">
                    <button type="button" class="mT-5 w-100 h-100 btn btn-primary" @click="showJoinConditionModal()">入社条件を提示する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 9">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    提示した入社条件に対する候補者からの返答待ちです。<br>
                    返答があるまで今しばらくお待ちください。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">提示した入社条件</span>
                </p>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">オファー金額（円）</p>
                <div class="jcondition_detail_content">
                    <p>年収 {{ this.joiningConditions.length > 0 ? formatDigits(this.joiningConditions[0].offer_amount) : 0 }} 円</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">初日出勤日</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].first_day_attendance_date) : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">返答期限</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].reply_deadline) : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">入社手続きに必要な書類</p>
                <div class="jcondition_detail_content">
                    <p v-for="(file, index) in this.joinConditionAttachments">{{ file.attachment_name }}
                        <a class="link mL-20 fRight" :href="'/storage/recruit/attachment/'+file.attachment" target="_blank">
                            <i class="link-icon ti-new-window"></i>
                        </a>
                        <a class="link fRight" :href="'/storage/recruit/attachment/'+file.attachment" :download="file.attachment_name">
                            <i class="link-icon ti-download"></i>
                        </a>
                    </p>
                </div>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showIssueScheduleModal()">オファー面談をする<br>（日程を調整）</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showJoinConditionModal()">入社条件を変更する</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 10">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    提示した入社条件に対する候補者からの返答待ちです。<br>
                    返答があるまで今しばらくお待ちください。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">前回提示した入社条件と変更後の入社条件</span>
                </p>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">オファー金額（円）</p>
                <div class="jcondition_detail_content" v-if="this.joiningConditions.length > 1 && this.joiningConditions[0].offer_amount != this.joiningConditions[1].offer_amount">
                    <p>＜変更前＞年収 {{ formatDigits(this.joiningConditions[1].offer_amount) }} 円</p>
                    <p class="text-primary">＜変更後＞年収 {{ formatDigits(this.joiningConditions[0].offer_amount) }} 円</p>
                </div>
                <div class="jcondition_detail_content" v-else>
                    <p>年収 {{ this.joiningConditions.length > 0 ? formatDigits(this.joiningConditions[0].offer_amount) : 0 }} 円</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">初日出勤日</p>
                <div class="jcondition_detail_content" v-if="this.joiningConditions.length > 1 && this.joiningConditions[0].first_day_attendance_date != this.joiningConditions[1].first_day_attendance_date">
                    <p>＜変更前＞{{ formatDate(this.joiningConditions[1].first_day_attendance_date) }}</p>
                    <p class="text-primary">＜変更後＞{{ formatDate(this.joiningConditions[0].first_day_attendance_date) }}</p>
                </div>
                <div class="jcondition_detail_content" v-else>
                    <p>{{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].first_day_attendance_date) : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">返答期限</p>
                <div class="jcondition_detail_content" v-if="this.joiningConditions.length > 1 && this.joiningConditions[0].reply_deadline != this.joiningConditions[1].reply_deadline">
                    <p>＜変更前＞{{ formatDate(this.joiningConditions[1].reply_deadline) }}</p>
                    <p class="text-primary">＜変更後＞{{ formatDate(this.joiningConditions[0].reply_deadline) }}</p>
                </div>
                <div class="jcondition_detail_content" v-else>
                    <p>{{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].reply_deadline) : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">入社手続きに必要な書類<!--<br/>（変更したファイルを含む全ての書類）--></p>
                <div class="jcondition_detail_content">
                    <p v-for="(file, index) in this.joinConditionAttachments">{{ file.attachment_name }}
                        <a class="link mL-20 fRight" :href="'/storage/recruit/attachment/'+file.attachment" target="_blank">
                            <i class="link-icon ti-new-window"></i>
                        </a>
                        <a class="link fRight" :href="'/storage/recruit/attachment/'+file.attachment" :download="file.attachment_name">
                            <i class="link-icon ti-download"></i>
                        </a>
                    </p>
                </div>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showIssueScheduleModal()">オファー面談をする<br>（日程を調整）</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showJoinConditionModal()">入社条件を変更する</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 11">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    提示した入社条件に対して、転職者から希望の条件が提示されました。<br>
                    確認後、ご回答をお願い致します。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">提示した入社条件</span>
                </p>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">オファー金額（円）</p>
                <div class="jcondition_detail_content">
                    <p>年収 {{ this.joiningConditions.length > 0 ? formatDigits(this.joiningConditions[0].offer_amount) : 0 }} 円</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">初日出勤日</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].first_day_attendance_date) : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">返答期限</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].reply_deadline) : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">入社手続きに必要な書類</p>
                <div class="jcondition_detail_content">
                    <p v-for="(file, index) in this.joinConditionAttachments">{{ file.attachment_name }}
                        <a class="link mL-20 fRight" :href="'/storage/recruit/attachment/'+file.attachment" target="_blank">
                            <i class="link-icon ti-new-window"></i>
                        </a>
                        <a class="link fRight" :href="'/storage/recruit/attachment/'+file.attachment" :download="file.attachment_name">
                            <i class="link-icon ti-download"></i>
                        </a>
                    </p>
                </div>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">転職者が希望する入社条件</span>
                </p>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">希望年収（円）</p>
                <div class="jcondition_detail_content">
                    <p>年収 {{ this.joiningConditions.length > 0 ? formatDigits(this.joiningConditions[0].job_changer_desired_annual_income) : '' }} 円</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">初日出勤日</p>
                <div class="jcondition_detail_content">
                    <p>{{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].job_changer_first_day_attendance_date) : '' }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-5">
                <p class="jcondition_detail_title">その他希望</p>
                <div class="jcondition_detail_content" style="white-space: break-spaces;">
                    <p>{{ this.joiningConditions.length > 0 ? this.joiningConditions[0].other_desired : '' }}</p>
                </div>
            </div>

            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showNotAdoptedModal()">不採用</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showJoinConditionModal()">入社条件を<br>再提案</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmConditionModal()">同意する<br>（採用決定）</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 12">
        <div class="card-body">
            <div class="icon-container mT-0"><i class="ti-face-smile"></i><span>ご採用おめでとうございます！</span></div>
            <p class="text mX-30 mY-10">
                貴社の採用活動に微力ながらもお手伝い出来たことを大変光栄に思っております。<br/>
                採用者の初日出勤に関しまして、以下のご対応をお願い致します。
            </p>
            <div class="prompt prompt--success">
                <p class="icon">!</p>
                <p class="text mY-0">
                    <em>初日出勤の確認待ち</em><br>
                    <em>初日出勤予定日：{{ confirmedDate }}</em>
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRejectPresentModal()">出勤し<br>なかった</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmPresentModal()">出勤した</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showChangePresentDateModal()">入社日変更</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 13">
        <div class="card-body">
            <div class="prompt prompt--success">
                <p class="icon orange no-background pX-0">〇</p>
                <p class="text mY-5"><em>入社確認済み：{{ confirmedDate }}</em></p>
            </div>
            <p class="text mY-10">※万一、この採用者が以下の返金規定の期間内に退職した場合は以下よりエージェントへ要請ください。</p>
            <p class="text bgc-grey-200 p-20">
                【返済規定】
                <p class="mB-10">{{ this.$offerInfo.refund_policy }}</p>
                <a class="link pL-10 cur-p bold" @click="showRefundRequestModal()">▶返金規定内で退職したため返金申請をする</a>
            </p>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 14">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    候補者が出勤しませんでした。<br>
                    新しい入社日が届くまで今しばらくお待ちください。
                </p>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 15">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text mY-10">
                    【返金申請】<br>
                    人材紹介会社様へ「返金規定期間内での退職による返金申請」を行いました。<br>
                    返答があるまで今しばらくお待ちください。<br><br>

                    ■ 返金時のお支払いの流れ<br>
                    ①求人企業様から当社へ「紹介成功報酬の全額」をお支払い<br>
                    ②当社から人材紹介会社様へ「紹介成功報酬の全額」をお支払い<br>
                    ③人材紹介会社様から「求人企業様と合意いただいた返金額」を求人企業様へ直接ご返金
                </p>
            </div>
            <div class="row mX-20 mY-10">
                <p class="col-md">対象者</p>
                <div class="col-md">
                    <p>{{ this.$jobSeeker.last_name + ' ' + this.$jobSeeker.first_name }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-10">
                <p class="col-md">入社日（初日出勤日）</p>
                <div class="col-md">
                    <p>{{ formatDate(recruitApplyMgt.joining_confirmation_date) }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-10">
                <p class="col-md">退職日</p>
                <div class="col-md">
                    <p>{{ formatDate(recruitApplyMgt.retirement_date) }}</p>
                </div>
            </div>
            <div class="row mX-20 mY-10">
                <p class="col-md">採用時年収</p>
                <div class="col-md">
                    <p>{{ this.joiningConditions.length > 0 ? formatDigits(this.joiningConditions[0].job_changer_desired_annual_income) : 0 }}円</p>
                </div>
            </div>
            <p class="text bgc-grey-200 p-20">
                【返済規定】
                <p>{{ this.$offerInfo.refund_policy }}</p>
            </p>
            <div class="row mX-20 mT-20">
                <p class="col-md">紹介成功報酬：</p>
                <div class="col-md">
                    <p class="bold">
                        {{ this.$offerInfo.success_reward_calculation_method == 1 ? '年収の '+parseFloat((this.$offerInfo.theory_annual_income==null || this.$offerInfo.theory_annual_income=="") ? 0 : this.$offerInfo.theory_annual_income)+' ％' : '一律固定報酬 ' + this.$offerInfo.theory_annual_income_definition + ' 万円' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 16">
        <div class="card-body">
            <div class="prompt prompt--success">
                <p class="icon orange no-background pX-0">〇</p>
                <p class="text mY-5">
                    <em>入社確認済み：{{ confirmedDate }}</em><br>
                    <em>（返金あり。 退職日：{{ formatDate(recruitApplyMgt.retirement_date) }}。 返金額： {{ formatDigits(recruitApplyMgt.refund_amount) }}円）</em>
                </p>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 17">
        <div class="card-body">
            <div class="prompt prompt--success">
                <p class="icon orange no-background pX-0">〇</p>
                <p class="text mY-5">
                    <em>入社確認済み：{{ confirmedDate }}</em><br>
                    <em>（返金なし。 返金不同意日： {{ formatDate(recruitApplyMgt.refund_disagreement_date) }}）</em>
                </p>
            </div>
            <p class="text mY-10">※万一、この採用者が以下の返金規定の期間内に退職した場合は以下よりエージェントへ要請ください。</p>
            <p class="text bgc-grey-200 p-20">
                【返済規定】
                <p class="mB-10">{{ this.$offerInfo.refund_policy }}</p>
                <a class="link pL-10 cur-p bold" @click="showRefundRequestModal()">▶返金規定内で退職したため返金申請をする</a>
            </p>
        </div>
    </div>

    <!-- begin modal -->
    <div class="modal fade" id="notAdoptedReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header no-border pB-0">
                    <h5 class="modal-title pT-20 w-100 text-sm-center">落選の最も大きい理由を１つ選択してください。<span class="badge">必須</span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pX-40 pY-10">

                    <div class="sub-content mT-0">
                        <div class="row sp_block mX-0">
                            <div v-for="(reason, index) in this.$enumRecruitUnseatedReasons" class="col col-md-3 pX-0">
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
                            <div class="modal-title modal-title--sub">{{ unseated_reason_text }}について当てはまる落選理由を1つ選択してください。<span class="badge">必須</span></div>
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
                        <div class="text mT-20 mX-10 mB-0 pB-5">落選理由の詳細があれば記入してください。</div>
                        <textarea id="unseated_cause_detail" class="textarea box-shadow mX-0 p-10" rows="3" ></textarea>
                    </div>

                    <p class="text mT-10 mB-0">ご入力いただいた内容は、求人票の改善や候補者とのミスマッチ防止に利用いたします。</p>
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
                            <option v-for="(evaluation, index) in this.$enumRecruitEvaluations" :value="index">{{ evaluation }}</option>
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
                        紹介料：{{ this.$offerInfo.success_reward_calculation_method == 1 ? '年収の '+parseFloat((this.$offerInfo.theory_annual_income==null || this.$offerInfo.theory_annual_income=="") ? 0 : this.$offerInfo.theory_annual_income)+' ％' : '一律固定報酬 ' + this.$offerInfo.theory_annual_income_definition + ' 万円' }}<br/>
                        採用事務手数料：上記紹介料の20%（上限15万円）
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
                    選考状況を「内定」に変更します。宜しいですか？
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="submitHire()">内定する</button>
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
                            緊急連絡先（転職者様のみご利用可）<span class="badge">必須</span>
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
                    <button type="button" class="btn btn-outline-secondary" @click="sendInterviewDetail(true)">面接詳細を登録して候補者に日程候補日を提示してもらう</button>
                    <button type="button" class="btn btn-primary" @click="sendInterviewDetail(false)">面接詳細を登録して日程を提示する</button>
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

    <div class="modal fade" id="issueScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header justify-content-start">
                    <span class="icon-holder"><i class="ti-calendar"></i> </span>
                    <h5 class="mL-10 modal-title">オファー面談の申し込みと日程の提示</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span>
                        開始すると、オファー面談の申し込みと同時に、面談日程の提示を行うための日程調整画面に移ります。
                    </span>
                    <br/>
                    <span style="color:red">
                        ※オファー面談を開始すると面談が終了するまで入社条件の提示ができなくなりますのでご注意ください。
                    </span>
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="col btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="col btn btn-primary" @click="sendInterviewSettingPersonType(true)">開始する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="joiningConditionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <h5 class="mL-10 modal-title">入社条件を提示</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <p class="mB-0">後ほど更新することもできます。</p>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            オファー金額（円）<span class="badge">必須</span>
                        </div>
                        <div class="col-md-8 pL-0">
                            年収 <input class="box-shadow col-md-5 p-10 hide-arrow" id="offer_amount" type="number" min="0" oninput="validity.valid||(value='');"> 円<br>
                            <span class="form-text mT-0">内定通知書に記入予定の年収を記入してください</span>
                        </div>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            初日出勤日<span class="badge">必須</span>
                        </div>
                        <div class="col-md-4 pL-0">
                            <input type="text" class="box-shadow col-md-12 p-10" id="first_day_attendance_date" autocomplete="off" data-provide="datepicker">
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
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            入社手続きに必要な書類<span class="badge blue">任意</span>
                        </div>
                        <div class="col-md-8 p-0">
                            <div id="upload_file_div" class="p-0">
                                <div class="attach_line">
                                    <input name="attachments" type="file" class="mB-5" v-on:change="onFileChange">
                                </div>
                            </div>
                            <a class="link-add-file mY-0" href="#" @click="addFileBtn()">＋書類を追加する</a>
                            <span class="form-text mT-5">※内定通知、労働条件通知書、入社承諾書など</span>
                            <span class="form-text mT-0 pT-0">※PDF各ファイル　５MBまで</span>
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

    <div class="modal fade" id="confirmConditionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    転職者が希望する入社条件に同意します。宜しいですか？
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="submitAllow()">同意する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changePresentDateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header justify-content-start">
                    <span class="icon-holder"><i class="ti-calendar"></i> </span>
                    <h5 class="mL-10 modal-title">入社日の変更</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            初日出勤日<span class="badge">必須</span>
                        </div>
                        <div class="col-md-5 pL-0">
                            <input type="text" class="box-shadow col-md-12 p-10" id="first_day_work_schedule_date" autocomplete="off" data-provide="datepicker">
                            <i class="ti-calendar place"></i>
                        </div>
                    </div>
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="col btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="col btn btn-primary" @click="sendChangePresentDate()">変更する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmPresentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    候補者が出勤しました。宜しいですか？
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="sendConfirmPresent(true)">確認</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectPresentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    候補者が出勤しませんでした。宜しいですか？
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="sendConfirmPresent(false)">確認</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="refundRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="icon-holder"><i class="ti-write"></i> </span>
                    <h5 class="mL-10 modal-title">返金規定期間内での退職による返金申請</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <div class="prompt pT-20">
                        <p class="icon">!</p>
                        <p class="text ta-l mY-0">
                            返金規定の期間内に退職した場合は、こちらから人材紹介エージェントへ紹介成功報酬の返金申請をしてください。<br>
                            申請後は選考画面の「人材紹介タイムライン」よりエージェントと直接やり取りをお願い致します。<br>
                            人材紹介エージェントが同意した場合にのみ返金となります。<br><br>
                            <i>※紹介成功報酬の返金は当社からではなく、人材紹介エージェントより直接行われます。<br>
                            ※紹介成功報酬および採用事務手数料を当社へお支払いする前の場合、全額お支払いいただいた後に人材紹介エージェントから返金される流れになります。返金分を減額してのお支払い対応はシステム上できかねますのでご了承ください。<br>
                            ※当社にお支払いいただいた採用事務手数料については返金の対象外となりますのでご了承ください。</i>
                        </p>
                    </div>
                    <div class="mX-20 mY-10">
                        <div class="row">
                            <p class="jcondition_detail_title pY-5 mY-0">求人タイトル</p>
                            <p class="jcondition_detail_content pY-5 mY-0">{{ this.$offerInfo.job_title }}</p>
                        </div>
                        <div class="row">
                            <p class="jcondition_detail_title pY-5 mY-0">対象者</p>
                            <p class="jcondition_detail_content pY-5 mY-0">{{ this.$jobSeeker.last_name + ' ' + this.$jobSeeker.first_name }}</p>
                        </div>
                        <div class="row">
                            <p class="jcondition_detail_title pY-5 mY-0">入社日（初日出勤日）</p>
                            <p class="jcondition_detail_content pY-5 mY-0">{{ formatDate(recruitApplyMgt.joining_confirmation_date) }}</p>
                        </div>
                        <div class="row">
                            <p class="jcondition_detail_title pY-5 mY-0">採用時年収</p>
                            <p class="jcondition_detail_content pY-5 mY-0">{{ this.joiningConditions.length > 0 ? formatDigits(this.joiningConditions[0].job_changer_desired_annual_income) : 0 }}円</p>
                        </div>
                    </div>
                    <div class="mY-20 bgc-grey-200 p-20">
                        <div class="row">
                            <p class="jcondition_detail_title">この求人の返済規定</p>
                            <p class="jcondition_detail_content mB-0" style="white-space: break-spaces;">{{ this.$offerInfo.refund_policy }}
                            </p>
                        </div>
                    </div>
                    <div class="mX-20">
                        <div class="row">
                            <div class="col-md-4">
                                退職日<span class="badge">必須</span>
                            </div>
                            <div class="col-md-4 pL-0">
                                <input type="text" class="box-shadow col-md-12 p-10" id="retirement_date" autocomplete="off" data-provide="datepicker">
                                <i class="ti-calendar place"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between mX-40">
                    <button type="button" class="col btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="col btn btn-primary" @click="sendRetirementDate()">送信</button>
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
            recruitApplyMgt : state => state.recruitApplyMgt,
            selectionResults : state => state.selectionResults,
            timelines : state => state.timelines,
            interviewSchedules : state => state.interviewSchedules,
            joiningConditions : state => state.joiningConditions,
            joinConditionAttachments : state => state.joinConditionAttachments,
        }),
        pastDays() {
            var current = new Date(this.serverTime);

            let currentStep = this.recruitApplyMgt.last_selection_flow_number;
            const resultKey = this.$enumRecruitApplyMgtSelectionResultKeys[currentStep];
            if (resultKey == null) {
                console.warn('NextOperation.vue/pastDays() currentStep is out of range.');
                return 0;
            }

            const selection_date = this.recruitApplyMgt[resultKey+'_date'];
            if (selection_date == null) {
                console.warn('NextOperation.vue/pastDays() resultKey is out of range.');
                return 0;
            }
            var lastday = new Date(selection_date);

            return Math.ceil(Math.abs(current-lastday) / (1000*60*60*24)).toFixed(0);
        },
        selectionText() {
            let currentStep = this.recruitApplyMgt.last_selection_flow_number;
            const result = this.$enumRecruitSelectionFlows[currentStep];
            if (result == null) {
                console.warn('NextOperation.vue/selectionText() currentStep is out of range.');
                return '〇〇〇';
            }
            if (result == '採用') {
                return 'オファー面談';
            }

            return result;
        },
        confirmedDate() {
            let currentStep = this.recruitApplyMgt.last_selection_flow_number;
            const resultKey = this.$enumRecruitApplyMgtSelectionResultKeys[currentStep];
            if (resultKey == null) {
                console.warn('NextOperation.vue/confirmedDate() currentStep is out of range.');
                return '';
            }

            const result = this.recruitApplyMgt[resultKey];
            // オファー面談の場合
            if (currentStep == 12 && result == 2/*2:オファー面談設定済み*/) {
                selection_date = '';
                from_time = to_time = '';

                for (var i=0; i<this.interviewSchedules.length; i++) {
                    if (this.interviewSchedules[i].interview_phase == currentStep && this.interviewSchedules[i].interview_date_type == 2) { // 2:確定した日(=◯)
                        let formated = moment(new Date(this.interviewSchedules[i].interview_candidates_date)).format('YYYY/M/D');
                        selection_date = moment(new Date(this.interviewSchedules[i].interview_candidates_date)).format('YYYY年M月D日');
                        from_time = moment(new Date(formated + ' ' + this.interviewSchedules[i].interview_candidates_from)).format('HH:mm');
                        to_time = moment(new Date(formated + ' ' + this.interviewSchedules[i].interview_candidates_to)).format('HH:mm');
                        break;
                    }
                }
                if (from_time == "" || to_time == "") {
                    return '';
                }
                selection_date = selection_date + ' ' + from_time + ' ～ ' + to_time;
                return selection_date;
            }
            else if (currentStep == 12 && result == 5/*5:入社予定日あり*/) {
                selection_date = '';

                if (this.joiningConditions.length > 0) {
                    selection_date = moment(new Date(this.joiningConditions[0].first_day_work_schedule_date)).format('YYYY年M月D日');
                }
                return selection_date;
            }

            let selection_date = this.recruitApplyMgt[resultKey+'_date'];
            if (selection_date == null || selection_date == '') {
                return '';
            }
            let from_time = '00:00';
            let to_time = '00:00';
            if (currentStep <= 11) {
                selection_date = moment(new Date(selection_date)).format('YYYY年M月D日');
                from_time = to_time = '';

                for (var i=0; i<this.interviewSchedules.length; i++) {
                    if (this.interviewSchedules[i].interview_phase == currentStep && this.interviewSchedules[i].interview_date_type == 2) { // 2:確定した日(=◯)
                        let formated = moment(new Date(this.interviewSchedules[i].interview_candidates_date)).format('YYYY/M/D');
                        from_time = moment(new Date(formated + ' ' + this.interviewSchedules[i].interview_candidates_from)).format('HH:mm');
                        to_time = moment(new Date(formated + ' ' + this.interviewSchedules[i].interview_candidates_to)).format('HH:mm');
                        break;
                    }
                }
                if (from_time == "" || to_time == "") {
                    return '';
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
                // 入社確認の段階は表示しない。　内定することで「採用」段階に移行する。
                if (parseInt(steps[i]) >= 12) {
                    continue;
                }
                if (parseInt(steps[i]) > parseInt(this.recruitApplyMgt.last_selection_flow_number))
                {
                    let selection = new Object();
                    selection.id=steps[i];
                    selection.text = this.$enumRecruitSelectionFlows[selection.id];
                    following_selections.push(selection);
                }
            }
            return following_selections;
        },
        InterviewSchedules() {
            let interview_schedules = [];
            let curStep = this.recruitApplyMgt.last_selection_flow_number;
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
            let calendar1 = '/company/recruit/calendar1?id='+this.recruitApplyMgt.id;
            let calendar2 = '/company/recruit/calendar2?id='+this.recruitApplyMgt.id;

            let current_selection_result = this.getCurrentSelectionResult(this.recruitApplyMgt.last_selection_flow_number);

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

            let curStep = this.recruitApplyMgt.last_selection_flow_number;
            let curStatus = this.getCurrentSelectionStatus(curStep);

            // 応募及び書類確認の選考は、詳細画面に表示しない。
            if (curStep == 1 || curStep == 2)
            {
                console.warn('NextOperation.vue/nextOpType() currentStep is unreachable.');
                return -1;
            }

            // 選考結果が送付済みの場合は、「選考結果の選択」ブタンを表示しない。（内定、通過、辞退、不採用）
            if (curStep <= 11/*最終面接*/ && (curStatus == 1 || curStatus == 2 || curStatus == 3 || curStatus == 4))
            {
                console.warn('NextOperation.vue/nextOpType() currentStep is not next-flow.');
                return -1;
            }

            // (1) 選考結果の選択
            if ((curStep == 3/*書類選考*/ && curStatus == 5/*選考結果未送付*/)
                || (curStep >= 4/*筆記、webテスト*/ && curStep <= 11/*最終面接*/ && curStatus == 7/*選考結果未送付*/))
            {
                return 1;
            }

            if (curStep >= 4/*筆記、webテスト*/ && curStep <= 11/*最終面接*/)
            {
                if (curStatus == 5/*5:日程未確定（！要対応）*/) {
                    let current_selection_result = this.getCurrentSelectionResult(curStep);

                    // (6) 〇〇面接の面接詳細および日程
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
                            // (2) 〇〇面接の候補日を指定する
                            return 2;
                        }
                    }
                }
                else if (curStatus == 6/*6:日程設定済み*/) {
                    // (7) 以下の日程で〇〇面接の日時が確定
                    return 7;
                }
                else {
                    return -1;
                }
            }

            // (5) 面接の詳細を入力
            // Nothing...

            if (curStep == 12)
            {
                if (curStatus == 1) { // 1:オファー面談日程未確定（！要対応）
                    let current_selection_result = this.getCurrentSelectionResult(curStep);

                    // (8) 入社手続きを進めてください
                    if (current_selection_result == null) {
                        return 8;
                    }
                    // (3) 候補者からの日程調整の入力待ち
                    if (current_selection_result.interview_setting_person_type == 2) {
                        return 3;
                    }
                    // (4) 候補者へ提示した日程調整の回答待ち
                    else if (current_selection_result.interview_setting_person_type == 1) {
                        return 4;
                    }
                    // (8) 入社手続きを進めてください
                    return 8;
                }
                else if (curStatus == 2) { // 2:オファー面談設定済み
                    // (7) 以下の日程で〇〇面接の日時が確定
                    return 7;
                }
                else if (curStatus == 3) { // 3:入社条件提示・交渉（！要対応）
                    if (this.joiningConditions == null || this.joiningConditions.length == 0) {
                        // (8) 入社手続きを進めてください
                        return 8;
                    }
                    let current_joining_condition = this.joiningConditions[0];
                    if ((current_joining_condition.job_changer_desired_annual_income != null && current_joining_condition.job_changer_desired_annual_income != 0) &&  // 転職者_希望年収（円）
                        current_joining_condition.job_changer_first_day_attendance_date != null) {  // 転職者_希望初日出勤日
                        // (11) 提示した入社条件に対して、転職者から希望の条件が提示されました。
                        return 11;
                    }
                    // (8) 入社手続きを進めてください
                    return 8;
                }
                else if (curStatus == 4) { // 4:入社条件返答待ち

                    if (this.joiningConditions == null || this.joiningConditions.length == 0) {
                        // (8) 入社手続きを進めてください
                        return 8;
                    }
                    else if (this.joiningConditions.length == 1) {
                        // (9) 提示した入社条件に対する候補者からの返答待ち （変更なし）
                        return 9;
                    }
                    else {
                        // (10) 提示した入社条件に対する候補者からの返答待ち （変更後）
                        return 10;
                    }
                }
                else if (curStatus == 5) { // 5:入社予定日あり
                    // (12) ご採用おめでとうございます！
                    return 12;
                }
                else {
                    return -1;
                }
            }

            // (13) 入社確認済み
            if (curStep == 13)
            {
                if (curStatus == 1) {   // 1:入社
                    if (this.confirmedDate != 0) {
                        if (this.recruitApplyMgt['refund_status'] == 1) { // 1:選択前状態（申請した後）
                            // (15) 返金申請済み
                            return 15;
                        }
                        else if (this.recruitApplyMgt['refund_status'] == 2) { // 2:同意した状態
                            // (16) 申請承認済み
                            return 16;
                        }
                        else if (this.recruitApplyMgt['refund_status'] == 3) { // 3:同意しなかった状態
                            // (17) 申請拒否済み
                            return 17;
                        }
                        else {
                            // (13) 入社確認済み
                            return 13;
                        }
                    }
                    else {
                        // (14) 出勤しなかった
                        return 14;
                    }
                }
                else {  // 2:辞退 3:不採用
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
            const resultKey = this.$enumRecruitApplyMgtSelectionResultKeys[currentStep];
            if (resultKey == null) {
                console.warn('NextOperation.vue/getCurrentSelectionStatus() currentStep is out of range.');
                return 0;
            }

            const result = this.recruitApplyMgt[resultKey];
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
        // 不採用
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
        // 内定する
        showConfirmHireModal() {
            $('#confirmHireModal').modal('show');
        },
        // 面接詳細を入力する
        showInterviewModal() {
            var prevBelongs = '';
            if (this.selectionResults != null) {
                for (var i=this.selectionResults.length-1; i>=0; i--) {
                    if (this.selectionResults[i].next_phase == this.recruitApplyMgt.last_selection_flow_number) {
                        prevBelongs = this.selectionResults[i].belongings;
                        break;
                    }
                }
            }

            $("#interviewModal input[id=interviewer]").val(this.$offerCompanyUser.name);
            $("#interviewModal input[id=interview_address]").val(this.$enumPrefectures[this.$recruitingCompany.prefecture] + ' ' + this.$recruitingCompany.address);
            $("#interviewModal input[id=belongings]").val(prevBelongs);    // -持ち物：前回の面接設定の際に入力した情報（※実装可否は要確認）
            $("#interviewModal input[id=emergency_contact_address]").val(this.$offerCompanyUser.phone_number);

            $('#interviewModal').modal('show');
        },
        // 候補日を提示してもらう
        showRequireScheduleModal() {
            $('#requireScheduleModal').modal('show');
        },
        // オファー面談をする - 候補日を提示してもらう
        showIssueScheduleModal() {
            $('#issueScheduleModal').modal('show');
        },
        // 入社条件を提示する
        showJoinConditionModal() {
            $('#joiningConditionModal').modal('show');
        },
        // 同意する（採用決定）
        showConfirmConditionModal() {
            $('#confirmConditionModal').modal('show');
        },
        // 入社日変更
        showChangePresentDateModal() {
            $('#changePresentDateModal').modal('show');
        },
        // 出勤した
        showConfirmPresentModal() {
            $('#confirmPresentModal').modal('show');
        },
        // 出勤しなかった
        showRejectPresentModal() {
            $('#rejectPresentModal').modal('show');
        },
        // 返金申請
        showRefundRequestModal() {
            $('#refundRequestModal').modal('show');
        },
        changeNotAdoptedReason(index) {
            if (this.unseated_reason != index) {
                $("input[name=unseated_reason_sub]").filter(":checked").prop('checked', false);
                this.unseated_reason = index;
                this.unseated_reason_text = this.$enumRecruitUnseatedReasons[this.unseated_reason];
                if (this.unseated_reason_text.endsWith('合わなかった')) {
                    this.unseated_reason_text += '点';
                }
                this.NotAdoptedDetailReasons = this.$enumRecruitUnseatedReasonSubs[this.unseated_reason];
            }
        },
        // 不採用 - 送信
        submitNotAdoptedReason() {
            let mainRadio        = $("#notAdoptedReasonModal input[name=unseated_reason]");
            let unseated_reason  = mainRadio.filter(":checked").val();

            let subRadio         = $("#notAdoptedReasonModal input[name=unseated_reason_sub]");
            let unseated_reason_sub  = subRadio.filter(":checked").val();

            let unseated_cause_detail = $("#notAdoptedReasonModal textarea[id=unseated_cause_detail]").val();

            if (unseated_reason == null || unseated_reason.trim() == '')
            {
                alert('落選したの理由を選択ください。');
                return;
            }
            if (unseated_reason_sub == null || unseated_reason_sub.trim() == '')
            {
                alert('落選したの理由を選択ください。');
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
        // 内定する - OK
        submitHire() {
            store.commit('selection_hired', {
            });
            $('#confirmHireModal').modal('hide');

            setTimeout(() => { this.showJoinConditionModal(); }, 500);
        },
        // 日程調整へ
        linkCalendar(type) {
            window.location.href = '/company/recruit/calendar'+type+'?id='+this.recruitApplyMgt.id;
        },
        // 面接詳細を入力する - OK
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
            $('#issueScheduleModal').modal('hide');
        },
        // 入社条件を提示する - 送信
        sendJoiningCondition() {
            let offerAmount =               $("#joiningConditionModal input[id=offer_amount]").val();
            let firstDayAttendanceDate =    $("#joiningConditionModal input[id=first_day_attendance_date]").val();
            let replyDeadline =             $("#joiningConditionModal input[id=reply_deadline]").val();
            var conditionAttachments = [];

            if (offerAmount == "" || firstDayAttendanceDate == "" || replyDeadline == "") {
                alert('必須項目を入力してください。');
                return;
            }

            for (var i=0; i<$('#joiningConditionModal #upload_file_div input[name=attachments]').length; i++)
            {
                if ($('#joiningConditionModal input[name=attachments]')[i].files[0] != undefined) {
                    conditionAttachments.push($('#joiningConditionModal input[name=attachments]')[i].files[0]);
                }
            }

            store.commit('send_joining_condition', {
                offerAmount: offerAmount,
                firstDayAttendanceDate: firstDayAttendanceDate,
                replyDeadline: replyDeadline,
                files: conditionAttachments
            });

            $('#joiningConditionModal').modal('hide');
        },
        onFileChange(e){
            this.file = e.target.files[0];
        },
        addFileBtn() {
            var input=document.createElement('div');
            input.className = "attach_line";
            var tag = '<input name="attachments" type="file" class="mB-5" v-on:change="onFileChange">' + 
                      '<button type="button" class="remove-file" onclick="$(this).parent().remove();">削除</button>';
            input.innerHTML = tag;
            document.getElementById('upload_file_div').appendChild(input);
        },
        // 同意する（採用決定） - OK
        submitAllow() {
            store.commit('allow_joining_condition', {
            });
            $('#confirmConditionModal').modal('hide');
        },
        // 入社日変更 - OK
        sendChangePresentDate() {
            let presentDate = $("#changePresentDateModal input[id=first_day_work_schedule_date]").val();
            if (presentDate == "") {
                alert('初日出勤予定日を入力してください。');
                return;
            }
            store.commit('change_present_date', {
                present_date: presentDate
            });
            $('#changePresentDateModal').modal('hide');
        },
        // 出勤した・出勤しなかった
        sendConfirmPresent(isPresent) {
            store.commit('check_presented', {
                isPresent: isPresent,
            });
            $('#confirmPresentModal').modal('hide');
            $('#rejectPresentModal').modal('hide');
        },
        // 返金申請 - OK
        sendRetirementDate() {
            let retirementDate = $("#refundRequestModal input[id=retirement_date]").val();
            if (retirementDate == "") {
                alert('退職日を入力してください。');
                return;
            }
            store.commit('send_retirement_date', {
                retirement_date: retirementDate
            });
            $('#refundRequestModal').modal('hide');
        }
    },
    mounted() {
        this.currentStep = this.recruitApplyMgt.last_selection_flow_number;
    },

}
</script>

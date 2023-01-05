<template>

    <div class="card mb-2" v-if="nextOpType == 1">
        <div class="card-body">
            <div class="prompt prompt--info" v-if="this.recruitApplyMgt.last_selection_flow_number == 3">
                <p class="text">
                    [応募済み]<br>
                    求人企業様が書類選考をしています。<br>
                    選考結果が届くまで今しばらくお待ちください。
                </p>
            </div>
            <div class="prompt prompt--info" v-else>
                <p class="text">
                    [{{ selectionText }}]<br>
                    求人企業様で選考をしています。<br>
                    選考結果が届くまで今しばらくお待ちください。
                </p>
            </div>

            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 2">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    [通過済み]<br>
                    求人企業様が{{ selectionText }}の候補日を設定しています。<br>
                    日程が届くまで今しばらくお待ちください。
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 3">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    求人企業様から{{ this.recruitApplyMgt.last_selection_flow_number == 12 ? 'オファー面談' : selectionText }}の希望日程の提示を求められています。<br>
                    以下より日程調整を行ってください。
                </p>
            </div>
            <div class="icon-container">
                <i class="ti-check-box vA-middle"></i>
                <span class="vA-middle">{{ this.recruitApplyMgt.last_selection_flow_number == 12 ? '内定：オファー面談' : prevSelectionText + '通過' }}</span>
            </div>
            <div v-if="this.recruitApplyMgt.last_selection_flow_number == 12">
                <p class="text pY-10 pL-30">入社条件などの調整のため、求人企業様からオファー面談の要請がありました。</p>
            </div>
            <div v-else>
                <p class="text pY-10 pL-30">{{ prevSelectionText }}の選考を通過いたしました。<br>
                次の選考は「{{ selectionText }}」となります。</p>
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
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="linkCalendar(2)">日程調整へ</button>
            </div>
            <div class="mT-20 pX-10">
                <p class="text">タイムラインなどで調整して日程が確定している場合は、以下より確定日時をご入力いただいても設定することができます。</p>
                <p class="text" style="color:red">※こちらで設定すると確定した日程として求人企業様に即時送信されますのでご注意ください。</p>
                <div class="row mX-0 mY-10">
                    <div class="col-md-6 pX-0 pY-5" style="display:flex;align-items:center">
                        <label class="mB-0 pR-5" style="word-break:keep-all">日程 </label>
                        <div class="col-md-10 pL-0 pR-15" style="position:relative">
                            <input type="text" class="box-shadow col-md-12 p-10" id="fixed_interview_date" autocomplete="off" data-provide="datepicker">
                            <i class="ti-calendar place"></i>
                        </div>
                    </div>
                    <div class="col-md-6 pL-0 pY-5">時間 
                        <select name="fixed_time_from" class="box-shadow pX-5">
                            <option v-for="time in times" :value="time" :selected="time=='06:00'">{{ time }}</option>
                        </select>
                        ～
                        <select name="fixed_time_to" class="box-shadow pX-5">
                            <option v-for="time in times" :value="time" :selected="time=='06:00'">{{ time }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-outline-secondary" @click="sendFixedInterviewDate()">確定した日程を送信</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 4">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    求人企業様から{{ this.recruitApplyMgt.last_selection_flow_number == 12 ? 'オファー面談' : selectionText }}の日程候補日が提示されています。<br>
                    以下より日程調整を行ってください。
                </p>
            </div>
            <div class="icon-container">
                <i class="ti-check-box vA-middle"></i>
                <span>{{ this.recruitApplyMgt.last_selection_flow_number == 12 ? '内定：オファー面談' : prevSelectionText + '通過' }}</span>
            </div>
            <div v-if="this.recruitApplyMgt.last_selection_flow_number == 12">
                <p class="text pY-10 pL-30">入社条件などの調整のため、求人企業様からオファー面談の要請がありました。</p>
            </div>
            <div v-else>
                <p class="text pY-10 pL-30">{{ prevSelectionText }}の選考を通過いたしました。<br>
                次の選考は「{{ selectionText }}」となります。</p>
            </div>
            <div v-show="InterviewSchedules.length > 0">
                <p class="text pY-10 pL-10">
                    <span class="icon-holder"><i class="ti-calendar"></i> </span>
                    <span class="pL-10">提示された日程候補</span>
                </p>
                <p class="text pL-30" style="color: #2B7CBF;">
                    <div v-for="(schedule, index) in InterviewSchedules" v-html="formatInterviewDate(schedule.interview_candidates_date, schedule.interview_candidates_from, schedule.interview_candidates_to, schedule.interview_date_type)" />
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="linkCalendar(1)">日程調整へ</button>
            </div>
            <div class="mT-20 pX-10">
                <p class="text">タイムラインなどで調整して日程が確定している場合は、以下より確定日時をご入力いただいても設定することができます。</p>
                <p class="text" style="color:red">※こちらで設定すると確定した日程として求人企業様に即時送信されますのでご注意ください。</p>
                <div class="row mX-0 mY-10">
                    <div class="col-md-6 pX-0 pY-5" style="display:flex;align-items:center">
                        <label class="mB-0 pR-5" style="word-break:keep-all">日程 </label>
                        <div class="col-md-10 pL-0 pR-15" style="position:relative">
                            <input type="text" class="box-shadow col-md-12 p-10" id="fixed_interview_date" autocomplete="off" data-provide="datepicker">
                            <i class="ti-calendar place"></i>
                        </div>
                    </div>
                    <div class="col-md-6 pL-0 pY-5">時間 
                        <select name="fixed_time_from" class="box-shadow pX-5">
                            <option v-for="time in times" :value="time" :selected="time=='06:00'">{{ time }}</option>
                        </select>
                        ～
                        <select name="fixed_time_to" class="box-shadow pX-5">
                            <option v-for="time in times" :value="time" :selected="time=='06:00'">{{ time }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-outline-secondary" @click="sendFixedInterviewDate()">確定した日程を送信</button>
                </div>
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
                    <button type="button" class="col col-md-6 btn cur-p btn-outline-secondary" @click="showInterviewModal()">{{ selectionText }}の詳細を入力する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 6">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    [通過済み]<br>
                    求人企業様が{{ selectionText }}の面接詳細を設定しています。<br>
                    日程が届くまで今しばらくお待ちください。
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
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
                    <em>{{ confirmedDate }}</em>
                </p>
            </div>
            <div class="mT-20 pX-10">
                <p class="text">日程を変更する場合は、タイムラインで求人企業様と調整後に以下よりご変更ください。</p>
                <p class="text" style="color:red">※こちらで設定すると確定した日程として求人企業様に即時送信されますのでご注意ください。</p>
                <div class="row mX-0 mY-10">
                    <div class="col-md-6 pX-0 pY-5" style="display:flex;align-items:center">
                        <label class="mB-0 pR-5" style="word-break:keep-all">日程 </label>
                        <div class="col-md-10 pL-0 pR-15" style="position:relative">
                            <input type="text" class="box-shadow col-md-12 p-10" id="fixed_interview_date" autocomplete="off" data-provide="datepicker">
                            <i class="ti-calendar place"></i>
                        </div>
                    </div>
                    <div class="col-md-6 pL-0 pY-5">時間 
                        <select name="fixed_time_from" class="box-shadow pX-5">
                            <option v-for="time in times" :value="time" :selected="time=='06:00'">{{ time }}</option>
                        </select>
                        ～
                        <select name="fixed_time_to" class="box-shadow pX-5">
                            <option v-for="time in times" :value="time" :selected="time=='06:00'">{{ time }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="sendFixedInterviewDate()">変更後の日程を送信</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 8">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    [内定]<br>
                    求人企業様から内定されました、おめでとうございます！<br>
                    この後、求人企業様から「入社条件の提示」または「オファー面談の要請」が届きますので、今しばらくお待ちください。
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 9">
        <div class="card-body">
            <div class="prompt">
                <p class="text">
                    [内定：入社条件の確認]<br>
                    求人企業様から入社条件が届きました。<br>
                    以下より回答を行ってください。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">提示された入社条件</span>
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
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showJoinConditionModal()">入社条件を<br>交渉する</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmConditionModal()">入社条件に同意</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 10">
        <div class="card-body">
            <div class="prompt">
                <p class="text">
                    [内定：入社条件変更]<br>
                    求人企業様から入社条件が変更されました。<br>
                    以下よりご確認いただきご回答をお願い致します。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">前回提示された入社条件と変更後の入社条件</span>
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
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showJoinConditionModal()">入社条件を<br>交渉する</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmConditionModal()">入社条件に同意</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 11">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    求人企業様から提示された入社条件に対して、希望の条件を提示しました。<br>
                    回答が届くまで今しばらくお待ちください。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">求人企業様から提示された入社条</span>
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
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showJoinConditionModal()">入社条件を<br>交渉する</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmConditionModal()">入社条件に同意</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 12">
        <div class="card-body">
            <div class="icon-container mT-0"><i class="ti-face-smile"></i><span>ご採用おめでとうございます！</span></div>
            <p class="text mX-30 mY-10">
                貴社の営業活動に微力ながらもお手伝い出来たことを大変光栄に思っております。<br/>
                この後、転職者様が初日出勤をされましたら、貴社への成功報酬が確定しますので求人企業様からのご報告をお待ちください。
            </p>
            <div class="prompt prompt--success">
                <p class="icon">!</p>
                <p class="text mY-0">
                    <em>初日出勤の確認待ち</em><br>
                    <em>初日出勤予定日：{{ confirmedDate }}</em>
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showChangePresentDateModal()">新たな入社日を登録</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 13">
        <div class="card-body">
            <div class="prompt prompt--success">
                <p class="icon orange no-background pX-0">〇</p>
                <p class="text mY-5">
                    <em>採用（入社確定）</em><br>
                    <em>紹介成功報酬： {{ this.$offerInfo.success_reward_calculation_method == 1 ? '年収の '+parseFloat((this.$offerInfo.theory_annual_income==null || this.$offerInfo.theory_annual_income=="") ? 0 : this.$offerInfo.theory_annual_income)+' ％' : '一律固定報酬 ' + this.$offerInfo.theory_annual_income_definition + ' 万円' }}</em>
                </p>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 14">
        <div class="card-body">
            <div class="prompt">
                <p class="text">
                    採用（入社未確定）<br>
                    求人企業様から報告が届きました。<br>
                    以下よりご確認いただきご回答をお願い致します。
                </p>
            </div>
            <div class="row mX-20 pT-10">
                <p class="col-md">出勤状態</p>
                <div class="col-md">
                    <p>未出勤</p>
                </div>
            </div>
            <div class="row mX-20 mY-0">
                <p class="col-md">初日出勤予定日：</p>
                <div class="col-md">
                    <p>{{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].first_day_work_schedule_date) : '' }}</p>
                </div>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showChangePresentDateModal()">新たな入社日を登録</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 15">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text mY-10">
                    <b>[返金申請がありました]</b><br>
                    求人企業様から「返金規定期間内での退職による返金申請」がありました。<br>
                    人材紹介タイムラインより求人企業様へ直接連絡していただき、結果の回答を以下より行ってください。<br>
                    尚、返金対応は以下で同意後、人材紹介会社様より求人企業様へ直接お振込みをお願い致します。<br><br>

                    ■ 返金時のお支払いの流れ<br>
                    ①求人企業様から当社へ「紹介成功報酬の全額」をお支払い<br>
                    ②当社から人材紹介会社様へ「紹介成功報酬の全額」をお支払い<br>
                    ③人材紹介会社様から「求人企業様と合意いただいた返金額」を求人企業様へ直接ご返金<br><br>

                    ※求人企業様から当社へのお支払いにおいて、返金分を減額してのお支払い対応はシステム上できませんので、求人企業様へご案内されませんようご注意ください。
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
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRejectRefundModal()">同意しない</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmRefundModal()">返金に同意する</button>
            </div>
        </div>
    </div>

    <div class="card mb-2" v-if="nextOpType == 16">
        <div class="card-body">
            <div class="prompt prompt--success">
                <p class="icon orange no-background pX-0">〇</p>
                <p class="text mY-5">
                    <em>採用（入社確定）</em><br>
                    <em>紹介成功報酬： {{ this.$offerInfo.success_reward_calculation_method == 1 ? '年収の '+parseFloat((this.$offerInfo.theory_annual_income==null || this.$offerInfo.theory_annual_income=="") ? 0 : this.$offerInfo.theory_annual_income)+' ％' : '一律固定報酬 ' + this.$offerInfo.theory_annual_income_definition + ' 万円' }}</em><br>
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
                    <em>採用（入社確定）</em><br>
                    <em>紹介成功報酬： {{ this.$offerInfo.success_reward_calculation_method == 1 ? '年収の '+parseFloat((this.$offerInfo.theory_annual_income==null || this.$offerInfo.theory_annual_income=="") ? 0 : this.$offerInfo.theory_annual_income)+' ％' : '一律固定報酬 ' + this.$offerInfo.theory_annual_income_definition + ' 万円' }}</em><br>
                    <em>（返金なし。 返金不同意日： {{ formatDate(recruitApplyMgt.refund_disagreement_date) }}）</em>
                </p>
            </div>
        </div>
    </div>

    <!-- begin modal -->
    <div class="modal fade" id="refusalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header no-border pB-0">
                    <h5 class="modal-title pT-20 w-100 text-sm-center">選考辞退の理由で最も近いものを１つ選択してください。<span class="badge">必須</span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pX-40 pY-10">

                    <div class="sub-content mT-0">
                        <div class="row sp_block mX-0">
                            <div v-for="(reason, index) in this.$enumRecruitRefusalReasons" class="col col-md-3 pX-0">
                                <div class="radio-container">
                                    <input class="" type="radio" name="refusal_reason" :id="'radio1-'+index" :value="index">
                                    <label class="form-check-label" :for="'radio1-'+index" style="white-space: break-spaces;">
                                        {{ reason }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sub-content mT-0">
                        <div class="text mT-20 mX-10 mB-0 pB-5">具体的な理由をお教えください。<span class="badge">必須</span></div>
                        <textarea id="unseated_cause_detail" class="textarea box-shadow mX-0 p-10" rows="3" ></textarea>
                    </div>

                    <p class="text mT-10 mB-0">ご入力いただいた内容は、求人票の改善や候補者とのミスマッチ防止に利用いたします。</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="col btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="col btn btn-primary" @click="submitRefusalReason()">送信</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="joiningConditionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <h5 class="mL-10 modal-title">入社条件を交渉する</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <p class="mB-0">◯ 求人企業から提示された入社条件</p>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">オファー金額（円）</div>
                        <div class="col-md-8 pL-0">
                            年収 {{ this.joiningConditions.length > 0 ? formatDigits(this.joiningConditions[0].offer_amount) : 0 }} 円
                        </div>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">初日出勤日</div>
                        <div class="col-md-4 pL-0">
                            {{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].first_day_attendance_date) : '' }}
                        </div>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">返答期限</div>
                        <div class="col-md-4 pL-0">
                            {{ this.joiningConditions.length > 0 ? formatDate(this.joiningConditions[0].reply_deadline) : '' }}
                        </div>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">入社手続きに必要な書類</div>
                        <div class="col-md-8 pL-0">
                            <p class="mB-0" v-for="(file, index) in this.joinConditionAttachments">{{ file.attachment_name }}
                                <a class="link mL-20" :href="'/storage/recruit/attachment/'+file.attachment" target="_blank">
                                    <i class="link-icon ti-new-window"></i>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-body pY-10">
                    <p class="mB-0">◯ 転職者が希望する入社条件</p>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            希望年収（円）<span class="badge">必須</span>
                        </div>
                        <div class="col-md-8 pL-0">
                            年収<input class="box-shadow mX-10 col-md-5 p-10 hide-arrow" id="job_changer_desired_annual_income" type="number" min="0" oninput="validity.valid||(value='');">円
                        </div>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            初日出勤日<span class="badge">必須</span>
                        </div>
                        <div class="col-md-4 pL-0">
                            <input type="text" class="box-shadow col-md-12 p-10" id="job_changer_first_day_attendance_date" autocomplete="off" data-provide="datepicker">
                            <i class="ti-calendar place"></i>
                        </div>
                    </div>
                    <div class="row mX-20 mY-10">
                        <div class="col-md-4 pL-0 pY-5">
                            その他希望欄<span class="badge blue">任意</span>
                        </div>
                        <textarea class="box-shadow col-md-8 p-10" rows="3" id="other_desired"></textarea>
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
                    求人企業様から提示された入社条件に同意します。宜しいですか？
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

    <div class="modal fade" id="confirmRefundModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="icon-holder"><i class="ti-write"></i> </span>
                    <h5 class="mL-10 modal-title">紹介成功報酬の返金に同意する</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pY-10">
                    <div class="row mX-0 mY-15">
                        <div class="col-md-4 pL-0 pY-5">
                            返金額（円）<span class="badge">必須</span>
                        </div>
                        <div class="col-md-8 pL-0">
                            <input class="box-shadow mX-10 w-75 p-10 hide-arrow" id="refund_amount" type="number" min="0" oninput="validity.valid||(value='');">
                            円<br>
                        </div>
                    </div>
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="sendAgreeRefund(true)">送信</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectRefundModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    求人企業様からの返金申請について「同意しない」として返答します。宜しいですか？
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="sendAgreeRefund(false)">送信</button>
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

            refusal_reason: '',
            times: []
        }
    },
    created() {
        for (let i=0; i<24; i++) {
            for (let j=0; j<60; j+=10) {
                this.times.push(String(i).padStart(2, '0')+':'+String(j).padStart(2, '0'));
            }
        }
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
        prevSelectionText() {
            let currentStep = this.recruitApplyMgt.last_selection_flow_number;
            if (this.selectionResults == null) {
                return '';
            }
            for (var i=0; i<this.selectionResults.length; i++) {
                if (this.selectionResults[i].next_phase == currentStep) {
                    return this.$enumRecruitSelectionFlows[this.selectionResults[i].phase];
                }
            }
            return '';
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
            let calendar1 = '/agent/calendar1?id='+this.recruitApplyMgt.id;
            let calendar2 = '/agent/calendar2?id='+this.recruitApplyMgt.id;

            if (this.interviewSchedules == null) {
                return calendar2;
            }
            for (var i=0; i<this.interviewSchedules.length; i++)
            {
                if (this.interviewSchedules[i].interview_phase == this.recruitApplyMgt.last_selection_flow_number)
                {
                    if (this.interviewSchedules[i].interview_date_type == 1) {  // 1:候補日
                        return calendar1;
                    }
                }
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
        // 選考辞退
        showRefusalModal() {
            this.refusal_reason = '';

            $("#refusalModal input[name=refusal_reason]").filter(":checked").prop('checked', false);
            $("#refusalModal textarea[id=unseated_cause_detail]").val('');

            $('#refusalModal').modal('show');
        },
        // 入社条件を交渉する
        showJoinConditionModal() {
            $('#joiningConditionModal').modal('show');
        },
        // 入社条件に同意
        showConfirmConditionModal() {
            $('#confirmConditionModal').modal('show');
        },
        // 初日出勤日の変更
        showChangePresentDateModal() {
            $('#changePresentDateModal').modal('show');
        },
        // 返金に同意する
        showConfirmRefundModal() {
            $('#confirmRefundModal').modal('show');
        },
        // 返金に不同意する
        showRejectRefundModal() {
            $('#rejectRefundModal').modal('show');
        },
        // 選考辞退 - 送信
        submitRefusalReason() {
            let mainRadio        = $("#refusalModal input[name=refusal_reason]");
            let selectMainReason = mainRadio.filter(":checked").val();

            let refusal_detail_reasons = $("#refusalModal textarea[id=unseated_cause_detail]").val();

            if (selectMainReason == null || selectMainReason.trim() == '') {
                alert('選考辞退の理由を選択ください。');
                return;
            }
            if (refusal_detail_reasons == null || refusal_detail_reasons.trim() == '') {
                alert('具体的な理由を入力してください。');
                return;
            }

            //const store = useStore();
            store.commit('selection_refusal', {
                reason: selectMainReason,
                details: refusal_detail_reasons
            });

            $('#refusalModal').modal('hide');
        },
        // 確定した日程を送信
        sendFixedInterviewDate() {
            let interviewDate     = $("input[id=fixed_interview_date]").val();
            let interviewTimeFrom = $("select[name=fixed_time_from]").val();
            let interviewTimeTo   = $("select[name=fixed_time_to]").val();

            if (interviewDate == "" || interviewTimeFrom == "" || interviewTimeFrom == undefined || interviewTimeTo == "" || interviewTimeTo == undefined) {
                alert('確定した日程を入力してください。');
                return;
            }
            if (interviewTimeFrom.toString().localeCompare(interviewTimeTo.toString()) >= 0) {
                alert('時間を正確に入力してください。');
                return;
            }
            store.commit('fixed_interview_date', {
                interviewDate: interviewDate,
                interviewTimeFrom: interviewTimeFrom,
                interviewTimeTo: interviewTimeTo
            });
        },
        // 日程調整へ
        linkCalendar(type) {
            window.location.href = '/agent/calendar'+type+'?id='+this.recruitApplyMgt.id;
        },
        // 入社条件を交渉する - OK
        sendJoiningCondition() {
            let annualIncome =           $("#joiningConditionModal input[id=job_changer_desired_annual_income]").val();
            let firstDayAttendanceDate = $("#joiningConditionModal input[id=job_changer_first_day_attendance_date]").val();
            let other_desired =          $("#joiningConditionModal textarea[id=other_desired]").val();

            if (annualIncome == "" || firstDayAttendanceDate == "") {
                alert('必須項目を入力してください。');
                return;
            }

            store.commit('send_joining_condition', {
                annualIncome: annualIncome,
                firstDayAttendanceDate: firstDayAttendanceDate,
                other_desired: other_desired,
            });

            $('#joiningConditionModal').modal('hide');
        },
        // 入社条件に同意 - OK
        submitAllow() {
            store.commit('allow_joining_condition', {
            });
            $('#confirmConditionModal').modal('hide');
        },
        // 初日出勤日の変更 - OK
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
        // 返金に同意・不同意する - OK
        sendAgreeRefund(isAgree) {
            let refundAmount = $("#confirmRefundModal input[id=refund_amount]").val();
            if (isAgree && (refundAmount == "" || refundAmount == 0)) {
                alert('返金額を入力してください。');
                return;
            }
            store.commit('agree_refund', {
                refundAmount: refundAmount,
                isAgree: isAgree,
            });
            $('#confirmRefundModal').modal('hide');
            $('#rejectRefundModal').modal('hide');
        },
    },
    mounted() {
        this.currentStep = this.recruitApplyMgt.last_selection_flow_number;
    },

}
</script>

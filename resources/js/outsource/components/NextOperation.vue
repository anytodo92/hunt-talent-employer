<template>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 1">
        <div class="card-body">
            <div class="prompt prompt--info" v-if="this.outsourceApplyMgt.last_selection_flow_number == 3">
                <p class="text">
                    [エントリー済み]<br>
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

    <!-- checked -->
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

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 3">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    求人企業様から{{ selectionText }}の希望日程の提示を求められています。<br>
                    以下より日程調整を行ってください。
                </p>
            </div>
            <div class="icon-container">
                <i class="ti-check-box vA-middle"></i>
                <span class="vA-middle">{{ prevSelectionText + '通過' }}</span>
            </div>
            <div>
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

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 4">
        <div class="card-body">
            <div class="prompt">
                <p class="icon">!</p>
                <p class="text">
                    求人企業様から{{ selectionText }}の日程候補日が提示されています。<br>
                    以下より日程調整を行ってください。
                </p>
            </div>
            <div class="icon-container">
                <i class="ti-check-box vA-middle"></i>
                <span>{{ prevSelectionText + '通過' }}</span>
            </div>
            <div>
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

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 6">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    [通過済み]<br>
                    求人企業様が{{ selectionText }}の面談詳細を設定しています。<br>
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

    <!-- checked -->
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

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 8">
        <div class="card-body">
            <div class="prompt prompt--info">
                <p class="text">
                    [オファー]<br>
                    求人企業様からオファーされました、おめでとうございます！<br>
                    この後、求人企業様から「契約条件の提示」がありますので、今しばらくお待ちください。
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <div class="col ta-c">
                    <button type="button" class="col col-md-6 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                </div>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 9">
        <div class="card-body">
            <div class="prompt">
                <p class="text">
                    [オファー：契約条件の確認]<br>
                    求人企業様から契約条件が届きました。<br>
                    以下より回答を行ってください。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">提示された契約条件</span>
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
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmConditionModal()">契約条件に同意</button>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 10">
        <div class="card-body">
            <div class="prompt">
                <p class="text">
                    [オファー：契約条件変更]<br>
                    求人企業様から契約条件が変更されました。<br>
                    以下よりご確認いただきご回答をお願い致します。
                </p>
            </div>
            <div>
                <p class="text pT-10 pL-10">
                    <span class="icon-holder"><i class="ti-medall"></i> </span>
                    <span class="pL-10">前回提示された契約条件と変更後の契約条件</span>
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
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showConfirmConditionModal()">契約条件に同意</button>
            </div>
        </div>
    </div>

    <!-- checked -->
    <div class="card mb-2" v-if="nextOpType == 12">
        <div class="card-body">
            <div class="icon-container mT-0"><i class="ti-face-smile"></i><span>ご契約おめでとうございます！</span></div>
            <p class="text mX-30 mY-10">
                貴社の営業活動に微力ながらもお手伝い出来たことを大変光栄に思っております。<br/>
                参画開始日を経過して参画が開始された後は、毎月稼働時間数と請求金額をご入力いただき、求人企業様へ当社よりご請求、その後当社から貴社へお支払いする流れとなります。
            </p>
            <div class="prompt prompt--success">
                <p class="icon">!</p>
                <p class="text mY-0">
                    <em>参画開始待ち</em><br>
                    <span>参画開始日：{{ confirmedDate }}</span>
                </p>
            </div>
            <div class="row mT-20 pX-10">
                <button type="button" class="col mX-5 btn cur-p btn-outline-secondary" @click="showRefusalModal()">選考辞退</button>
                <button type="button" class="col mX-5 btn cur-p btn-primary" @click="showChangeStartDateModal()">参画開始日の変更</button>
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
                    求人企業様より以下の日程で「参画終了申請」が届いています。<br>
                    確認されましたら確認済みボタンの押下をお願いします。<br>
                    （求人企業様への確認や連絡事項がある場合はタイムラインのチャットよりご連絡をお願い致します）<br>
                    <span class="red">※確認済みボタンを押下しない場合でも日付経過後には自動的に参画終了となりますのでご注意ください。</span>
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
    <div class="card mb-2" v-if="nextOpType == 15">
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
                            <div v-for="(reason, index) in this.$enumOutsourceRefusalReasons" class="col col-md-3 pX-0">
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

                    <p class="text mT-10 mB-0">ご入力いただいた内容は、案件情報の改善や候補者とのミスマッチ防止に利用いたします。</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="col btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="col btn btn-primary" @click="submitRefusalReason()">送信</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmConditionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    求人企業様から提示された契約条件に同意します。宜しいですか？
                </div>
                <div class="modal-footer align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" @click="submitAllow()">同意する</button>
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
            outsourceApplyMgt : state => state.outsourceApplyMgt,
            selectionResults : state => state.selectionResults,
            timelines : state => state.timelines,
            interviewSchedules : state => state.interviewSchedules,
            contractTerms : state => state.contractTerms,
        }),
        selectionText() {
            let currentStep = this.outsourceApplyMgt.last_selection_flow_number;
            const result = this.$enumOutsourceSelectionFlows[currentStep];
            if (result == null) {
                console.warn('NextOperation.vue/selectionText() currentStep is out of range.');
                return '〇〇〇';
            }

            return result;
        },
        prevSelectionText() {
            let currentStep = this.outsourceApplyMgt.last_selection_flow_number;
            if (this.selectionResults == null) {
                return '';
            }
            for (var i=0; i<this.selectionResults.length; i++) {
                if (this.selectionResults[i].next_phase == currentStep) {
                    return this.$enumOutsourceSelectionFlows[this.selectionResults[i].phase];
                }
            }
            return '';
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
            let calendar1 = '/ses/calendar1?id='+this.outsourceApplyMgt.id;
            let calendar2 = '/ses/calendar2?id='+this.outsourceApplyMgt.id;

            if (this.interviewSchedules == null) {
                return calendar2;
            }
            for (var i=0; i<this.interviewSchedules.length; i++)
            {
                if (this.interviewSchedules[i].interview_phase == this.outsourceApplyMgt.last_selection_flow_number)
                {
                    if (this.interviewSchedules[i].interview_date_type == 1) {  // 1:候補日
                        return calendar1;
                    }
                }
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
        // 選考辞退
        showRefusalModal() {
            this.refusal_reason = '';

            $("#refusalModal input[name=refusal_reason]").filter(":checked").prop('checked', false);
            $("#refusalModal textarea[id=unseated_cause_detail]").val('');

            $('#refusalModal').modal('show');
        },
        // 契約条件に同意
        showConfirmConditionModal() {
            $('#confirmConditionModal').modal('show');
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
            window.location.href = '/ses/calendar'+type+'?id='+this.outsourceApplyMgt.id;
        },
        // 契約条件に同意 - OK
        submitAllow() {
            store.commit('allow_joining_condition', {
            });
            $('#confirmConditionModal').modal('hide');
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

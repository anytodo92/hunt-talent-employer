<template>
    <div class="card mb-2">
        <div class="card-title mB-0">
            <span class="icon-holder mR-10"><i class="ti-control-shuffle"></i> </span>
            <span class="title">選考の進捗状況</span>
        </div>
        <div class="card-body pY-10">
            <div class="breadcrumb">
                <span
                    v-for="(index, idx) in flow"
                    :key="idx"
                    :id="'step'+idx"
                    :class="['breadcrumb__step',
                        { 'breadcrumb__step--active': index == selectedStep },
                        { 'breadcrumb__step--disabled' : index > recruitApplyMgt.last_selection_flow_number && index != 14 } ]"
                    @click="updateDetails(index)"
                >{{ steps[idx] }}</span>
            </div>
            <div class="content">
                <div class="row mX-0  mY-0" v-for="content in currentContent" :key="content">
                    <div class="breadcrumb_detail_title">
                        <p class="mY-5" style="white-space: break-spaces">{{ content.name }}</p>
                    </div>
                    <div class="breadcrumb_detail_content">
                        <p class="mY-5" style="white-space: break-spaces">{{ content.value }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import moment from 'moment'
    import {mapState} from "vuex";
    export default {
        props: {
            selectionFlow: String,
        },
        computed: {
            ...mapState({
                recruitApplyMgt : state => state.recruitApplyMgt,
                selectionResults : state => state.selectionResults,
                timelines : state => state.timelines,
                interviewSchedules : state => state.interviewSchedules,
                joiningConditions : state => state.joiningConditions,
                joinConditionAttachments : state => state.joinConditionAttachments,
            }),
            allContents() {
                let currrentStep = 0;
                let data = [];
                let retSelection = [];
                let results = [];

                const lastFlow = this.recruitApplyMgt.last_selection_flow_number;
                if (lastFlow < 3) // 書類選考の以前
                    return;

                // 書類選考
                if (lastFlow >= 3) {
                    currrentStep = 3;
                    retSelection = this.getSelectionResult(currrentStep);
                    switch (this.recruitApplyMgt.applicant_screening) {
                        case 3://辞退
                            data = [
                                {name : '辞退日', value : this.recruitApplyMgt.applicant_screening_refusal_reason_date != null ? this.formatDate(this.recruitApplyMgt.applicant_screening_refusal_reason_date) : 'ー'},
                                {name : '選考結果', value : this.$enumRecruitAppScreeningStatuses[this.recruitApplyMgt.applicant_screening]},
                                {name : '辞退理由', value : this.recruitApplyMgt.applicant_screening_refusal_reason != null ? this.recruitApplyMgt.applicant_screening_refusal_reason : 'ー'},
                            ];
                            break;
                        case 4://不採用
                            data = [
                                {name : '選考結果', value : this.$enumRecruitAppScreeningStatuses[this.recruitApplyMgt.applicant_screening]},
                                {name : '不採用日', value : this.recruitApplyMgt.applicant_screening_not_adopted_date != null ? this.formatDate(this.recruitApplyMgt.applicant_screening_not_adopted_date) : 'ー'},
                            ];
                            break;
                        default:
                            data = [
                                {name : '応募確定日', value : this.recruitApplyMgt.application_date != null ? this.formatDate(this.recruitApplyMgt.application_date) : 'ー'},
                                {name : '選考結果', value : this.recruitApplyMgt.applicant_screening != null ? this.$enumRecruitAppScreeningStatuses[this.recruitApplyMgt.applicant_screening] : 'ー'},
                                {name : '結果通知日', value : this.recruitApplyMgt.applicant_screening_date != null ? this.formatDate(this.recruitApplyMgt.applicant_screening_date) : 'ー'},
                                {name : '次回の選考', value : retSelection != null ? (retSelection['next_phase'] != null ? this.$enumRecruitSelectionFlows[retSelection['next_phase']] : 'ー') : 'ー'},
                                {name : '評価', value : retSelection != null ? (retSelection['current_evaluation'] != null ? this.$enumRecruitEvaluations[retSelection['current_evaluation']] : 'ー'): 'ー'},
                            ];
                            break;
                    }

                    results.push({step: 3, data: data});
                }

                // 筆記、webテスト
                if (lastFlow >= 4) {
                    currrentStep = 4;
                    retSelection = this.getSelectionResult(currrentStep);

                    switch (this.recruitApplyMgt.writing_web_test) {
                        case 3://辞退
                            data = [
                                {name : '辞退日', value : this.recruitApplyMgt.writing_web_test_refusal_reason_date != null ? this.formatDate(this.recruitApplyMgt.writing_web_test_refusal_reason_date) : 'ー'},
                                {name : '選考結果', value : this.$enumRecruitInterviewStatuses[this.recruitApplyMgt.writing_web_test]},
                                {name : '辞退理由', value : this.recruitApplyMgt.writing_web_test_refusal_reason != null ? this.recruitApplyMgt.writing_web_test_refusal_reason : 'ー'},
                            ];
                            break;
                        case 4://不採用
                            data = [
                                {name : '選考結果', value : this.$enumRecruitInterviewStatuses[this.recruitApplyMgt.writing_web_test]},
                                {name : '不採用日', value : this.recruitApplyMgt.writing_web_test_not_adopted_date != null ? this.formatDate(this.recruitApplyMgt.writing_web_test_not_adopted_date) : 'ー'},
                            ];
                            break;
                        default:
                            data = [
                                {name : '日程', value : this.recruitApplyMgt.writing_web_test_date != null ? this.formatDate(this.recruitApplyMgt.writing_web_test_date) : 'ー'},
                                {name : '選考結果', value : this.recruitApplyMgt.writing_web_test != null ? this.$enumRecruitInterviewStatuses[this.recruitApplyMgt.writing_web_test] : 'ー'},
                                {name : '詳細', value : retSelection != null ? ('面接担当者名 : ' + (retSelection['interviewer'] != null ? retSelection['interviewer'] : 'ー') + '\r\n' +
                                        '面接場所住所 : ' + (retSelection['interview_address'] != null ? retSelection['interview_address'] : 'ー') + '\r\n' +
                                        '持ち物 : ' + (retSelection['belongings'] != null ? retSelection['belongings'] : 'ー') + '\r\n' +
                                        '緊急連絡先 : ' + (retSelection['emergency_contact_address'] != null ? retSelection['emergency_contact_address'] : 'ー') + '\r\n' +
                                        'その他特記事項 : ' + (retSelection['else_special_note'] != null ? retSelection['else_special_note'] : 'ー')) : 'ー'},
                            ];
                            break;
                    }
                    results.push({step: 4, data: data});
                }

                // 面談、1次面接 ~ 5次面接、最終面接
                if (lastFlow >= 5) {
                    const atom_keys = ['', '1st_', '2nd_', '3rd_', '4th_', '5th_', 'last_'];
                    for (let i=0; i<atom_keys.length /*&& lastFlow < 4 + i*/; i++) {
                        currrentStep = 5 + i;
                        retSelection = this.getSelectionResult(currrentStep);

                        switch (this.recruitApplyMgt[atom_keys[i] + 'interview']) {
                            case 3://辞退
                                data = [
                                    {name : '辞退日', value : this.recruitApplyMgt[atom_keys[i] + 'refusal_reason_date'] != null ? this.formatDate(this.recruitApplyMgt[atom_keys[i] + 'refusal_reason_date']) : 'ー'},
                                    {name : '選考結果', value : this.$enumRecruitInterviewStatuses[this.recruitApplyMgt[atom_keys[i] + 'interview']]},
                                    {name : '辞退理由', value : this.recruitApplyMgt[atom_keys[i] + 'refusal_reason'] != null ? this.recruitApplyMgt[atom_keys[i] + 'refusal_reason'] : 'ー'},
                                ];
                                break;
                            case 4://不採用
                                data = [
                                    {name : '選考結果', value : this.$enumRecruitInterviewStatuses[this.recruitApplyMgt[atom_keys[i] + 'interview']]},
                                    {name : '不採用日', value : this.recruitApplyMgt[atom_keys[i] + 'not_adopted_date'] != null ? this.formatDate(this.recruitApplyMgt[atom_keys[i] + 'not_adopted_date']) : 'ー'},
                                ];
                                break;
                            default:
                                data = [
                                    {name : '面接日程', value : this.recruitApplyMgt[atom_keys[i] + 'interview_date'] != null ? this.formatDate(this.recruitApplyMgt[atom_keys[i] + 'interview_date']) : 'ー'},
                                    {name : '選考結果', value : this.recruitApplyMgt[atom_keys[i] + 'interview'] != null ? this.$enumRecruitInterviewStatuses[this.recruitApplyMgt[atom_keys[i] + 'interview']] : 'ー'},
                                    {name : '面接詳細', value : retSelection != null ? ('面接担当者名 : ' + (retSelection['interviewer'] != null ? retSelection['interviewer'] : 'ー') + '\r\n' +
                                            '面接場所住所 : ' + (retSelection['interview_address'] != null ? retSelection['interview_address'] : 'ー') + '\r\n' +
                                            '持ち物 : ' + (retSelection['belongings'] != null ? retSelection['belongings'] : 'ー') + '\r\n' +
                                            '緊急連絡先 : ' + (retSelection['emergency_contact_address'] != null ? retSelection['emergency_contact_address'] : 'ー') + '\r\n' +
                                            'その他特記事項 : ' + (retSelection['else_special_note'] != null ? retSelection['else_special_note'] : 'ー')) : 'ー'},
                                ];
                                break;
                        }
                        results.push({step: 5 + i, data: data});
                    }
                }

                // 採用
                if (lastFlow >= 12) {
                    currrentStep = 12;
                    data = [
                        {name : '内定日', value : this.recruitApplyMgt['recruitment_date'] != null ? this.formatDate(this.recruitApplyMgt['recruitment_date']) : 'ー'},
                        {name : '入社条件', value : this.joiningConditions.length > 0 ? ('オファー金額 : ' + (this.joiningConditions[0].offer_amount != null ? this.formatDigits(this.joiningConditions[0].offer_amount) + '円' : 'ー') + '\r\n' +
                                '初日出勤日 : ' + (this.joiningConditions[0].first_day_attendance_date != null ? this.formatDate(this.joiningConditions[0].first_day_attendance_date) : 'ー') + '\r\n' +
                                '返答期限 : ' + (this.joiningConditions[0].reply_deadline != null ? this.formatDate(this.joiningConditions[0].reply_deadline) : 'ー')) : 'ー'},
                    ];
                    results.push({step: 12, data: data});
                }

                // 入社確認
                if (lastFlow >= 13) {
                    currrentStep = 13;
                    switch (this.recruitApplyMgt.joining_confirmation) {
                        case 2://辞退
                            data = [
                                {name : '辞退日', value : this.recruitApplyMgt.joining_confirmation_refusal_reason_date != null ? this.formatDate(this.recruitApplyMgt.joining_confirmation_refusal_reason_date) : 'ー'},
                                {name : '選考結果', value : this.$enumRecruitJoiningStatuses[this.recruitApplyMgt.joining_confirmation]},
                                {name : '辞退理由', value : this.recruitApplyMgt.joining_confirmation_refusal_reason != null ? this.recruitApplyMgt.joining_confirmation_refusal_reason : 'ー'},
                            ];
                            break;
                        case 3://不採用
                            data = [
                                {name : '選考結果', value : this.$enumRecruitJoiningStatuses[this.recruitApplyMgt.joining_confirmation]},
                                {name : '不採用日', value : this.recruitApplyMgt.joining_confirmation_not_adopted_date != null ? this.formatDate(this.recruitApplyMgt.joining_confirmation_not_adopted_date) : 'ー'},
                            ];
                            break;
                        default:
                            data = [
                                {name : '入社日', value : this.joiningConditions.length > 0 ? (this.joiningConditions[0].first_day_work_schedule_date != null ? this.formatDate(this.joiningConditions[0].first_day_work_schedule_date) : 'ー') : 'ー'},
                                {name : '入社確認日', value : this.recruitApplyMgt.joining_confirmation_date != null ? this.formatDate(this.recruitApplyMgt.joining_confirmation_date) : 'ー'},
                            ];
                            break;
                    }
                    results.push({step: 13, data: data});
                }

                // 「入社後（採用後）の「紹介成功報酬の返金申請」」時に保存
                // ここがNOTNULLの場合、返金申請されたとみなす
                if (this.recruitApplyMgt.retirement_date != null) {
                    currrentStep = 14;
                    data = [
                        {name : '退社日', value : this.recruitApplyMgt['retirement_date'] != null ? this.formatDate(this.recruitApplyMgt['retirement_date']) : 'ー'},
                        {name : '返金申請日', value : this.recruitApplyMgt['refund_apply_date'] != null ? this.formatDate(this.recruitApplyMgt['refund_apply_date']) : 'ー'},
                    ];
                    if (this.recruitApplyMgt.refund_status == 3/*3:同意しなかった状態*/) {
                        data.push({name : '返金不同意日', value : this.recruitApplyMgt.refund_disagreement_date != null ? this.formatDate(this.recruitApplyMgt.refund_disagreement_date) : 'ー'});
                    }
                    else {
                        data.push({name : '申請承認日', value : this.recruitApplyMgt.refund_agreement_date != null ? this.formatDate(this.recruitApplyMgt.refund_agreement_date) : 'ー'});
                    }
                    results.push({step: 14, data: data});
                }

                return results;
            },
            currentContent() {
                let contents = this.allContents;
                if (contents != undefined && contents.length > 0) {
                    for (let i = 0; i < contents.length; i++) {
                        if (contents[i].step == this.selectedStep) {
                            return contents[i].data;
                        }
                    }
                }
                return [];
            }
        },
        data() {
            return {
                mgt_id: 0,
                selectedStep: 0,
                steps: [/*'書類選考', '筆記、webテスト', '面談', '１次面接', '２次面接', '３次面接', '４次面接', '５次面接', '最終選考', '内定', '入社', '退社(返金規定内)'*/],
                flow: [],
            }
        },
        created() {
            moment.locale('ja');
        },
        methods: {
            autoScrollBreadcrumb() {
                const slider = document.querySelector('.breadcrumb');
                let scrollX = -20;
                for (let i = 0; i < this.flow.length; i++) {
                    if (parseInt(this.flow[i]) < this.selectedStep) {
                        scrollX += $('span#step'+i).width() + 46 + 10;
                    }
                    if (this.flow[i] == this.selectedStep) {
                        //slider.scrollLeft = 1100 / 13.0 * i;
                        slider.scrollLeft = scrollX - (slider.clientWidth-$('span#step'+i).width())/2 + 56;
                        return;
                    }
                }
            },
            updateDetails(index) {
                if (this.selectedStep != index && index <= this.recruitApplyMgt.last_selection_flow_number || index == 14) {
                    this.selectedStep = index;
                    this.autoScrollBreadcrumb();
                }
            },
            getSelectionResult(curPhase) {
                let ret = [];

                for (let i = 0; i < this.selectionResults.length; i++) {
                    if (this.selectionResults[i].phase == curPhase) {

                        ret['next_phase'] = this.selectionResults[i].next_phase;
                        ret['current_evaluation'] = this.selectionResults[i].current_evaluation;
                        ret['interviewer'] = this.selectionResults[i].interviewer;
                        ret['interview_address'] = this.selectionResults[i].interview_address;
                        ret['belongings'] = this.selectionResults[i].belongings;
                        ret['emergency_contact_address'] = this.selectionResults[i].emergency_contact_address;
                        ret['else_special_note'] = this.selectionResults[i].else_special_note;

                        return ret;
                    }
                }
                return null;
            },
            formatDigits(value) {
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            },
            formatDate(date) {
                // 2021年 1月 1日 (月)
                return moment(new Date(date)).format("YYYY年M月D日 (ddd)");
            },
            getFlows() {
                this.steps = [];
                this.flow = [];

                const selectionFlowArr = this.selectionFlow.split(",");

                for (let i=2; i<selectionFlowArr.length; i++) {
                    if (selectionFlowArr[i] < this.recruitApplyMgt.last_selection_flow_number && this.getSelectionResult(selectionFlowArr[i]) == null) {
                        continue;
                    }
                    this.steps.push(this.$enumRecruitSelectionFlows[selectionFlowArr[i]]);
                    this.flow.push(selectionFlowArr[i]);
                }
                if (this.recruitApplyMgt.retirement_date != null) {
                    this.steps.push(this.$enumRecruitSelectionFlows[14]);
                    this.flow.push(14);
                }

                this.selectedStep = this.recruitApplyMgt.last_selection_flow_number;
                if (this.recruitApplyMgt.retirement_date != null) {
                    this.selectedStep = 14;
                }
            }
        },
        mounted() {
            this.mgt_id = this.id;

            this.getFlows();

            this.$nextTick(function () {
                const slider = document.querySelector('.breadcrumb');
                let isDown = false;
                let startX;
                let scrollLeft;

                slider.addEventListener('mousedown', (e) => {
                    isDown = true;
                    slider.classList.add('active');
                    startX = e.pageX - slider.offsetLeft;
                    scrollLeft = slider.scrollLeft;
                });
                slider.addEventListener('mouseleave', () => {
                    isDown = false;
                    slider.classList.remove('active');
                });
                slider.addEventListener('mouseup', () => {
                    isDown = false;
                    slider.classList.remove('active');
                });
                slider.addEventListener('mousemove', (e) => {
                    if(!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - slider.offsetLeft;
                    const walk = (x - startX) * 1; //scroll-fast
                    slider.scrollLeft = scrollLeft - walk;
                });

                this.autoScrollBreadcrumb();
            })
        },
        watch: {
            recruitApplyMgt: function(newSelectedType) {
                this.getFlows();

                this.selectedStep = this.recruitApplyMgt.last_selection_flow_number;
                if (this.recruitApplyMgt.retirement_date != null) {
                    this.selectedStep = 14;
                }
                this.autoScrollBreadcrumb();
            }
        }
    }


</script>

<style lang="scss">

.breadcrumb {
    text-align: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, .25);
    overflow: hidden;
    border-radius: 5px;
    counter-reset: flag;
    padding: unset !important;
    flex-wrap: unset !important;

    &__step {
        cursor: pointer;
        white-space: nowrap;
        text-decoration: none;
        outline: none;
        display: block;
        float: left;
        font-size: 14px;
        font-weight: 500;
        line-height: 46px;
        padding: 0px 10px 0px 46px;
        position: relative;
        background: var(--breadcrumb-theme-2);
        color: var(--breadcrumb-theme-1);
        transition: background .5s;
        width: 40%;

        &:first-child {
            padding-left: 26px;
            border-radius: 5px 0 0 5px;

            &::before {
            left: 14px;
            }
        }

        &:last-child {
            border-radius: 0 5px 5px 0;
            padding-right: 20px;

            &::after {
            content: none;
            }
        }
        &::after {
            overflow: hidden;
            content: '';
            position: absolute;
            top: 0;
            right: -23px;
            width: 46px;
            height: 46px;
            transform: scale(0.707) rotate(45deg);
            z-index: 1;
            border-radius: 0 3px 0 50px;
            background: var(--breadcrumb-theme-2);
            transition: background .5s;
            box-shadow: 6px -6px 0 6px var(--breadcrumb-theme-3);
        }

        &:hover,
        &--active,
        &:hover::after,
        &--active::after {
            background: var(--breadcrumb-theme-1);
        }

        &:hover,
        &--active {
            color: var(--breadcrumb-theme-2);
        }

        &--disabled {
            background: #d8d8d86b;
            color: #2b7cbfa3;

            &:after {
                background: #d8d8d86b;
            }
            &:hover, &:hover:after {
                background: #d8d8d86b;
                color: #2b7cbfa3;
            }
        }
    }
}

</style>

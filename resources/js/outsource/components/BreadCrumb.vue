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
                        { 'breadcrumb__step--disabled' : index > outsourceApplyMgt.last_selection_flow_number } ]"
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
                outsourceApplyMgt : state => state.outsourceApplyMgt,
                selectionResults : state => state.selectionResults,
                timelines : state => state.timelines,
                interviewSchedules : state => state.interviewSchedules,
                contractTerms : state => state.contractTerms,
            }),
            allContents() {
                let currrentStep = 0;
                let data = [];
                let retSelection = [];
                let results = [];

                const lastFlow = this.outsourceApplyMgt.last_selection_flow_number;
                if (lastFlow < 3) // 書類選考の以前
                    return;

                // 書類選考
                if (lastFlow >= 3) {
                    currrentStep = 3;
                    retSelection = this.getSelectionResult(currrentStep);
                    switch (this.outsourceApplyMgt.applicant_screening) {
                        case 4://辞退
                            data = [
                                {name : '辞退日', value : this.outsourceApplyMgt.applicant_screening_refusal_reason_date != null ? this.formatDate(this.outsourceApplyMgt.applicant_screening_refusal_reason_date) : 'ー'},
                                {name : '選考結果', value : this.$enumOutsourceAppScreeningStatuses[this.outsourceApplyMgt.applicant_screening]},
                                {name : '辞退理由', value : this.outsourceApplyMgt.applicant_screening_refusal_reason != null ? this.outsourceApplyMgt.applicant_screening_refusal_reason : 'ー'},
                            ];
                            break;
                        case 5://見送り
                            data = [
                                {name : '選考結果', value : this.$enumOutsourceAppScreeningStatuses[this.outsourceApplyMgt.applicant_screening]},
                                {name : 'お見送り通知日', value : this.outsourceApplyMgt.applicant_screening_send_off_date != null ? this.formatDate(this.outsourceApplyMgt.applicant_screening_send_off_date) : 'ー'},
                            ];
                            break;
                        default:
                            data = [
                                {name : '提案日', value : this.outsourceApplyMgt.proposal_date != null ? this.formatDate(this.outsourceApplyMgt.proposal_date) : 'ー'},
                                {name : '選考結果', value : this.outsourceApplyMgt.applicant_screening != null ? this.$enumOutsourceAppScreeningStatuses[this.outsourceApplyMgt.applicant_screening] : 'ー'},
                                {name : '結果通知日', value : this.outsourceApplyMgt.applicant_screening_date != null ? this.formatDate(this.outsourceApplyMgt.applicant_screening_date) : 'ー'},
                                {name : '次回の選考', value : retSelection != null ? (retSelection['next_phase'] != null ? this.$enumOutsourceSelectionFlows[retSelection['next_phase']] : 'ー') : 'ー'},
                                {name : '評価', value : retSelection != null ? (retSelection['current_evaluation'] != null ? this.$enumOutsourceEvaluations[retSelection['current_evaluation']] : 'ー'): 'ー'},
                            ];
                            break;
                    }

                    results.push({step: 3, data: data});
                }

                // 1次面談 ~ 3次面談、最終面談
                if (lastFlow >= 4) {
                    const atom_keys = ['1st_', '2nd_', '3rd_', 'last_'];
                    for (let i=0; i<atom_keys.length /*&& lastFlow < 4 + i*/; i++) {
                        currrentStep = 4 + i;
                        retSelection = this.getSelectionResult(currrentStep);

                        switch (this.outsourceApplyMgt[atom_keys[i] + 'interview']) {
                            case 4://辞退
                                data = [
                                    {name : '辞退日', value : this.outsourceApplyMgt[atom_keys[i] + 'refusal_reason_date'] != null ? this.formatDate(this.outsourceApplyMgt[atom_keys[i] + 'refusal_reason_date']) : 'ー'},
                                    {name : '選考結果', value : this.$enumOutsourceInterviewStatuses[this.outsourceApplyMgt[atom_keys[i] + 'interview']]},
                                    {name : '辞退理由', value : this.outsourceApplyMgt[atom_keys[i] + 'refusal_reason'] != null ? this.outsourceApplyMgt[atom_keys[i] + 'refusal_reason'] : 'ー'},
                                ];
                                break;
                            case 5://見送り
                                data = [
                                    {name : '選考結果', value : this.$enumOutsourceInterviewStatuses[this.outsourceApplyMgt[atom_keys[i] + 'interview']]},
                                    {name : 'お見送り通知日', value : this.outsourceApplyMgt[atom_keys[i] + 'send_off_date'] != null ? this.formatDate(this.outsourceApplyMgt[atom_keys[i] + 'send_off_date']) : 'ー'},
                                ];
                                break;
                            default:
                                data = [
                                    {name : '面談日程', value : this.outsourceApplyMgt[atom_keys[i] + 'interview_date'] != null ? this.formatDate(this.outsourceApplyMgt[atom_keys[i] + 'interview_date']) : 'ー'},
                                    {name : '選考結果', value : this.outsourceApplyMgt[atom_keys[i] + 'interview'] != null ? this.$enumOutsourceInterviewStatuses[this.outsourceApplyMgt[atom_keys[i] + 'interview']] : 'ー'},
                                    {name : '面談詳細', value : retSelection != null ? ('面談担当者名 : ' + (retSelection['interviewer'] != null ? retSelection['interviewer'] : 'ー') + '\r\n' +
                                            '面談場所住所 : ' + (retSelection['interview_address'] != null ? retSelection['interview_address'] : 'ー') + '\r\n' +
                                            '持ち物 : ' + (retSelection['belongings'] != null ? retSelection['belongings'] : 'ー') + '\r\n' +
                                            '緊急連絡先 : ' + (retSelection['emergency_contact_address'] != null ? retSelection['emergency_contact_address'] : 'ー') + '\r\n' +
                                            'その他特記事項 : ' + (retSelection['else_special_note'] != null ? retSelection['else_special_note'] : 'ー')) : 'ー'},
                                ];
                                break;
                        }
                        results.push({step: 4 + i, data: data});
                    }
                }

                // 契約成立
                if (lastFlow >= 8) {
                    currrentStep = 8;
                    data = [
                        {name : 'オファー日', value : this.outsourceApplyMgt['offer_date'] != null ? this.formatDate(this.outsourceApplyMgt['offer_date']) : 'ー'},
                        {name : '契約成立日', value : this.outsourceApplyMgt['contract_satisfied_date'] != null ? this.formatDate(this.outsourceApplyMgt['contract_satisfied_date']) : 'ー'},
                        {name : '契約条件', value : this.contractTerms.length > 0 ? ('オファー金額 : ' + (this.contractTerms[0].offer_amount != null ? this.formatDigits(this.contractTerms[0].offer_amount) + '円' : 'ー') + '\r\n' +
                                '初日出勤日 : ' + (this.contractTerms[0].first_day_attendance_date != null ? this.formatDate(this.contractTerms[0].first_day_attendance_date) : 'ー') + '\r\n' +
                                '返答期限 : ' + (this.contractTerms[0].reply_deadline != null ? this.formatDate(this.contractTerms[0].reply_deadline) : 'ー')) : 'ー'},
                        {name : '参画開始予定日', value : this.outsourceApplyMgt['joining_scheduled_date'] != null ? this.formatDate(this.outsourceApplyMgt['joining_scheduled_date']) : 'ー'},
                    ];
                    results.push({step: 8, data: data});
                }

                // 参画開始
                if (lastFlow >= 9) {
                    currrentStep = 9;
                    switch (this.outsourceApplyMgt.joining_confirmation) {
                        case 1://参画開始
                            data = [
                                {name : '参画開始日', value : this.outsourceApplyMgt.joining_confirmation_start_date != null ? this.formatDate(this.outsourceApplyMgt.joining_confirmation_start_date) : 'ー'},
                            ];
                            break;
                        case 2://辞退
                            data = [
                                {name : '辞退日', value : this.outsourceApplyMgt.joining_confirmation_refusal_reason_date != null ? this.formatDate(this.outsourceApplyMgt.joining_confirmation_refusal_reason_date) : 'ー'},
                                {name : '選考結果', value : this.$enumOutsourceJoiningStatuses[this.outsourceApplyMgt.joining_confirmation]},
                                {name : '辞退理由', value : this.outsourceApplyMgt.joining_confirmation_refusal_reason != null ? this.outsourceApplyMgt.joining_confirmation_refusal_reason : 'ー'},
                            ];
                            break;
                        case 3://見送り
                            data = [
                                {name : '選考結果', value : this.$enumOutsourceJoiningStatuses[this.outsourceApplyMgt.joining_confirmation]},
                                {name : 'お見送り通知日', value : this.outsourceApplyMgt.joining_confirmation_send_off_date != null ? this.formatDate(this.outsourceApplyMgt.joining_confirmation_send_off_date) : 'ー'},
                            ];
                            break;
                        default:
                            data = [
                            ];
                            break;
                    }
                    results.push({step: 9, data: data});
                }

                // 参画終了
                if (lastFlow >= 10) {
                    currrentStep = 10;
                    switch (this.outsourceApplyMgt.current_state) {
                        case 2://終了
                            data = [
                                {name : '参画終了日', value : this.outsourceApplyMgt.joining_end_date != null ? this.formatDate(this.outsourceApplyMgt.joining_end_date) : 'ー'},
                            ];
                            break;
                        default:
                            data = [
                                {name : '終了予定日', value : this.outsourceApplyMgt.joining_end_date != null ? this.formatDate(this.outsourceApplyMgt.joining_end_date) : 'ー'},
                            ];
                            break;
                    }
                    results.push({step: 10, data: data});
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
                steps: [/*'書類選考', '１次面談', '２次面談', '３次面談', '最終選考', '契約成立', '参画開始', '参画終了'*/],
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
                if (this.selectedStep != index && index <= this.outsourceApplyMgt.last_selection_flow_number) {
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
        },
        mounted() {
            this.mgt_id = this.id;

            const selectionFlowArr = this.selectionFlow.split(",");

            for (let i=2; i<selectionFlowArr.length; i++) {
                if (selectionFlowArr[i] < this.outsourceApplyMgt.last_selection_flow_number && this.getSelectionResult(selectionFlowArr[i]) == null && selectionFlowArr[i] <= 7) {
                    continue;
                }
                this.steps.push(this.$enumOutsourceSelectionFlows[selectionFlowArr[i]]);
                this.flow.push(selectionFlowArr[i]);
            }

            this.selectedStep = this.outsourceApplyMgt.last_selection_flow_number;

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
            outsourceApplyMgt: function(newSelectedType) {
                this.selectedStep = this.outsourceApplyMgt.last_selection_flow_number;
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

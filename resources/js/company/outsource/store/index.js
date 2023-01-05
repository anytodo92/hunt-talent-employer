import { createStore } from "vuex";
import moment from 'moment';
import axios from 'axios';

export default createStore({
    state: {
        count: 0,

        outsourceApplyMgt: null,
        selectionResults: [],
        timelines: []
    },
    getters: {
        message : state => {
            return state.timelines;
        }
    },
    mutations: {
        increment (state) {
            state.count++
        },
        init (state, payload) {
            state.outsourceApplyMgt = payload.mgt;
            state.selectionResults = payload.selectionResults;
            state.timelines = payload.timelines;
            state.interviewSchedules = payload.interviewSchedules;
            state.contractTerms = payload.contractTerms;
        },
        // 見送り
        selection_not_adopted(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('unseated_reason', params.unseated_reason);
            formData.append('unseated_reason_sub', params.unseated_reason_sub);
            formData.append('unseated_cause_detail', params.unseated_cause_detail);

            axios.post('/company/outsource/sendNotAdoptedReason', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.selectionResults = response.data.selectionResults;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 選考通過 次の選考へ
        selection_passed(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('next_phase', params.next_phase);
            formData.append('current_evaluation', params.current_evaluation);
            formData.append('evaluation_point', params.evaluation_point);
            formData.append('concern_point', params.concern_point);

            axios.post('/company/outsource/sendPassSelection', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.selectionResults = response.data.selectionResults;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 契約（オファー）する
        selection_hired(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);

            axios.post('/company/outsource/sendHire', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.selectionResults = response.data.selectionResults;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 面談詳細を入力する
        interview_detail(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('interviewer', params.interviewer);
            formData.append('interview_address', params.interview_address);
            formData.append('belongings', params.belongings);
            formData.append('emergency_contact_address', params.emergency_contact_address);
            formData.append('else_special_note', params.else_special_note);

            axios.post('/company/outsource/sendInterviewDetail', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.selectionResults = response.data.selectionResults;
                    state.interviewSchedules = response.data.interviewSchedules;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 候補日を提示してもらう
        interview_setting_person_type(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('interview_setting_person_type', params.interview_setting_person_type);

            axios.post('/company/outsource/sendInterviewDetail', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.selectionResults = response.data.selectionResults;
                    state.interviewSchedules = response.data.interviewSchedules;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 契約条件を提示する
        send_joining_condition(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('unit_price', params.unit_price);
            formData.append('unit_price_amount', params.unit_price_amount);
            formData.append('pay_off_start', params.pay_off_start);
            formData.append('pay_off_end', params.pay_off_end);
            formData.append('estimated_working_days_week', params.estimated_working_days_week);
            formData.append('special_notes', params.special_notes);
            formData.append('joining_start_date', params.joining_start_date);
            formData.append('reply_deadline', params.reply_deadline);

            const config = {
                headers: { 'content-type': 'multipart/form-data' }
            }

            axios.post('/company/outsource/sendJoiningCondition', formData, config)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.contractTerms = response.data.contractTerms;
                    state.timelines = response.data.timelines;
                }
                else if (response.data.msg.toString() != '') {
                    alert(response.data.msg);
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 参画開始日の変更
        change_start_date(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('joining_scheduled_date', params.joining_scheduled_date);

            axios.post('/company/outsource/sendChangeStartDate', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.contractTerms = response.data.contractTerms;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 参画終了申請
        send_finish_contract(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('joining_end_date', params.joining_end_date);

            axios.post('/company/outsource/sendFinishContract', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 参画終了申請 - 確認・取消する
        agree_finish_contract(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('isAgree', params.isAgree);

            axios.post('/company/outsource/sendAgreeFinish', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        SAVE_TIMELINE_RECORD(state, timeline) {
            state.timelines.unshift(timeline);
        },
        GET_TIMELINE_RECORDS(state, timelines) {
            return state.timelines = timelines;
        },
    },
    actions: {
        saveTimelineRecord({commit}, param) {

            let currentObj = this;

            const config = {
                headers: { 'content-type': 'multipart/form-data' }
            }

            let formData = new FormData();
            formData.append('file', param.fileName);
            formData.append('companyID', param.companyID);
            formData.append('companyUserID', param.companyUserID);
            formData.append('jobSeekerApplyMgtID', param.jobSeekerApplyMgtID);
            formData.append('messageDetail', param.messageDetail);

            axios.post('/company/outsource/saveTimelineRecord', formData, config)
                .then(function (res) {
                    commit('SAVE_TIMELINE_RECORD', res.data)
                })
                .catch(function (error){
                    console.log(error)
                })
        },
        getTimelineRecords({commit}, param) {
            axios.post('/company/outsource/getTimelineRecords', param)
                .then(res => {
                    commit('GET_TIMELINE_RECORDS', res.data)
                }).catch(err=> {
                console.log(err)
            })
        },
    }
})

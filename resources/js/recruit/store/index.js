import { createStore } from "vuex";
import moment from 'moment';
import axios from 'axios';

export default createStore({
    state: {
        count: 0,

        recruitApplyMgt: null,
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
            state.recruitApplyMgt = payload.mgt;
            state.selectionResults = payload.selectionResults;
            state.timelines = payload.timelines;
            state.interviewSchedules = payload.interviewSchedules;
            state.joiningConditions = payload.joiningConditions;
            state.joinConditionAttachments = payload.joinConditionAttachments;
        },
        // 選考辞退
        selection_refusal(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('reason', params.reason);
            formData.append('details', params.details);

            axios.post('/agent/sendRefusalReason', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 確定した日程を送信
        fixed_interview_date(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('interviewDate', params.interviewDate);
            formData.append('interviewTimeFrom', params.interviewTimeFrom);
            formData.append('interviewTimeTo', params.interviewTimeTo);

            axios.post('/agent/sendFixedInterviewDate', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
                    state.interviewSchedules = response.data.interviewSchedules;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 入社条件を交渉する
        send_joining_condition(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('annualIncome', params.annualIncome);
            formData.append('firstDayAttendanceDate', params.firstDayAttendanceDate);
            formData.append('other_desired', params.other_desired);

            axios.post('/agent/sendJoiningCondition', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
                    state.joiningConditions = response.data.joiningConditions;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 入社条件に同意
        allow_joining_condition(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);

            axios.post('/agent/sendAllowJoining', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
                    state.joiningConditions = response.data.joiningConditions;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 初日出勤日の変更
        change_present_date(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('present_date', params.present_date);

            axios.post('/agent/sendChangePresentDate', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
                    state.joiningConditions = response.data.joiningConditions;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 返金に同意・不同意する
        agree_refund(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('refundAmount', params.refundAmount);
            formData.append('isAgree', params.isAgree);

            axios.post('/agent/sendAgreeRefund', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
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

            axios.post('/agent/saveTimelineRecord', formData, config)
                .then(function (res) {
                    commit('SAVE_TIMELINE_RECORD', res.data)
                })
                .catch(function (error){
                    console.log(error)
                })
        },
        getTimelineRecords({commit}, param) {
            axios.post('/agent/getTimelineRecords', param)
                .then(res => {
                    commit('GET_TIMELINE_RECORDS', res.data)
                }).catch(err=> {
                console.log(err)
            })
        },
    }
})

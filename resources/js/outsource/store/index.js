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
        // 選考辞退
        selection_refusal(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('reason', params.reason);
            formData.append('details', params.details);

            axios.post('/ses/sendRefusalReason', formData)
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
        // 確定した日程を送信
        fixed_interview_date(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('interviewDate', params.interviewDate);
            formData.append('interviewTimeFrom', params.interviewTimeFrom);
            formData.append('interviewTimeTo', params.interviewTimeTo);

            axios.post('/ses/sendFixedInterviewDate', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.outsourceApplyMgt = response.data.outsourceApplyMgt;
                    state.interviewSchedules = response.data.interviewSchedules;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 契約条件に同意
        allow_joining_condition(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);

            axios.post('/ses/sendAllowJoining', formData)
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
        // 参画開始日の変更
        change_start_date(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.outsourceApplyMgt.id);
            formData.append('joining_scheduled_date', params.joining_scheduled_date);

            axios.post('/ses/sendChangeStartDate', formData)
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

            axios.post('/ses/sendFinishContract', formData)
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

            axios.post('/ses/sendAgreeFinish', formData)
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

            axios.post('/ses/saveTimelineRecord', formData, config)
                .then(function (res) {
                    commit('SAVE_TIMELINE_RECORD', res.data)
                })
                .catch(function (error){
                    console.log(error)
                })
        },
        getTimelineRecords({commit}, param) {
            axios.post('/ses/getTimelineRecords', param)
                .then(res => {
                    commit('GET_TIMELINE_RECORDS', res.data)
                }).catch(err=> {
                console.log(err)
            })
        },
    }
})

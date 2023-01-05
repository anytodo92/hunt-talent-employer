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
        // 不採用
        selection_not_adopted(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('unseated_reason', params.unseated_reason);
            formData.append('unseated_reason_sub', params.unseated_reason_sub);
            formData.append('unseated_cause_detail', params.unseated_cause_detail);

            axios.post('/company/recruit/sendNotAdoptedReason', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
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
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('next_phase', params.next_phase);
            formData.append('current_evaluation', params.current_evaluation);
            formData.append('evaluation_point', params.evaluation_point);
            formData.append('concern_point', params.concern_point);

            axios.post('/company/recruit/sendPassSelection', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
                    state.selectionResults = response.data.selectionResults;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 内定する
        selection_hired(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);

            axios.post('/company/recruit/sendHire', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
                    state.selectionResults = response.data.selectionResults;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 面接詳細を入力する
        interview_detail(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('interviewer', params.interviewer);
            formData.append('interview_address', params.interview_address);
            formData.append('belongings', params.belongings);
            formData.append('emergency_contact_address', params.emergency_contact_address);
            formData.append('else_special_note', params.else_special_note);

            axios.post('/company/recruit/sendInterviewDetail', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
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
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('interview_setting_person_type', params.interview_setting_person_type);

            axios.post('/company/recruit/sendInterviewDetail', formData)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
                    state.selectionResults = response.data.selectionResults;
                    state.interviewSchedules = response.data.interviewSchedules;
                    state.timelines = response.data.timelines;
                }
            })
            .catch(function (error) {
                console.warn('ajax_error: ', error);
            });
        },
        // 入社条件を提示する
        send_joining_condition(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('offerAmount', params.offerAmount);
            formData.append('firstDayAttendanceDate', params.firstDayAttendanceDate);
            formData.append('replyDeadline', params.replyDeadline);
            // formData.append('file', params.files);

            for( var i = 0; i < params.files.length; i++ ){
                let file = params.files[i];
                formData.append('files[' + i + ']', file);
            }

            const config = {
                headers: { 'content-type': 'multipart/form-data' }
            }

            axios.post('/company/recruit/sendJoiningCondition', formData, config)
            .then(function (response) {
                if (response.data.success == true) {
                    state.recruitApplyMgt = response.data.recruitApplyMgt;
                    state.joiningConditions = response.data.joiningConditions;
                    state.joinConditionAttachments = response.data.joinConditionAttachments;
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
        // 同意する（採用決定）
        allow_joining_condition(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);

            axios.post('/company/recruit/sendAllowJoining', formData)
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
        // 出勤した・出勤しなかった
        check_presented(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('isPresent', params.isPresent);

            axios.post('/company/recruit/sendPresented', formData)
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
        // 入社日変更
        change_present_date(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('present_date', params.present_date);

            axios.post('/company/recruit/sendChangePresentDate', formData)
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
        // 返金申請
        send_retirement_date(state, params) {
            // ajax post
            let formData = new FormData();
            formData.append('apply_mgt_id', state.recruitApplyMgt.id);
            formData.append('retirement_date', params.retirement_date);

            axios.post('/company/recruit/sendRetirementDate', formData)
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

            axios.post('/company/recruit/saveTimelineRecord', formData, config)
                .then(function (res) {
                    commit('SAVE_TIMELINE_RECORD', res.data)
                })
                .catch(function (error){
                    console.log(error)
                })
        },
        getTimelineRecords({commit}, param) {
            axios.post('/company/recruit/getTimelineRecords', param)
                .then(res => {
                    commit('GET_TIMELINE_RECORDS', res.data)
                }).catch(err=> {
                console.log(err)
            })
        },
    }
})

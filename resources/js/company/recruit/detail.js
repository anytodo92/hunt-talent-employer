require('../../bootstrap')
import store from './store'
import VueAxios from 'vue-axios';
import axios from 'axios';
import NextOperation from './components/NextOperation.vue'
import BreadCrumb from './components/BreadCrumb.vue'
import TimeLine from './components/TimeLine.vue'
import moment from 'moment-timezone'
moment.tz.setDefault('Asia/Tokyo')

window.Vue = require('vue');
window.$ = window.jQuery = require('jquery')

const app = Vue.createApp({
    data() {
        return {
            recruitApplyMgt: g_recruitApplyMgt,
            selectionResults: g_selectionResults,
            seeker: g_jobSeeker,
            interviewSchedules: g_interviewSchedules,
            timelines: g_timelines,
            joiningConditionPresents : g_joiningConditionPresents,
            recruitCompany : g_recruitCompany,
            offerCompanyUser : g_offerCompanyUser,
        }
    },
    created() {
    },
    methods: {
    },
    computed: {
        fullName: {
          // getter
          get() {
            return this.firstName + ' ' + this.lastName;
          },
          // setter
          set(newValue) {
            const names = newValue.split(' ');
            this.firstName = names[0];
            this.lastName = names[names.length - 1];
          }
        }
    }
})

// Components
app.component('next-operation', NextOperation) // global registration - can be used anywhere
app.component('breadcrumb', BreadCrumb)
app.component('timeline', TimeLine)

// Store & Axios
store.commit('init', {
    mgt: g_recruitApplyMgt,
    selectionResults: g_selectionResults,
    timelines: g_timelines,
    interviewSchedules: g_interviewSchedules,
    joiningConditions: g_joiningConditionPresents,
    joinConditionAttachments: g_joinConditionAttachments
});
app.use(store);
app.use(VueAxios, axios);

// Common variables
app.config.globalProperties.$jobSeeker = g_jobSeeker;
app.config.globalProperties.$recruitCompany = g_recruitCompany;
app.config.globalProperties.$recruitingCompany = g_recruitingCompany;
app.config.globalProperties.$offerInfo = g_offerInfo;
app.config.globalProperties.$offerCompanyUser = g_offerCompanyUser;

app.config.globalProperties.$enumPrefectures = enumPrefectures;
app.config.globalProperties.$enumRecruitDocConfirmStatuses = enumRecruitDocConfirmStatuses;
app.config.globalProperties.$enumRecruitAppScreeningStatuses = enumRecruitAppScreeningStatuses;
app.config.globalProperties.$enumRecruitInterviewStatuses = enumRecruitInterviewStatuses;
app.config.globalProperties.$enumRecruitRecruitmentStatuses = enumRecruitRecruitmentStatuses;
app.config.globalProperties.$enumRecruitJoiningStatuses = enumRecruitJoiningStatuses;
app.config.globalProperties.$enumRecruitPhases = enumRecruitPhases;
app.config.globalProperties.$enumRecruitEvaluations = enumRecruitEvaluations;
app.config.globalProperties.$enumRecruitSelectionFlows = enumRecruitSelectionFlows;
app.config.globalProperties.$enumRecruitApplyMgtSelectionResultKeys = enumRecruitApplyMgtSelectionResultKeys;
app.config.globalProperties.$enumRecruitUnseatedReasons = enumRecruitUnseatedReasons;
app.config.globalProperties.$enumRecruitUnseatedReasonSubs = enumRecruitUnseatedReasonSubs;

// Mount to element
var vm = app.mount("#app");

window.vm = vm;
//vm.fullName = 'John Doe';
//console.log(vm.$refs);

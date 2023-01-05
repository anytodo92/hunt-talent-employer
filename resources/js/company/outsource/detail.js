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
            outsourceApplyMgt: g_outsourceApplyMgt,
            selectionResults: g_selectionResults,
            seeker: g_jobSeeker,
            interviewSchedules: g_interviewSchedules,
            timelines: g_timelines,
            contractTerms : g_contractTerms,
            outsourceCompany : g_outsourceCompany,
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
    mgt: g_outsourceApplyMgt,
    selectionResults: g_selectionResults,
    timelines: g_timelines,
    interviewSchedules: g_interviewSchedules,
    contractTerms: g_contractTerms
});
app.use(store);
app.use(VueAxios, axios);

// Common variables
app.config.globalProperties.$jobSeeker = g_jobSeeker;
app.config.globalProperties.$outsourceCompany = g_outsourceCompany;
app.config.globalProperties.$recruitingCompany = g_recruitingCompany;
app.config.globalProperties.$offerInfo = g_offerInfo;
app.config.globalProperties.$offerCompanyUser = g_offerCompanyUser;

app.config.globalProperties.$enumPrefectures = enumPrefectures;
app.config.globalProperties.$enumUnitPrices = enumUnitPrices;
app.config.globalProperties.$enumOutsourceDocConfirmStatuses = enumOutsourceDocConfirmStatuses;
app.config.globalProperties.$enumOutsourceAppScreeningStatuses = enumOutsourceAppScreeningStatuses;
app.config.globalProperties.$enumOutsourceInterviewStatuses = enumOutsourceInterviewStatuses;
app.config.globalProperties.$enumOutsourceContractStatuses = enumOutsourceContractStatuses;
app.config.globalProperties.$enumOutsourceJoiningStatuses = enumOutsourceJoiningStatuses;
app.config.globalProperties.$enumOutsourcePhases = enumOutsourcePhases;
app.config.globalProperties.$enumOutsourceEvaluations = enumOutsourceEvaluations;
app.config.globalProperties.$enumOutsourceSelectionFlows = enumOutsourceSelectionFlows;
app.config.globalProperties.$enumOutsourceApplyMgtSelectionResultKeys = enumOutsourceApplyMgtSelectionResultKeys;
app.config.globalProperties.$enumOutsourceUnseatedReasons = enumOutsourceUnseatedReasons;
app.config.globalProperties.$enumOutsourceUnseatedReasonSubs = enumOutsourceUnseatedReasonSubs;

// Mount to element
var vm = app.mount("#app");

window.vm = vm;
//vm.fullName = 'John Doe';
//console.log(vm.$refs);

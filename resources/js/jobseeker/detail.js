require('../bootstrap')
import store from './store'
import VueAxios from 'vue-axios';
import axios from 'axios';
import InterviewSchedule from './components/InterviewSchedule.vue'
import JobList from './components/JobList.vue'
import moment from 'moment-timezone'
moment.tz.setDefault('Asia/Tokyo')

window.Vue = require('vue');

let tmpSeek = 0;

const app = Vue.createApp({
    data() {
        return {
        }
    },
    created() {
    },
    methods: {
    },
    computed: {
    }
})

// Components
app.component('interview-schedule', InterviewSchedule) // global registration - can be used anywhere
app.component('job-list', JobList)

// Store & Axios
store.commit('init', {
});
app.use(store);
app.use(VueAxios, axios);

// Common variables

// Mount to element
var vm = app.mount("#app");

window.vm = vm;

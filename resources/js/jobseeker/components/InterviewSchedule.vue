<template>
    <div class="text-line3" v-for="schedule in schedules" :key="schedule">
        <p class="line-head" v-if="schedule.status == 2">[実施済み]</p>
        <p class="line-head" v-else style="color: red">[面談予定]</p>
        <span v-if="schedule.status == 2">{{ formatDate(schedule.created_at) }}</span>
        <span v-else style="color: red">{{ formatDate(schedule.created_at) }}</span>
        <p class="line-content" v-if="schedule.status == 2">面談者：{{ schedule.name }}</p>
        <p class="line-content" v-else style="color: red">面談者：{{ schedule.name }}</p>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        data() {
            return {
                seekerID: '',
                schedules: [],
            }
        },
        created() {
            moment.locale('ja');
            g_interID = this;
        },
        watch: {
            seekerID : {
                handler(val, oldVal) {
                    this.getSchedule(val)
                },
                immediate: true,
            }
        },
        methods: {
            setInterID(newValue) {
                this.seekerID = newValue;
            },
            getSchedule(val) {
                axios.get('/agent/jobseeker/' + val)
                .then(res => {
                    if (res.data.success == true) {
                        this.schedules = res.data.interviewSchedules;
                    }
                })
            },
            formatDate(date) {
                // 2021.1.1 00:00
                return moment(new Date(date)).format("YYYY.M.D H:M");
            },
        },
    }
</script>

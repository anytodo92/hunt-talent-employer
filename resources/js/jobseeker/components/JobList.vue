<template>
    <div class="row mt-2 mb-3">
        <label class="form-check-label ml-3" style="font-weight: bold;">応募済み（最新 {{ appliedJobLists.length }} 件）応募総数：{{ totalCount }}件</label>
    </div>
    <div v-for="appliedJobList in appliedJobLists" class="row mt-2 mb-2">
        <div class="col-md-9">
            <label style="font-weight: bold;">株式会社〇〇〇</label>
            <span v-if="appliedJobList.selection_status == 1" class="badge-primary ml-3">選考中</span>
            <span v-else-if="appliedJobList.selection_status == 2" class="badge-info ml-3">落選/辞退</span>
            <span v-else-if="appliedJobList.selection_status == 3" class="badge-success ml-3">内定</span>
            <span v-else-if="appliedJobList.selection_status == 4" class="badge-danger ml-3">入社確定</span>
            <span v-else class="badge-secondary ml-3">不採用</span>
            <span class="carousel mr-0">{{ formatDate(appliedJobList.updated_at) }}</span>
            <p>{{ appliedJobList.job_title }}</p>
        </div>
        <div class="col-md-3 right-arrow">
            <span class="arrow"><i class="ti-angle-right"></i></span>
        </div>
    </div>

    <div class="row mt-2 mb-3">
        <label class="form-check-label ml-3" style="font-weight: bold;">未応募（求人票送付／最新 {{ notappliedJobLists.length }} 件）未応募総数：{{ totalCount }}件</label>
    </div>
    <div v-for="notappliedJobList in notappliedJobLists" class="row mt-2 mb-2">
        <div class="col-md-9">
            <label style="font-weight: bold;">株式会社〇〇〇</label>
            <p>{{ notappliedJobList.job_title }}</p>
        </div>
        <div class="col-md-3 right-arrow">
            <span class="arrow"><i class="ti-angle-right"></i></span>
        </div>
    </div>
</template>

<script>
    import moment from 'moment'
    import {mapState} from "vuex";
    export default {
        data() {
            return {
                seekerID: '',
                appliedJobLists:[],
                notappliedJobLists:[],
                totalCount: '',
            }
        },
        created() {
            moment.locale('ja');
            g_jobID = this;
        },
        watch: {
            seekerID : {
                handler(val, oldVal) {
                    this.getJobLists(val);
                },
                immediate: true,
            }
        },
        methods: {
            setJobID(newValue) {
                this.seekerID = newValue;
            },
            getJobLists(val) {
                axios.get('/agent/jobseeker/' + val)
                    .then(res => {
                        if (res.data.success == true) {
                            this.appliedJobLists = res.data.appliedJobRecords;
                            this.notappliedJobLists = res.data.notappliedJobRecords;
                            this.totalCount = res.data.totalJob;
                        }
                    })
            },
            formatDate(date) {
                // 2021.1.1
                return moment(new Date(date)).format("YYYY.M.D");
            },
        },
    }
</script>

<template>
    <div class="card mb-2">
        <div class="card-title">
            <span class="icon-holder mR-10 vA-middle"><i class="c-grey-icon ti-comments"></i> </span>
            <span class="title">人材紹介タイムライン</span>
        </div>
        <div class="new-msg-file">
            <form id="upload" enctype="multipart/form-data">
                <textarea class="box-shadow col-md-12 p-10" rows="1" id="inputChat" placeholder="メッセージを入力"></textarea>
                <div class="mB-5">
                    <label for="inputFileUpload" class="mB-0">＋ファイルの添付</label>
                    &nbsp&nbsp
                    <span id="uploadFileName"></span>
                    <input id="inputFileUpload" type="file" v-on:change="onFileChange" style="width:1px; height:1px; visibility: hidden; z-index: 2">
                    <button type="button" class="btn cur-p btn-send" id="btnChatSend" @click="saveTimelineRecord">送信</button>
                </div>
                <p class="text red">※添付ファイルは60日で自動削除されます。</p>
            </form>
        </div>
        <div class="card-body card-body--chat">
            <div v-for="msg in message" :key="msg"
                :class="['chat',
                    { 'chat--notification' : msg.message_type == 1 },
                    { 'chat--left' : msg.message_type!=1 && msg.sender_type != 2 },
                    { 'chat--right' : msg.message_type!=1 && msg.sender_type == 2 },
                ]">
                <div v-if="msg.message_type == 1" class="notification-title">
                    <figure class="figure">
                        <img v-if="msg.sender_type == 1" class="img" :src=makeLogoURL(msg) onerror="onImgErr(this, 1)" alt="">
                        <img v-else-if="msg.sender_type == 2" class="img" :src=makeLogoURL(msg) onerror="onImgErr(this, 2)" alt="">
                        <img v-else-if="msg.sender_type == 3" class="img" :src=makeLogoURL(msg) onerror="onImgErr(this, 3)" alt="">
                        <img v-else-if="msg.sender_type == 4" class="img" :src=makeLogoURL(msg) onerror="onImgErr(this, 4)" alt="">
                        <img v-else class="img" :src=makeLogoURL(msg) onerror="onImgErr(this)" alt="">
                    </figure>
                    <span v-html="msg.message_title" class="title"></span>
                </div>
                <figure v-else class="figure">
                        <img v-if="msg.sender_type == 1" class="img" :src=makeLogoURL(msg) onerror="onImgErr(this, 1)" alt="">
                        <img v-else-if="msg.sender_type == 2" class="img" :src=makeLogoURL(msg) onerror="onImgErr(this, 2)" alt="">
                        <img v-else-if="msg.sender_type == 3" class="img" :src=makeLogoURL(msg) onerror="onImgErr(this, 3)" alt="">
                        <img v-else-if="msg.sender_type == 4" class="img" :src=makeLogoURL(msg) onerror="onImgErr(this, 4)" alt="">
                        <img v-else class="img" :src=makeLogoURL(msg) onerror="onImgErr(this)" alt="">
                </figure>

                <div v-if="msg.message_type == 1 && msg.message_detail !=null && msg.message_detail.length > 0" class="text-group">
                    <p v-html="msg.message_detail" class="text"></p>
                </div>
                <p v-else v-html="msg.message_detail" class="text"></p>
                <p v-if="msg.attachment != null && msg.attachment.length > 0" lass="text">
                    <div v-for="(attach, index) in msg.attachment.split('|')" :key="'file'+index">
                        <div v-if="attach.trim() != ''">
                            {{ msg.attachment_name.split('|')[index] }}
                            <a class="mL-20 link fRight" :href=makeUploadFileURL(attach,msg.message_type) target="_blank" :data-title="msg.attachment_name.split('|')[index]"><i class="link-icon ti-new-window"></i></a>
                            <a class="link fRight" :href=makeUploadFileURL(attach,msg.message_type) :download="msg.attachment_name.split('|')[index]"><i class="link-icon ti-download"></i></a>
                        </div>
                    </div>
                </p>
                <p class="text timestamp">{{ formatDate(msg.created_at) }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import {mapGetters, mapState} from 'vuex'
    import moment from 'moment';
    export default {
        props: {
        },
        data() {
            return {
                attached_file: undefined
            }
        },
        created() {
            moment.locale('ja');
        },
        watch: {

        },
        methods: {
            saveTimelineRecord() {
                let inputChatString = $("#inputChat").val().trim();
                let fileName = $("#inputFileUpload").val();
                let fileData = this.attached_file;
                if (!inputChatString.length && !fileName.length) {
                    alert("メッセージを入力してください。");
                    return;
                }
                $("#inputChat").val('');
                $("#inputFileUpload").val('');
                $("#uploadFileName").text('');
                this.attached_file = undefined;
                let postParam = {
                    jobSeekerApplyMgtID : g_recruitApplyMgt.id,
                    messageDetail : inputChatString,
                    fileName : fileData
                };

                this.$store.dispatch('saveTimelineRecord', postParam);
            },
            onFileChange(e){
                this.attached_file = e.target.files[0];
                $("#uploadFileName").text('');
                if (this.attached_file != undefined) {
                    $("#uploadFileName").text(this.attached_file.name);
                }
            },
            formatDate(date) {
                // 2021-1-1 00:00
                return moment(new Date(date)).format("YYYY/M/D HH:mm");
            },
            makeLogoURL(content) {
                let logoUrl = '';
                if (content.message_type == 1 && (content.sender_type == 2 || content.sender_type == 3) && content.message_title.endsWith('さんが選考を辞退しました。')) {
                    logoUrl = this.makeLogoURLBySex(content.sex);
                } else {
                    switch (content.sender_type) {
                        case 1://求人企業担当者
                            logoUrl = '/storage/company/' + content.company_id + '/logo/' + content.company_logo;
                            break;
                        case 2://人材紹介会社担当者
                            logoUrl = '/storage/recruit/' + content.recruit_company_id + '/company_user/' + content.recruit_user_id + '/logo/' + content.recruit_user_logo;
                            break;
                        case 3://業務委託会社担当者
                            logoUrl = '/storage/outsource/' + content.outsource_company_id + '/company_user/' + content.outsource_user_id + '/logo/' + content.outsource_user_logo;
                            break;
                        case 4://運営企業担当者
                            //logoUrl = '/storage/admin/' + content.admin_user_id + '/logo/' + content.admin_user_logo;
                            logoUrl = '/storage/admin/1/logo/admin.png';
                            break;
                    }
                }

                return logoUrl;
            },
            makeLogoURLBySex(sexType) {
                let logoUrl = '';
                switch (sexType) {
                    case 1: //男生
                        logoUrl = '/assets/static/images/avatar/avatar1.png';
                        break;
                    case 2: //女性
                        logoUrl = '/assets/static/images/avatar/avatar2.png';
                        break;
                    case 3: //不問
                        logoUrl = '/assets/static/images/avatar/avatar3.png';
                        break;
                }
                return logoUrl;
            },
            makeUploadFileURL(filename, message_type) {
                if (message_type == 1) { // 1:自動送信メッセージ
                    return '/storage/recruit/attachment/' + filename;
                }
                return '/storage/timeline/' + filename;
            }
        },
        computed: {
          ...mapGetters([
              'message'
          ]),
        },
        mounted() {
            let postParam = {id : g_recruitApplyMgt.id};
            this.$store.dispatch('getTimelineRecords', postParam);
        },
        updated() {

        }
    }
</script>

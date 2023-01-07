@extends('layouts.app')

@section('title', '求人企業向けTOP')

@section('header')

    <ul class="nav-left">
        <div class="logo">
            <a href="/">
                <img src="/assets/static/images/logo.png" alt="{{ config('app.name') }}">
            </a>
        </div>
    </ul>
    <ul class="nav-right">
        <li>
            <a href="{{ route('login') }}">求人企業向けログイン</a>
        </li>
    </ul>

@endsection

@section('content')

    <div id="mainContent">
        <div class="full-container">
            <div class="row">
                <div class="col-md-8 col-lg-8 col-xl-8 d-none d-md-block d-lg-block d-xl-block">&nbsp;</div>
                <div class="col-md-4 col-lg-4 col-xl-4 col-12">
                    <div class="right-box">
                        <div class="tabs">
                            <div :class="getTabCss('contact')" @click="changeStatus('contact')">お問い合わせ・資料請求</div>
                            <div :class="getTabCss('request')" @click="changeStatus('request')">求人掲載のお申込み</div>
                        </div>

                        <!-- お問い合わせ・資料請求 -->
                        <div v-if="isStatusContact">
                            <div style="padding:20px;">
                                <div class="form-check" v-for="(name,id) in companyContactTypes">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" :value="id" v-model="params.contact_types"> @{{ name }}
                                    </label>
                                </div>
                            </div>
                            <v-error class="mx-2" v-model="errors.contact_types"></v-error>
                            <div class="form-group horizontal">
                                <label class="col-form-label">
                                    <span>会社名<span class="text-danger">*</span></span>
                                    <input type="text" class="form-control" v-model="params.company_name">
                                </label>
                                <v-error class="mx-2" v-model="errors.company_name"></v-error>
                                <label class="col-form-label">
                                    <span>お名前<span class="text-danger">*</span></span>
                                    <input type="text" class="form-control" v-model="params.user_name">
                                </label>
                                <v-error class="mx-2" v-model="errors.user_name"></v-error>
                                <label class="col-form-label">
                                    <span>メールアドレス<span class="text-danger">*</span></span>
                                    <input type="email" class="form-control" v-model="params.email">
                                </label>
                                <v-error class="mx-2" v-model="errors.email"></v-error>
                                <label class="col-form-label">
                                    <span>電話番号<span class="text-danger">*</span></span>
                                    <input type="tel" class="form-control" v-model="params.phone_number">
                                </label>
                                <v-error class="mx-2" v-model="errors.phone_number"></v-error>
                                <label class="col-form-label">
                                    <span>会社HP<span class="text-danger">*</span></span>
                                    <input type="url" class="form-control" v-model="params.url">
                                </label>
                                <v-error class="mx-2" v-model="errors.url"></v-error>
                                <label class="col-form-label">
                                    <span>年間採用人数<span class="text-danger">*</span></span>
                                    <select class="form-control" v-model="params.annual_recruit_number_id">
                                        <option value="">--選択してください--</option>
                                        <option v-for="(label,id) in annualRecruitNumbers" :value="id" v-text="label"></option>
                                    </select>
                                </label>
                                <v-error class="mx-2" v-model="errors.annual_recruit_number_id"></v-error>
                                <label class="col-form-label">
                                    <span>ご検討状況<span class="text-danger">*</span></span>
                                    <select class="form-control" v-model="params.consideration_status_id">
                                        <option value="">--選択してください--</option>
                                        <option v-for="(label,id) in considerationStatuses" :value="id" v-text="label"></option>
                                    </select>
                                </label>
                                <v-error class="mx-2" v-model="errors.consideration_status_id"></v-error>
                                <div class="p-4">
                                    <p>お問い合わせ内容</p>
                                    <textarea rows="5" class="form-control" v-model="params.inquiry"></textarea>
                                </div>
                                <div style="padding:20px;">
                                    <div class="">
                                        <label>
                                            <input style="display:inline-block;width:20px;margin-top:5px;" type="checkbox" v-model="params.accepted">
                                            <a href="/privacy_policy" target="_blank">プライバシーポリシー・利用規約</a>
                                            に同意する
                                            <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>
                                <v-error class="mx-2" v-model="errors.accepted"></v-error>
                                <div class="mx-1">
                                    <button type="submit" class="w-100 btn btn-primary" @click="onSubmit">送信</button>
                                </div>
                            </div>
                        </div>

                        <!-- 求人掲載のお申込み -->
                        <div v-if="isStatusRequest">
                            <div style="padding:20px;">
                                <p class="text-center">完全成功報酬の求人一括募集</p>
                            </div>
                            <div class="form-group horizontal">
                                <label class="col-form-label">
                                    <span>会社名<span class="text-danger">*</span></span>
                                    <input type="text" class="form-control" v-model="params.company_name">
                                </label>
                                <v-error class="mx-2" v-model="errors.company_name"></v-error>
                                <label class="col-form-label">
                                    <span>お名前<span class="text-danger">*</span></span>
                                    <input type="text" class="form-control" v-model="params.user_name">
                                </label>
                                <v-error class="mx-2" v-model="errors.user_name"></v-error>
                                <label class="col-form-label">
                                    <span>メールアドレス<span class="text-danger">*</span></span>
                                    <input type="email" class="form-control" v-model="params.email">
                                </label>
                                <v-error class="mx-2" v-model="errors.email"></v-error>
                                <label class="col-form-label">
                                    <span>電話番号<span class="text-danger">*</span></span>
                                    <input type="tel" class="form-control" v-model="params.phone_number">
                                </label>
                                <v-error class="mx-2" v-model="errors.phone_number"></v-error>
                                <label class="col-form-label">
                                    <span>本社住所<span class="text-danger">*</span></span>
                                    <select class="form-control pref" v-model="params.prefecture_id">
                                        <option value="">--都道府県▼--</option>
                                        <option v-for="(name,id) in prefectures" :value="id" v-text="name"></option>
                                    </select>
                                </label>
                                <v-error class="mx-2" v-model="errors.prefecture_id"></v-error>
                                <label class="col-form-label">
                                    <span>&nbsp;</span>
                                    <input type="text" class="form-control" v-model="params.address">
                                </label>
                                <v-error class="mx-2" v-model="errors.address"></v-error>
                                <label class="col-form-label">
                                    <span>会社HP<span class="text-danger">*</span></span>
                                    <input type="url" class="form-control" v-model="params.url">
                                </label>
                                <v-error class="mx-2" v-model="errors.url"></v-error>
                                <label class="col-form-label">
                                    <span>年間採用人数<span class="text-danger">*</span></span>
                                    <select class="form-control" v-model="params.annual_recruit_number_id">
                                        <option value="">--選択してください--</option>
                                        <option v-for="(label,id) in annualRecruitNumbers" :value="id" v-text="label"></option>
                                    </select>
                                </label>
                                <v-error class="mx-2" v-model="errors.annual_recruit_number_id"></v-error>
                                <div style="padding:20px;">
                                    <div class="">
                                        <label>
                                            <input style="display:inline-block;width:20px;margin-top:5px;" type="checkbox" v-model="params.accepted">
                                            <a href="/privacy_policy" target="_blank">プライバシーポリシー・利用規約</a>
                                            に同意する
                                            <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>
                                <v-error class="mx-2" v-model="errors.accepted"></v-error>
                                <div class="form-group horizontal">
                                    <div class="p-4">
                                        <p>お問い合わせ内容</p>
                                        <textarea rows="5" class="form-control" v-model="params.inquiry"></textarea>
                                    </div>
                                    <div class="mx-1">
                                        <button type="submit" class="w-100 btn btn-primary" @click="onSubmit">送信</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('css')

    <style>

        .right-box {
            width: calc(100% - 20px);
            margin: 10px;
            border: solid 1px gray;
        }

        .tabs {
            display: flex;
            flex-wrap: nowrap;
            overflow: hidden;
        }

        .tabs .tab {
            width: calc(50%);
            text-align: center;
            cursor: pointer;
            background-color: orange;
            overflow: hidden;
            color: white;
        }

        .tabs .tab:last-child {
            width: 50%;
            border-right: none;
        }

        .tab-active {
            background-color: white !important;
            overflow: hidden;
            color: #444 !important;
        }

        .right-box .horizontal label {
            width: 100%;
            display: inline-flex;
        }

        .right-box .horizontal label>span {
            width: 120px;
            padding: 8px;
        }

        .right-box .horizontal input,
        .right-box .horizontal select {
            width: calc(100% - 128px);
        }

    </style>

@endsection

@section('js')

    <script src="/js/components/v-error.js"></script>
    <script>

        createApp({
            data() {
                return {
                    status: 'contact', // `contact` or `request`
                    params: {},
                    errors: {},
                    prefectures: @json($prefectures),
                    annualRecruitNumbers: @json($annual_recruit_numbers),
                    considerationStatuses: @json($consideration_statuses),
                    companyContactTypes: @json($company_contact_types),
                }
            },
            methods: {
                changeStatus(status) {

                    this.status = status;

                },
                getTabCss(targetStatus) {

                    let classes = ['tab', 'py-2'];

                    if(this.status === targetStatus) {

                        classes.push('tab-active');

                    }

                    return classes;

                },
                clearParams() {

                    this.params = {
                        company_name: '',
                        user_name: '',
                        email: '',
                        phone_number: '',
                        prefecture_id: '',
                        address: '',
                        url: 'https://',
                        annual_recruit_number_id: '',
                        consideration_status_id: '',
                        inquiry: '',
                        accepted: false,
                        contact_types: []
                    }

                },
                onSubmit() {

                    if(confirm('入力内容を送信します。よろしいでしょうか。')) {

                        this.errors = {};
                        const url = '{{ route('company.register.store') }}';
                        const params = Object.assign(this.params, { status: this.status })
                        axios.post(url, params)
                            .then(response => {

                                if(response.data.result === true) {

                                    this.clearParams();
                                    location.href = '{{ route('company.register.complete') }}/'+ this.status;

                                } else {

                                    alert('予期しないエラーが発生しました。何度も表示されるようでしたら管理者へお問い合わせください。');

                                }

                            })
                            .catch(error => {

                                const errors = error.response.data.errors;
                                let errorMessages = {};

                                for(let key in errors) {

                                    errorMessages[key] = errors[key][0];

                                }

                                this.errors = errorMessages;

                                nextTick(() => {

                                    const errorElements = document.querySelectorAll('.v-error');
                                    const top = errorElements[0].offsetTop - 80;
                                    scrollTo({
                                        top: top,
                                        behavior: 'smooth'
                                    });

                                });

                            });

                    }

                }
            },
            computed: {
                isStatusContact() {

                    return (this.status === 'contact');

                },
                isStatusRequest() {

                    return (this.status === 'request');

                }
            },
            watch: {
                status() {

                    this.errors = {};

                }
            },
            mounted() {

                this.clearParams();

            }
        })
        .component('v-error', errorComponent)
        .mount('#app');

    </script>

@endsection
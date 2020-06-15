<template>
    <div>
        <heading class="mb-6">Monthly Report</heading>

        <div class="card">
            <form enctype="multipart/form-data">
                <div class="flex border-b border-40">
                    <div class="w-1/5 py-6 px-8">
                        <label for="Business Username" class="inline-block text-80 pt-2 leading-tight">
                            Business Username
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <input id="username" dusk="long_url" type="text" placeholder="Business Username"
                               class="w-full form-control form-input form-input-bordered" v-model="username">
                        <div class="help-text help-text mt-2"></div>
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/5 py-6 px-8">
                        <label for="Business Report" class="inline-block text-80 pt-2 leading-tight">
                            Business Report
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <input id="report" dusk="long_url" type="file"  ref="report"  placeholder="Business Report"
                               class="w-full form-control form-input form-input-bordered"  @change="setInfluencerReport($event)">
                        <div class="help-text help-text mt-2"></div>
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/5 py-6 px-8">
                        <label for="Month" class="inline-block text-80 pt-2 leading-tight">
                            Month
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <select id="month" dusk="title"  placeholder="Month"
                                class="w-full form-control form-input form-input-bordered" v-model="month">
                            <option v-for="m in 12"  >{{m}}</option>
                        </select>
                        <div class="help-text help-text mt-2"></div>
                    </div>
                </div>

                <div class="flex items-center">
                <button class="btn btn-default btn-primary inline-flex items-center relative"
                        @click="saveInfluencerReport($event)" dusk="create-button" :disabled="disabled">
                    <span class=""> Save Report</span>
                </button>
            </div>
            </form>

        </div>

    </div>
</template>

<script>
import axios from "axios";
import Vue from "vue";
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

Vue.use(VueSweetalert2);
export default {
    data() {
        return {
            username: '',
            month: new Date().getMonth(),
            report:'',
            disabled:false,
        }
    },
    mounted() {

    },
    methods:{
        resetForm(){
            this.report= '';
            this.month= new Date().getMonth();
            this.username= '';
        },
        setInfluencerReport($event){
            this.report= this.$refs.report.files[0];
        },
        postData: async function (url = '', data = {}) {
            const headers = {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept':'application/json'
            };
            return axios.post(url, data, {
                headers: headers
            })
        },
        saveInfluencerReport: function ($event) {

            $event.preventDefault();
            this.disabled= true;
            let self= this;
            let csrf= document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            let myHeaders = new Headers();
            myHeaders.append("Accept", "application/json");
            myHeaders.append('X-CSRF-Token', csrf )


            let formdata = new FormData();
            formdata.append("report", this.report);
            formdata.append("username", this.username);
            formdata.append("month", this.month);

            let requestOptions = {
                method: 'POST',
                headers: myHeaders,
                body: formdata,
                redirect: 'follow'
            };

            fetch("/nova-vendor/monthly-report/save-influencer-report", requestOptions)
                .then(result => {

                    if (result.status !=200) {
                        result.json().then(response => {
                            let errors= response.errors;
                            let htmlErrors= "<ul>";
                            for (let err in errors){
                                htmlErrors+= '<li>'+errors[err][0]+'</li>'
                            }
                            htmlErrors += "</ul>"
                            Vue.swal( {
                                icon:'error',
                                title:response.message,
                                html: htmlErrors
                            }).then(()=>self.disabled=false)
                        })

                    }else{
                        result.json().then(response => {
                            Vue.swal({
                                title: response.message,
                                html: '<small>'+response.report+'</small>',
                                confirmButtonText: 'Ok',
                                confirmButtonColor: '#91c18e'
                            }).then(()=>{self.disabled=false;self.resetForm()})
                        });
                    }
                })
                .catch(errors => {
                    console.log(errors)
            });

        },
    }
}
</script>

<style>
/* Scoped Styles */
</style>

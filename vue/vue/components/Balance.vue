<template>
    <div class="col-sm-10 products">
        <h4>Баланс</h4>

        <div class="form-group">
            <div class="col-sm-4">
                <label>ID МФО</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="mfo.id">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4">
                <label>Ключ МФО</label>
            </div>
            <div class="col-sm-8">
                <input type="password" class="form-control" v-model="mfo.token">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-8 col-sm-offset-4">
                <button type="submit" class="btn btn-primary" @click.prevent="getBalance()" :disabled="loading == true">Запросить</button>
            </div>
        </div>

        <div class="row" v-show="resbal.result != 0">
            <div class="col-sm-12">
                <div>Статус: {{ resbal.status }}</div>
            </div>
        </div>
        <div class="row" v-show="resbal.message != ''">
            <div class="col-sm-12">
                <div>{{ resbal.message }}</div>
            </div>
        </div>
        <div class="row" v-show="this.resbal.result != 0">
            <div class="col-sm-12">
                <div>Баланс: {{ resbal.amount }}</div>
            </div>
        </div>
    </div>

</template>
<script>
    import axios from 'axios';
    import sha1 from 'sha1';

    export default {

        data() {
            return {
                mfo: {
                    id: 0,
                    token: ''
                },
                resbal: {
                    result: 0,
                    status: 0,
                    balance: 0,
                    message: ''
                },
                loading: false
            }
        },
        created() {
        },
        methods: {
            getBalance() {
                this.resbal.result = 0;
                this.loading = true;
                axios({
                    method: 'post',
                    url: '/mfo/account/balance',
                    responseType: 'json',
                    data: this.getData(),
                    headers: {
                        'X-Mfo' : this.mfo.id,
                        'X-Token' : this.calcToken(this.getData()),
                        'Content-type': 'application/json; charset=UTF-8',
                    }
                }).then(response => {
                    this.resbal.result = 1;
                    this.resbal.message = response.data.message;
                    this.resbal.status = response.data.status;
                    if (this.resbal.status == 1) {
                        this.resbal.amount = response.data.amount;
                    } else {
                        this.resbal.amount = 0;
                    }

                }).catch(error => {
                    console.log(error);
                    this.resbal.message = error;
                    this.resbal.status = 0;
                    this.resbal.amount = 0;
                }).finally(() => (this.loading = false))
            },
            getData() {
                return {};
            },
            calcToken(data) {
                return sha1(
                    sha1(this.mfo.token)+
                    sha1(JSON.stringify(data))
                );
            }
        }
    }
</script>

<style scoped>
    .products {
        border: 1px solid grey;
        border-radius: 10px;
        margin: 10px;
        padding: 10px;
    }
</style>
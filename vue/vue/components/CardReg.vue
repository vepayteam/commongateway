<template>
    <div class="col-sm-10 products">
        <h4>Регистрация карты</h4>

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
            <div class="col-sm-4">
                <label>Карта</label>
            </div>
            <div class="col-sm-8">
                <div><label><input type="radio" v-model="card.cardtype" value="0"> Для автоплатежа</label></div>
                <div><label><input type="radio" v-model="card.cardtype" value="1"> Для выплаты</label></div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-8 col-sm-offset-4">
                <button type="submit" class="btn btn-primary" @click.prevent="addRegcard()" :disabled="loading == true">Зарегистрировать</button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12" v-show="respay.id > 0">
                <div>ID: {{ respay.id }}</div>
                <button type="button" class="btn btn-default" @click.prevent="getState()" :disabled="loading == true">Обновить статус</button>
            </div>
        </div>
        <div class="row" v-show="respay.result != 0">
            <div class="col-sm-12">
                <div>Статус: {{ respay.status }}</div>
            </div>
        </div>
        <div class="row" v-show="respay.message != ''">
            <div class="col-sm-12">
                <div>{{ respay.message }}</div>
            </div>
        </div>
        <div class="row" v-show="respay.url != ''">
            <div class="col-sm-12">
                <div>Url: <a :href="respay.url" target="_blank">{{ respay.url }}</a></div>
            </div>
        </div>
        <div class="row" v-show="respay.card.num != ''">
            <div class="col-sm-12">
                <div>Карта: id = {{ respay.card.id }}, {{ respay.card.num }}, {{ respay.card.exp }} </div>
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
                card: {
                    cardtype: 0
                },
                respay: {
                    id: 0,
                    result: 0,
                    status: 0,
                    card: {
                        id: 0,
                        num: '',
                        exp: '',
                    },
                    url: '',
                    message: ''
                },
                loading: false
            }
        },
        created() {
        },
        methods: {
            addRegcard() {
                this.respay.result = 0;
                this.loading = true;
                axios({
                    method: 'post',
                    url: '/mfo/card/reg',
                    responseType: 'json',
                    data: this.getData(),
                    headers: {
                        'X-Mfo' : this.mfo.id,
                        'X-Token' : this.calcToken(this.getData()),
                        'Content-type': 'application/json; charset=UTF-8',
                    }
                }).then(response => {
                    this.respay.result = 1;
                    this.respay.message = response.data.message;
                    this.respay.status = response.data.status;
                    if (this.respay.status == 1) {
                        this.respay.id = response.data.id;
                        this.respay.url = response.data.url;
                    } else {
                        this.respay.id = 0;
                        this.respay.url = '';
                    }

                }).catch(error => {
                    console.log(error);
                    this.respay.message = error;
                    this.respay.status = 0;
                }).finally(() => (this.loading = false))
            },
            getData() {
                return {'type': this.card.cardtype };
            },
            calcToken(data) {
                return sha1(
                    sha1(this.mfo.token)+
                    sha1(JSON.stringify(data))
                );
            },

            getState() {
                if (this.respay.id > 0) {
                    this.respay.result = 0;
                    this.loading = true;
                    axios({
                        method: 'post',
                        url: '/mfo/card/get',
                        responseType: 'json',
                        data: this.getStateData(),
                        headers: {
                            'X-Mfo' : this.mfo.id,
                            'X-Token' : this.calcToken(this.getStateData()),
                            'Content-type': 'application/json; charset=UTF-8',
                        }
                    }).then(response => {
                        this.respay.result = 1;
                        this.respay.message = response.data.message;
                        this.respay.status = response.data.status;
                        if (this.respay.status == 1) {
                            this.respay.card.id = response.data.card.id;
                            this.respay.card.num = response.data.card.num;
                            this.respay.card.exp = response.data.card.exp;
                        }

                    }).catch(error => {
                        console.log(error);
                        this.respay.message = error;
                        this.respay.status = 0;
                    }).finally(() => (this.loading = false))
                }
            },

            getStateData() {
                return {'id': this.respay.id, 'type': this.card.cardtype};

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
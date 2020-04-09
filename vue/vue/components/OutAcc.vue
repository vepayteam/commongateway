<template>
    <div class="col-sm-10 products">
        <h4>Выплата на счет</h4>

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
                <label>Вид</label>
            </div>
            <div class="col-sm-8">
                <div><label><input type="radio" class="radio" v-model="out.type" value="0"> Физлицо</label></div>
                <div><label><input type="radio" class="radio" v-model="out.type" value="1"> Юрлицо</label></div>
            </div>
        </div>

        <div class="form-group" v-show="out.type == 0">
            <div class="col-sm-4">
                <label>ФИО получателя</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="out.fio">
            </div>
        </div>

        <div class="form-group" v-show="out.type == 1">
            <div class="col-sm-4">
                <label>Наименование получателя</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="out.name">
            </div>
        </div>
        <div class="form-group" v-show="out.type == 1">
            <div class="col-sm-4">
                <label>ИНН получателя</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="out.inn">
            </div>
        </div>
        <div class="form-group" v-show="out.type == 1">
            <div class="col-sm-4">
                <label>КПП получателя</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="out.kpp">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4">
                <label>Счет в банке</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="out.account">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4">
                <label>БИК банка</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="out.bic">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4">
                <label>Комментарий (назначение платежа)</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="out.descript">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4">
                <label>Сумма</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="out.sum">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4">
                <label>ExtID</label>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control" v-model="out.extid">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-8 col-sm-offset-4">
                <button type="submit" class="btn btn-primary" @click.prevent="addOut()" :disabled="loading == true">Создать</button>
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
        <div class="row">
            <div class="col-sm-12">
                <div>{{ respay.message }}</div>
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
                out: {
                    type: 1,
                    fio: '',
                    name: '',
                    inn: '',
                    kpp: '',
                    account: '',
                    bic: '',
                    descript: '',
                    sum: 100.00,
                    extid: ''
                },
                respay: {
                    id: 0,
                    result: 0,
                    status: 0,
                    message: ''
                },
                loading: false
            }
        },
        created() {
        },
        methods: {
            addOut() {
                this.respay.result = 0;
                this.loading = true;
                axios({
                    method: 'post',
                    url: this.out.type == 0 ? '/mfo/out/payacc' : '/mfo/out/payul',
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
                    if (response.data.id !== undefined) {
                        this.respay.id = response.data.id;
                    } else {
                        this.respay.id = 0;
                    }

                }).catch(error => {
                    console.log(error);
                    this.respay.message = error;
                    this.respay.status = 0;
                }).finally(() => (this.loading = false))
            },
            getData() {
                if (this.out.type == 0) {
                    return {'fio': this.out.fio, 'account': this.out.account, 'bic': this.out.bic, 'descript': this.out.descript, 'amount': this.out.sum, 'extid': this.out.extid};
                } else {
                    return {'name': this.out.name, 'inn': this.out.inn, 'kpp': this.out.kpp, 'account': this.out.account, 'bic': this.out.bic, 'descript': this.out.descript, 'amount': this.out.sum, 'extid': this.out.extid};
                }
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
                        url: '/mfo/out/state',
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

                    }).catch(error => {
                        console.log(error);
                        this.respay.message = error;
                        this.respay.status = 0;
                    }).finally(() => (this.loading = false))
                }
            },

            getStateData() {
                return {'id': this.respay.id};

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
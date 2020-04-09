<script src="../testclimfo.js"></script>
<template>
    <div class="col-sm-12">
        <h2>Тест клиент МФО</h2>

        <div class="row">
            <div class="col-sm-12">
                <router-link to="/out/card">Выплата на карту</router-link>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <router-link to="/out/acc">Выплата на счет</router-link>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <router-link to="/card/reg">Регистрация карты</router-link>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <router-link to="/pay/lk">Погашение займа</router-link>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <router-link to="/pay/auto">Автоплатеж</router-link>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <router-link to="/account/balance">Баланс</router-link>
            </div>
        </div>

        <router-view></router-view>

    </div>
</template>

<script>

    export default {

        data() {
            return {
            }
        },

        mounted: function () {
            //console.log('App component mounted');
        }

    }
</script>

<style>

</style>
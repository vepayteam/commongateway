import Vue from 'vue';
import VueRouter from 'vue-router';

import App from './components/App.vue';
import CardReg from './components/CardReg.vue';
import OutCard from './components/OutCard.vue';
import OutAcc from './components/OutAcc.vue';
import Pay from './components/Pay.vue';
import AutoPay from './components/AutoPay.vue';
import Balance from './components/Balance.vue';

Vue.component('CardReg',CardReg);
Vue.component('OutCard',OutCard);
Vue.component('OutAcc',OutAcc);
Vue.component('Pay',Pay);
Vue.component('AutoPay',AutoPay);
Vue.component('Balance',Balance);

Vue.use(VueRouter);
 
const routes = [
    {
        path : '/card/reg',
        component : CardReg,
    },
    {
        path : '/out/card',
        component : OutCard,
    },
    {
        path : '/out/acc',
        component : OutAcc,
    },
    {
        path : '/pay/lk',
        component : Pay,
    },
    {
        path : '/pay/auto',
        component : AutoPay,
    },
    {
        path : '/account/balance',
        component : Balance,
    }
];
 
const router = new VueRouter ({
    routes
});
 
import axios from 'axios';
axios.defaults.headers.common['Authorization'] = 'Bearer 100-token';
 
new Vue({
    router,
    el: '#app',
    render: h => h(App)
});
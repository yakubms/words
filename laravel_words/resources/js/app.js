import Vue from 'vue';
import VueRouter from 'vue-router';
import routes from './routes';

window.axios = require('axios');
Vue.prototype.$apiToken = Laravel.apiToken;
window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + Laravel.apiToken;
Vue.use(VueRouter);
import { ClientTable, Event } from 'vue-tables-2';
Vue.use(ClientTable, {}, false, 'bulma', 'default');

const app = new Vue({
    el: '#app',
    router: new VueRouter(routes)
});

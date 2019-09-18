import Vue from 'vue';
import VueRouter from 'vue-router';
import routes from './routes';
import Storage from 'vue-ls';

const lsOptions = {
    namespace: 'vuejs__', // key prefix
    name: 'ls', // name variable Vue.[ls] or this.[$ls],
    storage: 'local', // storage name session, local, memory
}

Vue.use(Storage, lsOptions);
window.lsExpiryTime = 60 * 60 * 1000;
window.LSMONTH = 60 * 60 * 24 * 30 * 1000;

window.axios = require('axios');
Vue.prototype.$apiToken = Laravel.apiToken;
window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + Laravel.apiToken;
Vue.use(VueRouter);
import { ClientTable, Event } from 'vue-tables-2';
Vue.use(ClientTable, {}, false, 'bulma', 'default');

const router = new VueRouter(routes);

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!Laravel.apiToken) {
            window.location.replace('/login');
        }
    }
    next();
})

const app = new Vue({
    el: '#app',
    router: router,
    computed: {
        burgerClass() {
            return {
                'navbar-burger': true,
                burger: true,
                'is-active': this.isActive
            }
        },
        navbarClass() {
            return {
                'navbar-menu': true,
                'is-active': this.isActive
            }
        }
    },
    data: {
        isActive: false
    },
    methods: {
        clearStorage() {
            this.$ls.clear('level');
            this.$ls.clear('projects');
        },
        toggleBurger() {
            this.isActive = !this.isActive;
        },
        onLogout() {
            this.clearStorage();
            document.getElementById('logout-form').submit();
        }
    },
    mounted() {
        // remove expired local storage
        for (const key of Object.keys(localStorage)) {
            const vueStorageName = key.replace(lsOptions.namespace, '');
            if (Vue.ls.get(vueStorageName, undefined) === undefined) {
                Vue.ls.remove(vueStorageName);
            }
        }
    }
});

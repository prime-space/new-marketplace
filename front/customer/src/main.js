import './scss/main.scss';
import './scss/vue-snack.css';

import Vue from 'vue'
import App from './App.vue'
import router from './router'
import i18n from './i18n';
import VueResource from 'vue-resource';
import vuetify from './plugins/vuetify';
import store from './store';

let snack = {
    install(Vue) {
        Vue.prototype.$snack = {
            listener: null,
            success(data) {
                if (null !== this.listener) {
                    this.listener(data.text);
                }
            },
            danger(data) {
                return this.success(data);
            }
        }
    }
}


// import store from './store'

Vue.use(VueResource);
Vue.use(snack);

Vue.config.productionTip = false;

new Vue({
    router,
    i18n,
    vuetify,
    store,
    render: h => h(App)
}).$mount('#app');

import './scss/main.scss';

import Vue from 'vue'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify';
import VueResource from 'vue-resource';
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

Vue.use(snack);
Vue.use(VueResource);
Vue.config.productionTip = false

config.productCategoryTop = config.productCategoryTreeFlat.filter(x => x.parentId === 1);

new Vue({
    router,
    vuetify,
    store,
    render: h => h(App)
}).$mount('#app')

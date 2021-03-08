import './scss/main.scss';
import './scss/vue-snack.css';

import Vue from 'vue';
import App from './App.vue';
import VueResource from 'vue-resource';
import vuetify from '@/plugins/vuetify';
import VueClipboards from "vue-clipboards";
import store from './store';
import Ewll from './../Main';
import router from './router'

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

Vue.use(VueResource);
Vue.use(snack);
Vue.use(VueClipboards);
Vue.use(Ewll);
Vue.config.productionTip = false;

function hasTreeElementsRecursively(node) {
    let isNodeFound = false;
    for (let i in config.productCategoryTreeFlat) {
        let el = config.productCategoryTreeFlat[i];
        if (isNodeFound) {
            if (el.parentId !== node.id) {
                break;
            }
            if (true === hasTreeElementsRecursively(el)) {
                return true;
            }
        } else if (el.id === node.id) {
            if (el.elementsNum > 0) {
                return true;
            }
            isNodeFound = true;
        }
    }

    return false;
}

let cursor = null;
for (let i in config.productCategoryTreeFlat) {
    let el = config.productCategoryTreeFlat[i];
    if (el.children === undefined) {
        el.children = [];
    }
    el.haveElements = hasTreeElementsRecursively(el);
    if (el.id === 1) {
        el.parent = null;
        config.productCategoryTree = [el];
    } else {
        while (cursor.id !== el.parentId) {
            cursor = cursor.parent;
        }
        el.parent = cursor;
        cursor.children.push(el);
    }
    cursor = el;
}

function sortNodeRecursively(node) {
    node.children.sort((a, b) => (a.code > b.code) ? 1 : ((b.code > a.code) ? -1 : 0));
    for (let i in node.children) {
        sortNodeRecursively(node.children[i]);
    }
}

sortNodeRecursively(config.productCategoryTree[0]);

new Vue({
    render: (h) => h(App),
    router,
    vuetify,
    store,
}).$mount('#app');

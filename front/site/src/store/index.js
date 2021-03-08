import Vue from 'vue'
import Vuex from 'vuex'
import search from './search'
import breadcrumbs from './breadcrumbs'

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        search,
        breadcrumbs,
    }
})

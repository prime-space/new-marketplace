import Vue from 'vue'
import Vuex from 'vuex'
import breadcrumbs from './breadcrumbs'
import confirmer from './confirmer'
import unsaved from './unsaved'

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        breadcrumbs,
        confirmer,
        unsaved,
    }
})

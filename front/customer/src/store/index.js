import Vue from 'vue'
import Vuex from 'vuex'
import confirmer from './confirmer'

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        confirmer,
    }
})

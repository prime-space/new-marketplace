const initialState = {
    active: false,
    title: '',
    body: '',
    resolve: null,
    reject: null,
};

const state = Object.assign({}, initialState);

const mutations = {
    'ACTIVATE': (state, payload) => {
        Object.assign(state, payload)
    },

    'DEACTIVATE': (state) => {// eslint-disable-line no-unused-vars
        state = Object.assign(state, initialState)
    }
};

const actions = {
    ask: ({commit}, {title, body}) => {
        return new Promise((resolve, reject) => {
            commit('ACTIVATE', {
                active: true,
                title,
                body,
                resolve,
                reject
            })
        })
    }
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
}

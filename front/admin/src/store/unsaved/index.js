const initialState = {
    isUnsaved: false,
};

const state = Object.assign({}, initialState);

const mutations = {
    'setUnsaved': (state) => {
        state.isUnsaved = true;
    },
    'setSaved': (state) => {
        state.isUnsaved = false;
    },
};

export default {
    namespaced: true,
    state,
    mutations,
}

const initialFilters = {
    query: '',
    productCategoryId: null,
}

const initialState = {
    filters: Object.assign({}, initialFilters),
    loading: false,
    counter: 0,
};

const state = Object.assign({}, initialState);

const setFilters = function (state, filters) {
    for (let i in filters) {
        state.filters[i] = filters[i];
    }
    state.counter++;
}

const mutations = {
        'set': (state, filters) => {
            setFilters(state, filters);
            // global.console.log(state);
        },
        'search': (state, filters) => {
            setFilters(state, filters);
            state.loading = true;
        },
        'loaded': (state) => {
            state.loading = false;
        },
        'reset': (state) => {
            state.filters = Object.assign({}, initialFilters);
        },
    }
;

export default {
    namespaced: true,
    state,
    mutations,
}

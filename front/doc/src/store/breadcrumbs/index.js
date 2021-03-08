const initialState = {
    data: [],
    shortData: [],
};

const state = Object.assign({}, initialState);

const mutations = {
    'set': (state, data) => {
        state.data = data;
        let shortData = [];
        // if (data.length > 1) {
        //     shortData.push({
        //         text: 'Назад',
        //         to: data[data.length-2].to,
        //         disabled: false,
        //         exact: true,
        //     });
        // }
        shortData.push(data[data.length-1]);
        state.shortData = shortData;

        let text = state.data[state.data.length-1].text;
        let titleParts = document.title.split(' | ');
        document.title = text + ' | ' + titleParts[1];
    },
    'fill': (state, data) => {
        let text = state.data[state.data.length-1].text;
        Object.entries(data).forEach(([k, v])=>{
            text = text.replace('{'+k+'}', v);
        });
        state.shortData[state.shortData.length-1].text = text;
        let titleParts = document.title.split(' | ');
        document.title = text + ' | ' + titleParts[1];
    },
};

export default {
    namespaced: true,
    state,
    mutations,
}

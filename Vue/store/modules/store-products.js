import {VhHelper as Vh} from "../../vaahvue/helpers/VhHelper";

//---------Variables
let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
//---------/Variables

let ajax_url = base_url+"/backend/store/products";
let query = {
    page: null,
    q: null,
    sort: null,
    filter: {
        is_active: null,
        trashed: null,
    },
    include: null,
};

let action = {
    type: null,
    items: [],
    inputs: {},
}



let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Product';

export default {
    namespaced: true,
    state: {
        base_url: base_url,
        ajax_url: ajax_url,
        assets: null,
        data:{
            model: model_namespace,
            list: null,
            item: null,
            fillable:null,
            query: Vh.clone(query),
            action: Vh.clone(action),
            search: {
                delay_time: 600, // time delay in milliseconds
                delay_timer: 0 // time delay in milliseconds
            },
            empty: {
                query: Vh.clone(query),
                action: Vh.clone(action),
            },
            view: 'large',
            form: {
                type: 'Create',
                label_position: 'on-border',
                action: null,
                is_button_loading: null
            },
            show_filters: null,
            is_list_loading: null,
            count_filters: 0,
        },


    },
    //=========================================================================
    mutations:{
        updateState: function (state, payload) {
            state[payload.key] = payload.value;
        },
        //-----------------------------------------------------------------
    },
    //=========================================================================
    actions:{
        //-----------------------------------------------------------------
        async getAssets({ state, commit, dispatch, getters }) {

            if(!state.assets)
            {
                let payload;
                let url = state.ajax_url+'/assets';
                let params = {};
                let data = await Vh.ajax(url, params);
                payload = {
                    key: 'assets',
                    value: data.data.data
                };
                commit('updateState', payload);
            }

        },
        //-----------------------------------------------------------------
        updateView({ state, commit, dispatch, getters }, payload) {
            let view ='large';
            let update;

            if(payload.name === 'products.create'
            || payload.name === 'products.read'
            || payload.name === 'products.update')
            {
                view = 'small';
            }

            state.data.view = view;

            update = {
                key: 'data',
                value: state.data
            };
            commit('updateState', update);

        },
        //-----------------------------------------------------------------
    },
    //=========================================================================
    getters:{
        state(state) {return state;},
        assets(state) {return state.assets;},
        data(state) {return state.data;},
    }

}

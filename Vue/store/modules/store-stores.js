import {VhHelper as Vh} from "../../vaahvue/helpers/VhHelper";

//---------Variables
let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
//---------/Variables

let ajax_url = base_url+"/backend/store/stores";

export default {
    namespaced: true,
    state: {
        ajax_url: ajax_url,
        assets: null,
        page:{
            list: null,
            item: null,
            view: 'large',
            query: {
                page: 1,
                q: null,
                trashed: null,
                filter: null,
                sort_by: null,
                sort_order: 'desc',
            },
            show_filters: null,
            actions: {
                type: null,
                items: [],
                inputs: {},
            }
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
            let view;
            let update;

            if(payload.name === 'stores.create'
            || payload.name === 'stores.view'
            || payload.name === 'stores.edit')
            {
                view = 'medium';
            };

            update = {
                key: 'view',
                value: view
            };

            commit('updateState', update);

        },
        //-----------------------------------------------------------------
    },
    //=========================================================================
    getters:{
        state(state) {return state;},
        assets(state) {return state.assets;},
        page(state) {return state.page;},
    }

}

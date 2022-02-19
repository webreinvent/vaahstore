import {VaahHelper as Vaah} from "../../vaahvue/helpers/VaahHelper";

//---------Variables
let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let current_url = document.getElementById('current_url').getAttribute('content');
let debug = document.getElementById('debug').getAttribute('content');
//---------/Variables


export default {
    namespaced: true,
    //=========================================================================
    state: {
        debug: debug,
        base_url: base_url,
        current_url: current_url,
        assets: null,
        assets_reload: null,
        permissions: null,
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

            let root_assets = state.assets;

            if(!state.assets || state.assets_reload == true)
            {

                let params = {};

                let url = state.current_url + '/assets';
                let data = await Vaah.ajax(url, params);

                if (!root_assets) {
                    root_assets = {};
                }

                for (let index in data.data.data) {
                    root_assets[index] = data.data.data[index];
                }

                let payload = {
                    key: 'assets',
                    value: root_assets
                };

                this.commit('root/updateState', payload);

                payload = {
                    key: 'assets_reload',
                    value: false
                };

                this.commit('root/updateState', payload);

            }

        },
        //-----------------------------------------------------------------
        reloadAssets: function ({ state, commit, dispatch, getters }) {
            let payload = {
                key: 'assets_reload',
                value: true
            };
            commit('updateState', payload);
            dispatch('getAssets');
        },
        //-----------------------------------------------------------------
    },
    //=========================================================================
    getters:{
        state(state) {return state;},
        assets(state) {return state.assets;},
        permissions(state) {return state.permissions;},
    }

}

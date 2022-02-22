import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

import root from './modules/root';
import stores from './modules/store-stores';

export const store = new Vuex.Store({
    modules: {
        root: root,
        stores: stores,
    }
});

import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

import root from './modules/root';
import stores from './modules/store-stores';
import vendors from './modules/store-vendors';
import brands from './modules/store-brands';
import products from './modules/store-products';

export const store = new Vuex.Store({
    modules: {
        root: root,
        stores: stores,
        vendors: vendors,
        brands: brands,
        products: products,
    }
});

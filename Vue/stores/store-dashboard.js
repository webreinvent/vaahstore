import {toRaw, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import {vaah} from "../vaahvue/pinia/vaah";
import qs from "qs";
import dayjs from "dayjs";


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/dashboard";


export const useDashboardStore = defineStore({
    id: 'dashboard',
    state: () => ({
        base_url: base_url,
        ajax_url: ajax_url,
        assets: null,
        stores: null,
        selected_store_at_dashboard: null,
        default_store: null,
    }),
    getters: {

    },
    actions: {
        //---------------------------------------------------------------------
        async getAssets() {

            await vaah().ajax(
                this.ajax_url+'/assets',
                this.afterGetAssets,
            );
        },
        //---------------------------------------------------------------------
        afterGetAssets(data, res)
        {
            if(data)
            {
                this.assets = data;
                this.stores = data.stores;
            }
        },
        //---------------------------------------------------------------------
        searchStoreForListQuery(event){
            const query = event.query.toLowerCase();
            this.filteredStores = this.stores.filter(store =>
                store.name.toLowerCase().includes(query)
            );
        },
        setDefaultStoreForAtDashboard(){
            this.default_store = this.stores.find(store => store.is_default === 1);
            if (this.default_store) {
                this.selected_store_at_dashboard = this.default_store;
            }
        },
    }
});


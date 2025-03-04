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

            }
        },
        //---------------------------------------------------------------------

    }
});


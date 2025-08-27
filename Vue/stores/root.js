import {defineStore, acceptHMRUpdate} from 'pinia';
import {vaah} from "../vaahvue/pinia/vaah";
import {toRaw,watch} from "vue";
import {useRoute} from "vue-router";
let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store";

export const useRootStore = defineStore({
    id: 'root',
    state: () => ({
        base_url: base_url,
        ajax_url: ajax_url,
        assets: null,
        gutter: 20,
        show_progress_bar: false,
        assets_is_fetching: true,
        filter_end_date: null,
        filter_start_date: null,
        datatable_pt:{
            column:{
                class: "py-[0.17rem] line-height-0"
            },

        },
        stores:null,
        selected_store_at_sidebar:null,
        query: {
            selected_store: null,

        },
    }),
    getters: {},
    actions: {
        async getAssets() {
            if(this.assets_is_fetching === true){
                this.assets_is_fetching = false;

                vaah().ajax(
                    this.ajax_url+'/assets',
                    this.afterGetAssets,
                );
            }
        },

        //---------------------------------------------------------------------
        async afterGetAssets(data, res)
        {

            if(data)
            {
                // alert('as')
                this.assets = data;
                this.stores = await data.stores;
                this.selected_store_at_sidebar = data.selected_store_at_sidebar ?? data.default_store;
                this.default_store = data.default_store;
                const chartDate = data.charts_data_filtered_by;

                this.updatedChartsDateFilter(chartDate);
                if (!this.default_store?.id) {
                    await this.$router.push({ query: {} });

                }
                const default_store_id = this.default_store?.id;

                this.$router.push({ query: { selected_store: default_store_id } });

            }
        },
        async updatedChartsDateFilter(chart_date) {
            let start_date, end_date;
            const today = new Date();
            const charts_filter_by_date = JSON.parse(chart_date);

            if (charts_filter_by_date?.custom?.start_date && charts_filter_by_date?.custom?.end_date) {
                start_date = new Date(charts_filter_by_date.custom.start_date);
                end_date = new Date(charts_filter_by_date.custom.end_date);
            } else {
                switch (true) {
                    case charts_filter_by_date && charts_filter_by_date.today:
                        start_date = new Date(today.setHours(0, 0, 0, 0));
                        end_date = new Date(today.setHours(23, 59, 59, 999));
                        break;
                    case charts_filter_by_date && charts_filter_by_date['last-7-days']:
                        end_date = new Date(today);
                        start_date = new Date(today);
                        start_date.setDate(today.getDate() - 6); // last 7 days, including today
                        break;
                    case charts_filter_by_date && charts_filter_by_date['last-1-month']:
                        end_date = new Date(today);
                        start_date = new Date(today);
                        start_date.setMonth(today.getMonth() - 1); // last 1 month
                        break;
                    case charts_filter_by_date && charts_filter_by_date['last-1-year']:
                        end_date = new Date(today);
                        start_date = new Date(today);
                        start_date.setFullYear(today.getFullYear() - 1); // last 1 year
                        break;
                    default:
                        start_date = new Date(today.setHours(0, 0, 0, 0));
                        end_date = new Date(today.setHours(23, 59, 59, 999));
                        break;
                }
            }

            this.filter_start_date = start_date;
            this.filter_end_date = end_date;

        },


        async to(path)
        {
            this.$router.push({path: path})
        },
        showProgress()
        {
            this.show_progress_bar = true;
        },
        hideProgress()
        {
            this.show_progress_bar = false;
        },

        //---------------------------------------------------------------------

        async setStore(event) {
            let store = toRaw(event.value);
            this.vh_st_store_id = store.id;
            this.$router.push({ query: { selected_store: this.vh_st_store_id } });
        },
        //---------------------------------------------------------------------

        initWatchStoreChange(route, store, callback) {  // Accepting 'route' and 'store' as parameters
            if (!route) {
                return;
            }

            watch(() => route.query.selected_store, async (newVal, oldVal) => {

                // If the query is not provided (null, undefined, or empty), handle it separately
                if (!newVal) {

                    if (callback && typeof callback === 'function') {
                        await callback();
                    }

                    return;
                }

                if (newVal !== oldVal) {
                    this.query.selected_store = newVal;

                    if (store && store.query) {
                        store.query.selected_store = newVal;
                    }

                    if (callback && typeof callback === 'function') {
                        await callback();
                    }
                }

            }, { immediate: true });
        }


    }
})


// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useRootStore, import.meta.hot))
}

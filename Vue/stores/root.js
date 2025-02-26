import {defineStore, acceptHMRUpdate} from 'pinia';
import {vaah} from "../vaahvue/pinia/vaah";
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
        panel_pt: {
            header:{
                class: "p-2"
            },
            content:{
                class: "p-2"
            }
        },
        datatable_pt:{
            column:{
                class: "py-[0.17rem] line-height-0"
            },

        }
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
        afterGetAssets(data, res)
        {
            if(data)
            {
                this.assets = data;

                const chartDate = data.charts_data_filtered_by;
                this.updatedChartsDateFilter(chartDate);
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
        }

    }
})


// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useRootStore, import.meta.hot))
}

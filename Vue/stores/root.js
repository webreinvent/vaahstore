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
            let startDate, endDate;
            const today = new Date();
            const chartDate = JSON.parse(chart_date);

            if (chartDate?.custom?.start_date && chartDate?.custom?.end_date) {
                startDate = new Date(chartDate.custom.start_date);
                endDate = new Date(chartDate.custom.end_date);
            } else {
                switch (true) {
                    case chartDate && chartDate.today:
                        startDate = new Date(today.setHours(0, 0, 0, 0));
                        endDate = new Date(today.setHours(23, 59, 59, 999));
                        break;
                    case chartDate && chartDate['last-7-days']:
                        endDate = new Date(today);
                        startDate = new Date(today);
                        startDate.setDate(today.getDate() - 6); // last 7 days, including today
                        break;
                    case chartDate && chartDate['last-1-month']:
                        endDate = new Date(today);
                        startDate = new Date(today);
                        startDate.setMonth(today.getMonth() - 1); // last 1 month
                        break;
                    case chartDate && chartDate['last-1-year']:
                        endDate = new Date(today);
                        startDate = new Date(today);
                        startDate.setFullYear(today.getFullYear() - 1); // last 1 year
                        break;
                    default:
                        startDate = new Date(today.setHours(0, 0, 0, 0));
                        endDate = new Date(today.setHours(23, 59, 59, 999));
                        break;
                }
            }

            this.filter_start_date = startDate;
            this.filter_end_date = endDate;

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

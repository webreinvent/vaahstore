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

            }
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

import { watch } from 'vue'
import { acceptHMRUpdate, defineStore } from 'pinia'
import { vaah } from '../vaahvue/pinia/vaah'
import qs from 'qs'


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/settings";

export const useSettingStore = defineStore({
    id: 'settings',
    state: () => ({
        base_url: base_url,
        ajax_url: ajax_url,
        assets_is_fetching: true,
        assets: null,
        list: null,
        quantity:null,
        is_button_disabled:false,

        user_roles_menu: null,
        meta_content: null,
        is_btn_loading: false,
        crud_options: [
            {
                label: 'All',
                value: 'All'
            },
            {
                label: 'Stores',
                value: 'Store'
            },
            {
                label: 'Vendors',
                value: 'Vendors'
            },
            {
                label: 'Vendor Products',
                value: 'VendorsProduct'
            },

            {
                label: 'Products',
                value: 'Product'
            },
            {
                label: 'Product Variations',
                value: 'ProductVariations'
            },
            {
                label: 'Product Attributes',
                value: 'ProductAttribute'
            },
            {
                label: 'Product Medias',
                value: 'ProductMedia'
            },

            {
                label: 'Product Stocks',
                value: 'ProductStock'
            },

            {
                label: 'Brands',
                value: 'Brand'
            },

            {
                label: 'Warehouses',
                value: 'Warehouses'
            },
            {
                label: 'Attributes',
                value: 'Attributes'
            },

            {
                label: 'Attributes Group',
                value: 'AttributeGroups'
            },
            {
                label: 'Addresses',
                value: 'Address'
            },
            {
                label: 'Wishlists',
                value: 'Wishlists'
            },

            {
                label: 'Customer',
                value: 'Customer'
            },
            {
                label: 'Customer Group',
                value: 'CustomerGroup'
            },


        ],
        selected_crud:null,
    }),
    getters: {

    },
    actions: {
        //---------------------------------------------------------------------
        async onLoad(route)
        {

        },
        //---------------------------------------------------------------------
        async getAssets() {

            if (this.assets_is_fetching === true) {
                this.assets_is_fetching = false;

                vaah().ajax(
                    this.ajax_url+'/assets',
                    this.afterGetAssets,
                );
            }
        },
        //---------------------------------------------------------------------
        afterGetAssets(data, res) {
            if (data) {
                this.assets = data;
            }
        },
        //---------------------------------------------------------------------
        async getList() {
            let options = {
            };

            await vaah().ajax(
                this.ajax_url,
                await this.afterGetList,
                options
            );
        },
        //---------------------------------------------------------------------
        async afterGetList (data, res) {

            this.is_btn_loading = false;

            if (data) {
                this.list = data;
            }
        },
        //--------------------------------------------------------------------
        async createBulkRecords( data) {

            this.is_button_disabled = true;


            let query = {
                params:{
                    crud: this.selected_crud,
                    quantity:this.quantity
                }
            };

            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/fill/bulk/method',
                this.createBulkRecordsAfter,
                options
            );


        },


        //---------------------------------------------------------------------

        async createBulkRecordsAfter (data, res) {

            if(res.data.success === true)
            {
                this.quantity = null;
                this.selected_crud = null;
            }
            this.is_button_disabled = false;

        },
        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
        getCopy(value)
        {
            let text =  "{!! config('settings.global."+value+"'); !!}";
            navigator.clipboard.writeText(text);
            vaah().toastSuccess(['Copied']);
        },
        //---------------------------------------------------------------------
        async storeSettings() {
            let options = {
                method: 'put',
                params:{
                    list: this.list
                }
            };

            let ajax_url = this.ajax_url;
            await vaah().ajax(ajax_url, this.storeSettingsAfter, options);
        },
        //---------------------------------------------------------------------
        storeSettingsAfter(){
            this.getList();
        },

    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useSettingStore, import.meta.hot))
}




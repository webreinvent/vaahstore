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

        user_roles_menu: null,
        meta_content: null,
        is_btn_loading: false,
        crud_options: [
            {
                label: 'Store',
                value: 'Store'
            },
            {
                label: 'Address',
                value: 'Address'
            },
            {
                label: 'Wishlists',
                value: 'Wishlists'
            },
            {
                label: 'Brand',
                value: 'Brand'
            },
            {
                label: 'Customer',
                value: 'Customer'
            },
            {
                label: 'Customer Group',
                value: 'CustomerGroup'
            },
            {
                label: 'Vendor',
                value: 'Vendors'
            },
            {
                label: 'Product',
                value: 'Product'
            },
            {
                label: 'Product Variations',
                value: 'ProductVariations'
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
                label: 'Attribute Group',
                value: 'AttributeGroups'
            },
            {
                label: 'Product Attribute',
                value: 'ProductAttribute'
            },
            {
                label: 'Product Media',
                value: 'ProductMedia'
            },
            {
                label: 'Product Stock',
                value: 'ProductStock'
            },
            {
                label: 'Vendor Products',
                value: 'VendorsProduct'
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

            this.quantity = null;
            this.selected_crud = null;
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




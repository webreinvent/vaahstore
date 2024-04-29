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
        input_number_value:null,
        user_roles_menu: null,
        meta_content: null,
        is_btn_loading: false,
        is_checked:false,
        crud_options: [
            {
                label: 'All',
                value: 'All',
                isChecked: false,
                quantity: 0,
                count: 0
            },
            {
                label: 'Stores',
                value: 'Store',
                isChecked: false,
                quantity: 0,
                count: 0
            },
            {
                label: 'Vendors',
                value: 'Vendors',
                isChecked: false,
                quantity: 0,
                count: 0
            },
            {
                label: 'Vendor Products',
                value: 'VendorsProduct',
                isChecked: false,
                quantity: 0,
                count: 0
            },

            {
                label: 'Products',
                value: 'Product',
                isChecked: false,
                quantity: 0,
                count: 0 ,

            },
            {
                label: 'Product Variations',
                value: 'ProductVariations',
                isChecked: false,
                quantity: 0,
                count: 0
            },
            {
                label: 'Product Attributes',
                value: 'ProductAttribute',
                isChecked: false,
                quantity: 0
            },
            {
                label: 'Product Medias',
                value: 'ProductMedia',
                isChecked: false,
                quantity: 0,
                count: 0
            },

            {
                label: 'Product Stocks',
                value: 'ProductStock',
                isChecked: false,
                quantity: 0,
                count: 0
            },

            {
                label: 'Brands',
                value: 'Brand',
                isChecked: false,
                quantity: 0,
                count: 0
            },

            {
                label: 'Warehouses',
                value: 'Warehouses',
                isChecked: false,
                quantity: 0 ,
                count: 0
            },
            {
                label: 'Attributes',
                value: 'Attributes',
                isChecked: false,
                quantity: 0
            },

            {
                label: 'Attributes Group',
                value: 'AttributeGroups',
                isChecked: false,
                quantity: 0 ,
                count: 0
            },
            {
                label: 'Addresses',
                value: 'Address',
                isChecked: false,
                quantity: 0 ,
                count: 0
            },
            {
                label: 'Wishlists',
                value: 'Wishlists',
                isChecked: false,
                quantity: 0 ,
                count: 0
            },

            {
                label: 'Customer',
                value: 'Customer',
                isChecked: false,
                quantity: 0
            },
            {
                label: 'Customer Group',
                value: 'CustomerGroup',
                isChecked: false,
                quantity: 0 ,
                count: 0
            },


        ],
        selected_crud:[],
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


        checkAll(item) {
            if (item.label === 'All') {
                // Toggle isChecked for all options based on the isChecked state of the "All" option
                const allChecked = item.isChecked;
                this.crud_options.forEach(option => {
                    option.isChecked = !allChecked;
                });
            } else {
                // If it's not "All", toggle only the isChecked property of the clicked option
                item.isChecked = !item.isChecked;
            }
        },


        //--------------------------------------------------------------------
        async createBulkRecords() {


           this.selected_crud = this.crud_options.filter(item => item.isChecked);

            console.log(this.selected_crud)

            if (this.selected_crud.length === 0) {
                vaah().toastErrors(['Please Choose A crud First']);
                return;
            }

            const query = {selectedCrud : this.selected_crud};
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

            // if(res.data.success === true)
            // {
            //     this.quantity = null;
            //     this.selected_crud = null;
            // }
            this.updateCounts();
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

        fillAll() {
            const hasQuantity = this.crud_options.some(item => item.quantity > 0);

            if (!hasQuantity) {
                vaah().toastErrors(['Fill Quantity At least in a single column']);
                return;
            }

            // Get the quantity of the first item with a non-zero quantity
            const firstNonZeroQuantity = this.crud_options.find(item => item.quantity > 0).quantity;

            // Set the quantity of all items to the quantity of the first item with a non-zero quantity
            this.crud_options.forEach(item => {
                item.quantity = firstNonZeroQuantity;
            });
        },


        resetAll() {

            this.input_number_value=null;
            vaah().toastSuccess(['Action Was Successful']);
        },

        async updateCounts() {
            let options = {
                method: 'get',
            };

            await vaah().ajax(
                this.ajax_url+'/get/all-item/count',
                this.updateCountsAfter,
                options
            );
        },

        updateCountsAfter(data , res ){

            this.crud_options.forEach(option => {
                        option.count = data.count[`${option.value}`] || 0;
                    });
        }


    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useSettingStore, import.meta.hot))
}




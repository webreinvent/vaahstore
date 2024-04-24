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
                quantity: 0
            },
            {
                label: 'Stores',
                value: 'Store',
                isChecked: false,
                quantity: 0
            },
            {
                label: 'Vendors',
                value: 'Vendors',
                isChecked: false,
                quantity: 0
            },
            {
                label: 'Vendor Products',
                value: 'VendorsProduct',
                isChecked: false,
                quantity: 0
            },

            {
                label: 'Products',
                value: 'Product',
                isChecked: false,
                quantity: 0
            },
            {
                label: 'Product Variations',
                value: 'ProductVariations',
                isChecked: false,
                quantity: 0
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
                quantity: 0
            },

            {
                label: 'Product Stocks',
                value: 'ProductStock',
                isChecked: false,
                quantity: 0
            },

            {
                label: 'Brands',
                value: 'Brand',
                isChecked: false,
                quantity: 0
            },

            {
                label: 'Warehouses',
                value: 'Warehouses',
                isChecked: false,
                quantity: 0
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
                quantity: 0
            },
            {
                label: 'Addresses',
                value: 'Address',
                isChecked: false,
                quantity: 0
            },
            {
                label: 'Wishlists',
                value: 'Wishlists',
                isChecked: false,
                quantity: 0
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
                quantity: 0
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


        checkAll(item) {
            if (item.label === 'All') {
                if (item.isChecked) {
                    // If "All" checkbox is checked, mark all other checkboxes as checked
                    this.store.crud_options.forEach(option => {
                        option.isChecked = true;
                    });
                } else {
                    // If "All" checkbox is unchecked, mark all other checkboxes as unchecked
                    this.store.crud_options.forEach(option => {
                        option.isChecked = false;
                    });
                }
            }
        },


        //--------------------------------------------------------------------
        async createBulkRecords( data) {


            const selectedItems = this.crud_options.filter(item => item.isChecked);

            console.log(selectedItems)


            // let query = {
            //     params:{
            //         crud: this.selected_crud,
            //         quantity:this.quantity
            //     }
            // };
            //
            // const options = {
            //     params: query,
            //     method: 'post',
            // };
            //
            // await vaah().ajax(
            //     this.ajax_url+'/fill/bulk/method',
            //     this.createBulkRecordsAfter,
            //     options
            // );


        },


        //---------------------------------------------------------------------

        // async createBulkRecordsAfter (data, res) {
        //
        //     if(res.data.success === true)
        //     {
        //         this.quantity = null;
        //         this.selected_crud = null;
        //     }
        //     this.is_button_disabled = false;
        //
        // },
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

            if (!this.input_number_value)
            {
                vaah().toastErrors(['Fill Quantity Atleast in a single column']);
            }

            const columns = document.querySelectorAll('.align-items-center');
            columns.forEach(column => {
                const inputNumber = column.querySelector('input[type="number"]');
            });
        } ,

        resetAll() {

            this.input_number_value=null;
            vaah().toastSuccess(['Action Was Successful']);
        }

    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useSettingStore, import.meta.hot))
}




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
                count: 0 ,
                disabled:false
            },
            {
                label: 'Stores',
                value: 'Store',
                isChecked: false,
                quantity: 0,
                count: 0,
                disabled:false
            },
            {
                label: 'Vendors',
                value: 'Vendors',
                isChecked: false,
                quantity: 0,
                count: 0,
                disabled:false,
                labelsToCheck: ['Stores']
            },
            {
                label: 'Vendor Products',
                value: 'VendorsProduct',
                isChecked: false,
                quantity: 0,
                count: 0,
                disabled:false,
                labelsToCheck: ['Stores' , 'Vendors' , 'Products']
            },

            {
                label: 'Products',
                value: 'Product',
                isChecked: false,
                quantity: 0,
                count: 0 ,
                disabled:false,
                labelsToCheck: ['Stores']


            },
            {
                label: 'Product Variations',
                value: 'ProductVariations',
                isChecked: false,
                quantity: 0,
                count: 0,
                disabled:false,
                labelsToCheck: ['Products' ,'Stores']
            },
            {
                label: 'Product Attributes',
                value: 'ProductAttribute',
                isChecked: false,
                quantity: 0,
                disabled:false,
                labelsToCheck: ['Product Variations' , 'Attributes' , 'Products' , 'Stores']
            },
            {
                label: 'Product Medias',
                value: 'ProductMedia',
                isChecked: false,
                quantity: 0,
                count: 0,
                disabled:false,
                labelsToCheck:['Products' , 'Product Variations' ,'Stores']
            },

            {
                label: 'Product Stocks',
                value: 'ProductStock',
                isChecked: false,
                quantity: 0,
                count: 0,
                disabled:false,
                labelsToCheck: ['Products' ,'Warehouses', 'Product Variations' , 'Vendors' ,'Stores']
            },

            {
                label: 'Brands',
                value: 'Brand',
                isChecked: false,
                quantity: 0,
                count: 0,
                disabled:false
            },

            {
                label: 'Warehouses',
                value: 'Warehouses',
                isChecked: false,
                quantity: 0 ,
                count: 0,
                disabled:false,
                labelsToCheck: ['Vendors']
            },
            {
                label: 'Attributes',
                value: 'Attributes',
                isChecked: false,
                quantity: 0,
                disabled:false
            },

            {
                label: 'Attributes Group',
                value: 'AttributeGroups',
                isChecked: false,
                quantity: 0 ,
                count: 0,
                disabled:false,
                labelsToCheck: ['Attributes']
            },
            {
                label: 'Addresses',
                value: 'Address',
                isChecked: false,
                quantity: 0 ,
                count: 0,
                disabled:false
            },
            {
                label: 'Wishlists',
                value: 'Wishlists',
                isChecked: false,
                quantity: 0 ,
                count: 0,
                disabled:false,
                labelsToCheck: ['Customer']
            },

            {
                label: 'Customer',
                value: 'Customer',
                isChecked: false,
                quantity: 0,
                count: 0,
                disabled:false
            },
            {
                label: 'Customer Group',
                value: 'CustomerGroup',
                isChecked: false,
                quantity: 0 ,
                count: 0,
                disabled:false,
                labelsToCheck: ['Customer']

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


        //------------------------------------------------------------------------


        checkAll(item) {

            if (item.label === 'All') {
                // Toggle isChecked for all options based on the isChecked state of the "All" option
                this.crud_options.forEach(option => {
                    option.isChecked = item.isChecked;
                });
            }

            else if (item.hasOwnProperty('labelsToCheck'))
            {
                this.labelsToCheck(item.labelsToCheck,item.isChecked)
            }
        },

        //--------------------------------------------------------------------

        async labelsToCheck(labelsToCheck, is_checked = false)
        {
            let self = this;
            await(self.crud_options.forEach(option => {

                if (labelsToCheck.includes(option.label)) {

                    const count = option.count;

                    if (count < 1) {
                        option.isChecked = is_checked
                        option.disabled = is_checked

                    }
                }
            }));

            await(self.crud_options.forEach(option => {

                if (option.isChecked) {

                    self.crud_options.forEach(opt => {

                        if (option.hasOwnProperty('labelsToCheck') && option.labelsToCheck.includes(opt.label)) {

                            const count = opt.count;

                            if (!opt.isChecked && count < 1) {
                                opt.isChecked = true
                                opt.disabled = true

                            }
                        }

                    });
                }
            }));

        },


        //--------------------------------------------------------------------
        async createBulkRecords() {


           this.selected_crud = this.crud_options.filter(item => item.isChecked);

            if (this.selected_crud.length === 0) {
                vaah().toastErrors(['Please Choose A crud First']);
                return;
            }

            let errorOptions = [];

            this.crud_options.forEach(option => {
                // Check if the option is checked and has zero quantity
                if (option.isChecked && option.quantity === 0) {
                    // Add the name of the option to the errorOptions array
                    errorOptions.push(option.label);
                }
            });

            if (errorOptions.length > 0) {
                let errorMessage = `Please fill the quantity of the following CRUD options: ${errorOptions.join(', ')}`;

                vaah().toastErrors([errorMessage]);

                return;
            }

            this.is_button_disabled = true ;



            const query = {selectedCrud : this.selected_crud};
            const options = {
                params: query,
                method: 'post',
            };

            console.log(options);

            await vaah().ajax(
                this.ajax_url+'/fill/bulk/method',
                this.createBulkRecordsAfter,
                options
            );


        },


        //---------------------------------------------------------------------

        async createBulkRecordsAfter (data, res) {

            this.crud_options.forEach(option => {
                option.isChecked = false;

                option.quantity = 0;

                option.disabled = false
            });

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

        updateCountsAfter(data , res ) {

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




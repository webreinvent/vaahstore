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
                is_checked: false,
                quantity: null,
                count: 0 ,
                is_disabled:false
            },
            {
                label: 'Stores',
                value: 'Store',
                is_checked: false,
                quantity: null,
                count: 0,
                is_disabled:false
            },

            {
                label: 'Vendors',
                value: 'Vendors',
                is_checked: false,
                quantity: null,
                count: 0,
                is_disabled:false,
                labelsToCheck: ['Stores']
            },

            {
                label: 'Products',
                value: 'Product',
                is_checked: false,
                quantity: null,
                count: 0 ,
                is_disabled:false,
                labelsToCheck: ['Stores']


            },
            {
                label: 'Vendor Products',
                value: 'VendorsProduct',
                is_checked: false,
                quantity: null,
                count: 0,
                is_disabled:false,
                labelsToCheck: ['Stores' , 'Products' , 'Vendors']
            },

            {
                label: 'Attributes',
                value: 'Attributes',
                is_checked: false,
                quantity: null,
                is_disabled:false
            },

            {
                label: 'Product Variations',
                value: 'ProductVariations',
                is_checked: false,
                quantity: null,
                count: 0,
                is_disabled:false,
                labelsToCheck: ['Products' ,'Stores']
            },
            {
                label: 'Product Attributes',
                value: 'ProductAttribute',
                is_checked: false,
                quantity: null,
                is_disabled:false,
                labelsToCheck: ['Product Variations' , 'Attributes' , 'Products' , 'Stores']
            },
            {
                label: 'Product Medias',
                value: 'ProductMedia',
                is_checked: false,
                quantity: null,
                count: 0,
                is_disabled:false,
                labelsToCheck:['Products' , 'Product Variations' ,'Stores']
            },

            {
                label: 'Warehouses',
                value: 'Warehouses',
                is_checked: false,
                quantity: null ,
                count: 0,
                is_disabled:false,
                labelsToCheck: ['Vendors','Stores']
            },

            {
                label: 'Product Stocks',
                value: 'ProductStock',
                is_checked: false,
                quantity: null,
                count: 0,
                is_disabled:false,
                labelsToCheck: ['Products' ,'Warehouses', 'Product Variations' , 'Vendors' ,'Stores']
            },

            {
                label: 'Brands',
                value: 'Brand',
                is_checked: false,
                quantity: null,
                count: 0,
                is_disabled:false
            },

            {
                label: 'Attributes Group',
                value: 'AttributeGroups',
                is_checked: false,
                quantity: null ,
                count: 0,
                is_disabled:false,
                labelsToCheck: ['Attributes']
            },

            {
                label: 'Customer',
                value: 'Customer',
                is_checked: false,
                quantity: null,
                count: null,
                is_disabled:false
            },
            {
                label: 'Customer Group',
                value: 'CustomerGroup',
                is_checked: false,
                quantity: null ,
                count: 0,
                is_disabled:false,
                labelsToCheck: ['Customer']

            },
            {
                label: 'Addresses',
                value: 'Address',
                is_checked: false,
                quantity: null ,
                count: 0,
                is_disabled:false
            },
            {
                label: 'Wishlists',
                value: 'Wishlists',
                is_checked: false,
                quantity: null ,
                count: 0,
                is_disabled:false,
                labelsToCheck: ['Customer']
            },



        ],
        selected_crud:[],
        is_show_delete_modal: false,
        delete_inputs: {
            confirm: null,
            delete_records: null,
        },
        is_delete_confirm: false,
        show_progress_bar: false,
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
                // Toggle is_checked for all options based on the is_checked state of the "All" option
                this.crud_options.forEach(option => {
                    option.is_checked = item.is_checked;
                    option.is_disabled = false;
                });
            }

            else if (item.hasOwnProperty('labelsToCheck'))
            {
                this.labelsToCheck(item.labelsToCheck,item.is_checked)
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
                        option.is_checked = is_checked
                        option.is_disabled = is_checked

                    }
                }
            }));

            await(self.crud_options.forEach(option => {

                if (option.is_checked) {

                    self.crud_options.forEach(opt => {

                        if (option.hasOwnProperty('labelsToCheck') && option.labelsToCheck.includes(opt.label)) {

                            const count = opt.count;

                            if (!opt.is_checked && count < 1) {
                                opt.is_checked = true
                                opt.is_disabled = true

                            }
                        }

                    });
                }
            }));

        },


        //--------------------------------------------------------------------
        async createBulkRecords() {


           this.selected_crud = this.crud_options.filter(item => item.is_checked);

           if (this.selected_crud.length === 0) {
                vaah().toastErrors(['Please Choose A crud First']);
                return;
            }

            let error_options = [];

            let all_option = this.selected_crud.find(option => option.value === "All");

            if (!all_option) {
                // Only check for quantity errors if "All" is not selected
                this.crud_options.forEach(option => {
                    // Check if the option is checked and has zero quantity
                    if (option.is_checked && (option.quantity === null || option.quantity === 0)) {
                        // Add the name of the option to the errorOptions array
                        error_options.push(option.label);
                    }
                });

                if (error_options.length > 0) {
                    let error_message = `Please fill the quantity of the following CRUD options: ${error_options.join(', ')}`;

                    vaah().toastErrors([error_message]);
                    return;
                }
            }

            if(all_option)
            {

                if(all_option.quantity === 0 || all_option.quantity === null)
                {
                    let error_message = 'Please fill the quantity';
                    vaah().toastErrors([error_message]);
                    return;
                }

                this.selected_crud.forEach(option => {


                    if (option.value !== "All") {
                        option.quantity = all_option.quantity;
                    }
                });

                this.is_button_disabled = true ;

                for (let item of this.selected_crud) {
                    await this.createSingleCrudRecord(item);
                }

                this.is_button_disabled = false;
            }


            this.is_button_disabled = true ;

            if(!all_option)
            {
                for (let item of this.selected_crud) {
                    await this.createSingleCrudRecord(item);
                }

            }



            this.is_button_disabled = false;


        },

        //--------------------------------------------------------------------

        async createSingleCrudRecord(item) {
            const query = { selectedCrud: [item] };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url + '/fill/bulk/method',
                (data, res) => this.createSingleCrudRecordAfter(data, res, item),
                options
            );
        },

        //--------------------------------------------------------------------
        async createSingleCrudRecordAfter(data, res, item) {
            if (res.data && res.data.success) {
                this.updateCounts();
                vaah().toastSuccess([data.message]);

                // Uncheck the specific CRUD option and reset its quantity
                let crud_option = this.crud_options.find(option => option.value === item.value);
                if (crud_option) {
                    crud_option.is_checked = false;
                    crud_option.quantity = null;
                    crud_option.is_disabled = false;
                    if(crud_option.label !== 'All')
                    {
                        vaah().toastSuccess([crud_option.label + ' ' + 'records created']);
                    }

                }


            } else {
                vaah().toastErrors(data.errors);
            }

            // Increment the completed CRUD count
            this.completed_crud_count += 1;

            // Check if all CRUD operations are complete
            if (this.completed_crud_count === this.selected_crud.length) {
                this.finalizeBulkOperation();
            }
        },

        //--------------------------------------------------------------------
        finalizeBulkOperation() {
            this.crud_options.forEach(option => {
                option.is_checked = false;
                option.quantity = null;
                option.is_disabled = false;
            });

            this.is_button_disabled = false;
        },
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

        //---------------------------------------------------------------------

        fillAll() {


            // for all items
            const has_quantity = this.crud_options.some(item => item.quantity > 0);

            if (!has_quantity) {
                vaah().toastErrors(['Fill Quantity At least in a single column']);
                return;
            }

            // Get the quantity of the first item with a non-zero quantity
            const first_non_zero_quantity = this.crud_options.find(item => item.quantity > 0).quantity;

            const checked_crud = this.crud_options.filter(item => item.is_checked);

            // Set the quantity of all items to the quantity of the first item with a non-zero quantity

            if(checked_crud.length === 0)

            {
                this.crud_options.forEach(item => {
                    item.quantity = first_non_zero_quantity;
                });
            }

            // If any checked items exist
            if (checked_crud.length > 0) {
                // Get the quantity of the first checked item with a non-zero quantity
                const first_checked_crud_quantity = checked_crud.find(item => item.quantity > 0).quantity;

                // Set the quantity of checked items to the quantity of the first checked item with a non-zero quantity
                checked_crud.forEach(item => {
                    item.quantity = first_checked_crud_quantity;
                });
            }
        },

        //---------------------------------------------------------------------

        resetAll() {

            this.crud_options.forEach(item => {
                item.is_checked = false;
                item.quantity = null;
            });
            vaah().toastSuccess(['Action Was Successful']);
        },


        //---------------------------------------------------------------------

        deleteAll() {

            this.is_show_delete_modal = true ;
        },

        //---------------------------------------------------------------------

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

        //---------------------------------------------------------------------

        updateCountsAfter(data , res ) {

            this.crud_options.forEach(option => {
                        option.count = data.count[`${option.value}`] || 0;
                    });
        },

        showProgress()
        {
            this.show_progress_bar = true;
        },


        //---------------------------------------------------------------------
        confirmReset: function () {
            this.is_delete_confirm = true;
            this.showProgress();

            let params = {
                params: this.delete_inputs,
                method: 'post'
            };

            vaah().ajax(
                this.ajax_url+'/delete/confirm',
                this.afterConfirmReset,
                params
            );
        },
        //---------------------------------------------------------------------
        async afterConfirmReset (data, res) {
            if(data)
            {

                this.is_delete_confirm = false;
                this.is_show_delete_modal = false;
                await this.updateCounts();
                this.delete_inputs.confirm = null;
                this.delete_inputs.delete_records = null;
                this.crud_options.forEach(item => {
                    item.quantity = null;
                    item.is_checked = false;
                });

            }

            else {
                this.is_delete_confirm = false;
                this.is_show_delete_modal = true;
            }


        },


    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useSettingStore, import.meta.hot))
}




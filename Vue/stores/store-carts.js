import {computed, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Cart';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/carts";

let empty_states = {
    query: {
        page: null,
        rows: null,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
        },
    },
    action: {
        type: null,
        items: [],
    }
};

export const useCartStore = defineStore({
    id: 'carts',
    state: () => ({
        base_url: base_url,
        ajax_url: ajax_url,
        model: model_namespace,
        assets_is_fetching: true,
        app: null,
        assets: null,
        rows_per_page: [10,20,30,50,100,500],
        list: null,
        item: null,
        fillable:null,
        empty_query:empty_states.query,
        empty_action:empty_states.action,
        query: vaah().clone(empty_states.query),
        action: vaah().clone(empty_states.action),
        search: {
            delay_time: 600, // time delay in milliseconds
            delay_timer: 0 // time delay in milliseconds
        },
        route: null,
        watch_stopper: null,
        route_prefix: 'carts.',
        view: 'large',
        show_filters: false,
        list_view_width: 12,
        form: {
            type: 'Create',
            action: null,
            is_button_loading: null
        },
        is_list_loading: null,
        count_filters: 0,
        list_selected_menu: [],
        list_bulk_menu: [],
        list_create_menu: [],
        item_menu_list: [],
        item_menu_state: null,
        form_menu_list: [],
        bill_form:null,
        cart_products:null,
        cart_item_at_checkout:[],
        country_suggestions: null,show_new_address_tab:false,editing_address:null,showAll:false,selected_shipping_address:null,
        new_billing_address:null,total_amount_at_detail_page:0,user_billing_address:null,selected_billing_address:null,show_all_billing_address:false,
        show_tab_for_billing:false,
        ordered_product:[],ordered_total_mrp:null,ordered_billing_address:null,ordered_shipping_address:null,
        ordered_at:null,ordered_unique_id:null,
    }),
    getters: {
        displayedAddresses() {
            const sortedAddresses = this.many_adresses.sort((a, b) => {
                if (a.is_default === 1 && b.is_default === 0) return -1;
                if (a.is_default === 0 && b.is_default === 1) return 1;
                return 0;
            });

            const defaultAddress = sortedAddresses.find(address => address.is_default === 1);

            if (defaultAddress) {
                this.selected_shipping_address = defaultAddress;
            }else if (sortedAddresses.length > 0) {
                this.selected_shipping_address = sortedAddresses[0];
            }

            return this.showAll ? sortedAddresses : sortedAddresses.slice(0, 2);
        },
        displayedBillingAddresses() {
            const sortedAddresses = this.user_saved_billing_addresses.sort((a, b) => {
                if (a.is_default === 1 && b.is_default === 0) return -1;
                if (a.is_default === 0 && b.is_default === 1) return 1;
                return 0;
            });

            const defaultAddress = sortedAddresses.find(address => address.is_default === 1);

            if (defaultAddress) {
                this.selected_billing_address = defaultAddress;
            }else if (sortedAddresses.length > 0) {
                this.selected_billing_address = sortedAddresses[0];
            }

            return this.show_all_billing_address ? sortedAddresses : sortedAddresses.slice(0, 2);
        },

        showViewMoreButton() {
            return !this.showAll && this.many_adresses.length >= 3;
        },
        showViewMoreBillingAddressButton() {
            return (
                this.user_saved_billing_addresses &&
                this.user_saved_billing_addresses.length >= 3 &&
                !this.show_all_billing_address
            );
        },

        showAllAddresses() {
            return () => {
                this.showAll = true;
            };
        },
        showAllBillingAddresses() {
            return () => {
                this.show_all_billing_address = true;
            };
        },
        hideAddressTab() {
            return () => {
                this.showAll = !this.showAll;
            };
        },
        hideBillingAddressTab() {
            return () => {
                this.show_all_billing_address = !this.show_all_billing_address;
            };
        },
        remainingAddressCount (){
            return this.many_adresses.length - 2;
        },
        remainingAddressCountBilling (){
            return this.user_saved_billing_addresses.length - 2;
        },
        isSelectedShippingAddress() {
            return (address) => {
                return address === this.selected_shipping_address;
            };
        },
        isSelectedBillingAddress() {
            return (address) => {
                return address === this.selected_billing_address;
            };
        },
        accordionHeader() {
            if (this.isEditing) {
                if (this.editing_address && this.user_saved_billing_addresses.includes(this.editing_address)) {
                    return "Billing Details (Update Address)";
                } else {
                    return "Shipping Details (Update Address)";
                }
            } else {
                if (this.many_adresses.length === 0) {
                    this.show_tab_for_billing = false;
                    return "Shipping Details (New Address)";
                } else if (this.show_tab_for_billing && this.user_saved_billing_addresses.length >= 0) {
                    return "Billing Details (New Address)";
                } else {
                    return "Shipping Details (New Address)";
                }
            }
        }




    },
    actions: {
        setSelectedShippingAddress(address)  {
            this.selected_shipping_address = address;
        },
        setSelectedBillingAddress(address)  {
            this.selected_billing_address = address;
        },
        //---------------------------------------------------------------------
        async onLoad(route)
        {
            /**
             * Set initial routes
             */
            this.route = route;

            /**
             * Update with view and list css column number
             */
            await this.setViewAndWidth(route.name);

            await(this.query = vaah().clone(this.empty_query));

            await this.countFilters(this.query);

            /**
             * Update query state with the query parameters of url
             */
            await this.updateQueryFromUrl(route);
            await this.LoadAssets();
        },
        //---------------------------------------------------------------------
        setRowClass(data){
            return [{ 'bg-gray-200': data.id == this.route.params.id }];
        },
        async LoadAssets(){
            this.assets_is_fetching=true;
            await this.getAssets();
        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {
            switch(route_name)
            {
                case 'carts.index':
                    this.view = 'large';
                    this.list_view_width = 12;
                    break;
                default:
                    this.view = 'small';
                    this.list_view_width = 6;
                    this.show_filters = false;
                    break
            }
        },
        //---------------------------------------------------------------------
        async updateQueryFromUrl(route)
        {
            if(route.query)
            {
                if(Object.keys(route.query).length > 0)
                {
                    for(let key in route.query)
                    {
                        this.query[key] = route.query[key]
                    }
                    if(this.query.rows){
                        this.query.rows = parseInt(this.query.rows);
                    }
                    this.countFilters(route.query);
                }
            }
        },
        //---------------------------------------------------------------------
        watchRoutes(route)
        {
            //watch routes
            this.watch_stopper = watch(route, (newVal,oldVal) =>
                {

                    if(this.watch_stopper && !newVal.name.includes(this.route_prefix)){
                        this.watch_stopper();

                        return false;
                    }

                    this.route = newVal;

                    if(newVal.params.id){
                        this.getItem(newVal.params.id);
                    }

                    this.setViewAndWidth(newVal.name);

                }, { deep: true }
            )
        },
        //---------------------------------------------------------------------
        watchStates()
        {
            watch(this.query.filter, (newVal,oldVal) =>
                {
                    this.delayedSearch();
                },{deep: true}
            )
        },
        //---------------------------------------------------------------------
         watchItem(name)
          {
              if(name && name !== "")
              {
                  this.item.name = vaah().capitalising(name);
                  this.item.slug = vaah().strToSlug(name);
              }else{
                  this.item.slug = name;
              }
          },
        //---------------------------------------------------------------------
        async getAssets() {

            if(this.assets_is_fetching === true){
                this.assets_is_fetching = false;

                await vaah().ajax(
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
                this.countries = data.countries;
                if(!this.query.rows && data.rows)
                {
                    this.query.rows = data.rows;
                    this.empty_query.rows = data.rows;
                }

                if(this.route.params && !this.route.params.id){
                    this.item = vaah().clone(data.empty_item);
                }

            }
        },
        //---------------------------------------------------------------------
        async getList() {
            let options = {
                query: vaah().clone(this.query)
            };
            await vaah().ajax(
                this.ajax_url,
                this.afterGetList,
                options
            );
        },
        //---------------------------------------------------------------------
        afterGetList: function (data, res)
        {
            if(data)
            {
                this.list = data;
                this.product_list = data.products;
            }
        },
        //---------------------------------------------------------------------

        async getItem(id) {
            if(id){
                await vaah().ajax(
                    ajax_url+'/'+id,
                    this.getItemAfter
                );
            }
        },
        //---------------------------------------------------------------------
        async getItemAfter(data, res)
        {
            if(data)
            {
                this.item = data;
                this.cart_products=data.products;
                this.total_amount_at_detail_page=data.total_amount;
            }else{
                this.$router.push({name: 'carts.index',query:this.query});
            }
            await this.getItemMenu();
            await this.getFormMenu();
        },
        //---------------------------------------------------------------------
        isListActionValid()
        {

            if(!this.action.type)
            {
                vaah().toastErrors(['Select an action type']);
                return false;
            }

            if(this.action.items.length < 1)
            {
                vaah().toastErrors(['Select records']);
                return false;
            }

            return true;
        },
        //---------------------------------------------------------------------
        async updateList(type = null){

            if(!type && this.action.type)
            {
                type = this.action.type;
            } else{
                this.action.type = type;
            }

            if(!this.isListActionValid())
            {
                return false;
            }


            let method = 'PUT';

            switch (type)
            {
                case 'delete':
                    method = 'DELETE';
                    break;
            }

            let options = {
                params: this.action,
                method: method,
                show_success: false
            };
            await vaah().ajax(
                this.ajax_url,
                this.updateListAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        async updateListAfter(data, res) {
            if(data)
            {
                this.action = vaah().clone(this.empty_action);
                await this.getList();
            }
        },
        //---------------------------------------------------------------------
        async listAction(type = null){

            if(!type && this.action.type)
            {
                type = this.action.type;
            } else{
                this.action.type = type;
            }

            let url = this.ajax_url+'/action/'+type
            let method = 'PUT';

            switch (type)
            {
                case 'delete':
                    url = this.ajax_url
                    method = 'DELETE';
                    break;
                case 'delete-all':
                    method = 'DELETE';
                    break;
            }

            this.action.filter = this.query.filter;

            let options = {
                params: this.action,
                method: method,
                show_success: false
            };
            await vaah().ajax(
                url,
                this.updateListAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        async itemAction(type, item=null){
            if(!item)
            {
                item = this.item;
            }

            this.form.action = type;

            let ajax_url = this.ajax_url;

            let options = {
                method: 'post',
            };

            /**
             * Learn more about http request methods at
             * https://www.youtube.com/watch?v=tkfVQK6UxDI
             */
            switch (type)
            {
                /**
                 * Create a record, hence method is `POST`
                 * https://docs.vaah.dev/guide/laravel.html#create-one-or-many-records
                 */
                case 'create-and-new':
                case 'create-and-close':
                case 'create-and-clone':
                    options.method = 'POST';
                    options.params = item;
                    break;

                /**
                 * Update a record with many columns, hence method is `PUT`
                 * https://docs.vaah.dev/guide/laravel.html#update-a-record-update-soft-delete-status-change-etc
                 */
                case 'save':
                case 'save-and-close':
                case 'save-and-clone':
                    options.method = 'PUT';
                    options.params = item;
                    ajax_url += '/'+item.id
                    break;
                /**
                 * Delete a record, hence method is `DELETE`
                 * and no need to send entire `item` object
                 * https://docs.vaah.dev/guide/laravel.html#delete-a-record-hard-deleted
                 */
                case 'delete':
                    options.method = 'DELETE';
                    ajax_url += '/'+item.id
                    break;
                /**
                 * Update a record's one column or very few columns,
                 * hence the method is `PATCH`
                 * https://docs.vaah.dev/guide/laravel.html#update-a-record-update-soft-delete-status-change-etc
                 */
                default:
                    options.method = 'PATCH';
                    ajax_url += '/'+item.id+'/action/'+type;
                    break;
            }

            await vaah().ajax(
                ajax_url,
                this.itemActionAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        async itemActionAfter(data, res)
        {
            if(data)
            {
                await this.getList();
                await this.formActionAfter(data);
                this.getItemMenu();
                this.getFormMenu();
            }
        },
        //---------------------------------------------------------------------
        async formActionAfter (data)
        {
            switch (this.form.action)
            {
                case 'create-and-new':
                case 'save-and-new':
                    this.setActiveItemAsEmpty();
                    break;
                case 'create-and-close':
                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'carts.index',query:this.query});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    this.$router.push({name: 'carts.form',query:this.query,params: { id: null }});
                    await this.getFormMenu();
                    break;
                case 'trash':
                case 'restore':
                case 'save':
                    if(this.item && this.item.id){
                        this.item = data;
                    }
                    break;
                case 'delete':
                    this.item = null;
                    this.toList();
                    break;
            }
        },
        //---------------------------------------------------------------------
        async toggleIsActive(item)
        {
            if(item.is_active)
            {
                await this.itemAction('activate', item);
            } else{
                await this.itemAction('deactivate', item);
            }
        },
        //---------------------------------------------------------------------
        async paginate(event) {
            this.query.page = event.page+1;
            await this.getList();
            await this.updateUrlQueryString(this.query);
        },
        //---------------------------------------------------------------------
        async reload()
        {
            await this.getAssets();
            await this.getList();
        },
        //---------------------------------------------------------------------
        async getFormInputs () {
            let params = {
                model_namespace: this.model,
                except: this.assets.fillable.except,
            };

            let url = this.ajax_url+'/fill';

            await vaah().ajax(
                url,
                this.getFormInputsAfter,
            );
        },
        //---------------------------------------------------------------------
        getFormInputsAfter: function (data, res) {
            if(data)
            {
                let self = this;
                Object.keys(data.fill).forEach(function(key) {
                    self.item[key] = data.fill[key];
                });
            }
        },

        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
        onItemSelection(items)
        {
            this.action.items = items;
        },
        //---------------------------------------------------------------------
        setActiveItemAsEmpty()
        {
            this.item = vaah().clone(this.assets.empty_item);
        },
        //---------------------------------------------------------------------
        confirmDelete()
        {
            if(this.action.items.length < 1)
            {
                vaah().toastErrors(['Select a record']);
                return false;
            }
            this.action.type = 'delete';
            vaah().confirmDialogDelete(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmDeleteAll()
        {
            this.action.type = 'delete-all';
            vaah().confirmDialogDelete(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmAction(action_type,action_header)
        {
            this.action.type = action_type;
            vaah().confirmDialog(action_header,'Are you sure you want to do this action?',
                this.listAction,null,'p-button-primary');
        },
        //---------------------------------------------------------------------
        async delayedSearch()
        {
            let self = this;
            this.query.page = 1;
            this.action.items = [];
            clearTimeout(this.search.delay_timer);
            this.search.delay_timer = setTimeout(async function() {
                await self.updateUrlQueryString(self.query);
                await self.getList();
            }, this.search.delay_time);
        },
        //---------------------------------------------------------------------
        async updateUrlQueryString(query)
        {
            //remove reactivity from source object
            query = vaah().clone(query)

            //create query string
            let query_string = qs.stringify(query, {
                skipNulls: true,
            });
            let query_object = qs.parse(query_string);

            if(query_object.filter){
                query_object.filter = vaah().cleanObject(query_object.filter);
            }

            //reset url query string
            await this.$router.replace({query: null});

            //replace url query string
            await this.$router.replace({query: query_object});

            //update applied filters
            this.countFilters(query_object);

        },
        //---------------------------------------------------------------------
        countFilters: function (query)
        {
            this.count_filters = 0;
            if(query && query.filter)
            {
                let filter = vaah().cleanObject(query.filter);
                this.count_filters = Object.keys(filter).length;
            }
        },
        //---------------------------------------------------------------------
        async clearSearch()
        {
            this.query.filter.q = null;
            await this.updateUrlQueryString(this.query);
            await this.getList();
        },
        //---------------------------------------------------------------------
        async resetQuery()
        {
            //reset query strings
            await this.resetQueryString();

            //reload page list
            await this.getList();
        },
        //---------------------------------------------------------------------
        async resetQueryString()
        {
            for(let key in this.query.filter)
            {
                this.query.filter[key] = null;
            }
            await this.updateUrlQueryString(this.query);
        },
        //---------------------------------------------------------------------
        closeForm()
        {
            this.$router.push({name: 'carts.index',query:this.query})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'carts.index',query:this.query})
        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.$router.push({name: 'carts.form',query:this.query})
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            if(!this.item || !this.item.id || this.item.id !== item.id){
                this.item = vaah().clone(item);
            }
            this.$router.push({name: 'carts.view', params:{id:item.id},query:this.query})
        },
        //---------------------------------------------------------------------
        toEdit(item)
        {
            if(!this.item || !this.item.id || this.item.id !== item.id){
                this.item = vaah().clone(item);
            }
            this.$router.push({name: 'carts.form', params:{id:item.id},query:this.query})
        },
        //---------------------------------------------------------------------
        cartDetails(item)
        {
            // if(!this.item || !this.item.id || this.item.id !== item.id){
            //     this.item = vaah().clone(item);
            // }
            // this.item = item.user;

            this.$router.push({name: 'carts.details',params:{id:item.id},query:this.query})
            this.cash_on_delivery=null;
            this.item_billing_address=null;
            // this.bill_form=!this.bill_form;
            this.bill_form=null;

        },
        //---------------------------------------------------------------------
        checkOut(cart)
        {
            // if(!this.item || !this.item.id || this.item.id !== item.id){
            //     this.item = vaah().clone(item);
            // }
            if(this.cart_products.length<1){
                vaah().toastErrors(['No product available in the cart']);
                return;
            }
            this.$router.push({name: 'carts.check_out',params:{id:cart},query:this.query})
            this.item_user_address = vaah().clone(this.assets.item_user_address);
            this.item_new_billing_address = vaah().clone(this.assets.empty_item.item_billing_address);

        },
        //---------------------------------------------------------------------
        isViewLarge()
        {
            return this.view === 'large';
        },
        //---------------------------------------------------------------------
        getActionWidth()
        {
            let width = 100;
            if(!this.isViewLarge())
            {
                width = 80;
            }
            return width+'px';
        },
        //---------------------------------------------------------------------
        getActionLabel()
        {
            let text = null;
            if(this.isViewLarge())
            {
                text = 'Actions';
            }

            return text;
        },
        //---------------------------------------------------------------------
        async getListSelectedMenu()
        {
            this.list_selected_menu = [
                {
                    label: 'Activate',
                    command: async () => {
                        await this.updateList('activate')
                    }
                },
                {
                    label: 'Deactivate',
                    command: async () => {
                        await this.updateList('deactivate')
                    }
                },
                {
                    separator: true
                },
                {
                    label: 'Trash',
                    icon: 'pi pi-times',
                    command: async () => {
                        await this.updateList('trash')
                    }
                },
                {
                    label: 'Restore',
                    icon: 'pi pi-replay',
                    command: async () => {
                        await this.updateList('restore')
                    }
                },
                {
                    label: 'Delete',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.confirmDelete()
                    }
                },
            ]

        },
        //---------------------------------------------------------------------
        getListBulkMenu()
        {
            this.list_bulk_menu = [
                {
                    label: 'Mark all as active',
                    command: async () => {
                        await this.confirmAction('activate-all','Mark all as active');
                    }
                },
                {
                    label: 'Mark all as inactive',
                    command: async () => {
                        await this.confirmAction('deactivate-all','Mark all as inactive');
                    }
                },
                {
                    separator: true
                },
                {
                    label: 'Trash All',
                    icon: 'pi pi-times',
                    command: async () => {
                        await this.confirmAction('trash-all','Trash All');
                    }
                },
                {
                    label: 'Restore All',
                    icon: 'pi pi-replay',
                    command: async () => {
                        await this.confirmAction('restore-all','Restore All');
                    }
                },
                {
                    label: 'Delete All',
                    icon: 'pi pi-trash',
                    command: async () => {
                        this.confirmDeleteAll();
                    }
                },
            ];
        },
        //---------------------------------------------------------------------
        getItemMenu()
        {
            let item_menu = [];

            if(this.item && this.item.deleted_at)
            {

                item_menu.push({
                    label: 'Restore',
                    icon: 'pi pi-refresh',
                    command: () => {
                        this.itemAction('restore');
                    }
                });
            }

            if(this.item && this.item.id && !this.item.deleted_at)
            {
                item_menu.push({
                    label: 'Trash',
                    icon: 'pi pi-times',
                    command: () => {
                        this.itemAction('trash');
                    }
                });
            }

            item_menu.push({
                label: 'Delete',
                icon: 'pi pi-trash',
                command: () => {
                    this.confirmDeleteItem('delete');
                }
            });

            this.item_menu_list = item_menu;
        },
        //---------------------------------------------------------------------
        async getListCreateMenu()
        {
            let form_menu = [];

            form_menu.push(
                {
                    label: 'Create 100 Records',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.listAction('create-100-records');
                    }
                },
                {
                    label: 'Create 1000 Records',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.listAction('create-1000-records');
                    }
                },
                {
                    label: 'Create 5000 Records',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.listAction('create-5000-records');
                    }
                },
                {
                    label: 'Create 10,000 Records',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.listAction('create-10000-records');
                    }
                },

            )

            this.list_create_menu = form_menu;

        },

        //---------------------------------------------------------------------
        confirmDeleteItem()
        {
            this.form.type = 'delete';
            vaah().confirmDialogDelete(this.confirmDeleteItemAfter);
        },
        //---------------------------------------------------------------------
        confirmDeleteItemAfter()
        {
            this.itemAction('delete', this.item);
        },
        //---------------------------------------------------------------------
        async getFormMenu()
        {
            let form_menu = [];

            if(this.item && this.item.id)
            {
                let is_deleted = !!this.item.deleted_at;
                form_menu = [
                    {
                        label: 'Save & Close',
                        icon: 'pi pi-check',
                        command: () => {

                            this.itemAction('save-and-close');
                        }
                    },
                    {
                        label: 'Save & Clone',
                        icon: 'pi pi-copy',
                        command: () => {

                            this.itemAction('save-and-clone');

                        }
                    },
                    {
                        label: is_deleted ? 'Restore': 'Trash',
                        icon: is_deleted ? 'pi pi-refresh': 'pi pi-times',
                        command: () => {
                            this.itemAction(is_deleted ? 'restore': 'trash');
                        }
                    },
                    {
                        label: 'Delete',
                        icon: 'pi pi-trash',
                        command: () => {
                            this.confirmDeleteItem('delete');
                        }
                    },
                ];

            } else{
                form_menu = [
                    {
                        label: 'Create & Close',
                        icon: 'pi pi-check',
                        command: () => {
                            this.itemAction('create-and-close');
                        }
                    },
                    {
                        label: 'Create & Clone',
                        icon: 'pi pi-copy',
                        command: () => {

                            this.itemAction('create-and-clone');

                        }
                    },
                    {
                        label: 'Reset',
                        icon: 'pi pi-refresh',
                        command: () => {
                            this.setActiveItemAsEmpty();
                        }
                    }
                ];
            }

            form_menu.push({
                label: 'Fill',
                icon: 'pi pi-pencil',
                command: () => {
                    this.getFormInputs();
                }
            },)

            this.form_menu_list = form_menu;

        },
        //---------------------------------------------------------------------

        totalPrice(){
            return true;
        },
        //---------------------------------------------------------------------

        calculateTotalAmount (products) {
            return products.reduce((total, product) => {
                return total + this.calculatePrice(product);
            }, 0);
        },
        //---------------------------------------------------------------------

        calculatePrice(product){
            const price = parseFloat(product.pivot.price);
            const quantity = parseInt(product.pivot.quantity);
            let totalPrice = price * quantity;

            if (isNaN(totalPrice)) {
                totalPrice = 0;
            }
            return totalPrice;
        },
        //---------------------------------------------------------------------
        async updateQuantity(pivot_data,event){
            if (event.value===null ) {
                return;
            }
            const query = {
                cart_product_details:pivot_data,
                quantity:event.value
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/update/quantity',
                this.updateQuantityAfter,
                options
            );
        },
        //---------------------------------------------------------------------

         updateQuantityAfter(data,res){
             this.getItem(data.cart.id);
        },

        //---------------------------------------------------------------------

        async deleteCartItem(pivot_data){
            const query = {
                cart_product_details:pivot_data,
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/delete-cart-item',
                this.deleteCartItemAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        deleteCartItemAfter(data,res){
            if (data){
                this.getItem(data.cart.id);
            }
        },
        //---------------------------------------------------------------------

        async getCartItemDetailsAtCheckout(id) {
            if(id){
                await vaah().ajax(
                    ajax_url+'/cart-check-out/'+id,
                    this.getCartItemDetailsAtCheckoutAfter
                );
            }
        },
        //---------------------------------------------------------------------

        async getCartItemDetailsAtCheckoutAfter(data, res) {
            if (data) {
                if (data.product_details.length === 0) {
                    this.$router.push({ name: 'carts.index', query: this.query });
                    return;
                }

                this.cart_item_at_checkout = data.product_details;
                this.item_user = data.user;
                this.total_mrp = data.total_mrp;
                this.item_user_address = vaah().clone(this.assets.item_user_address);
                        this.item_new_billing_address = vaah().clone(this.assets.empty_item.item_billing_address);
                if (data.user_addresses) {
                    this.many_adresses = data.user_addresses;
                    const defaultAddress = data.user_addresses.find(address => address.is_default === 1);
                    this.user_address = defaultAddress || data.user_addresses[Math.floor(Math.random() * data.user_addresses.length)];
                }

                // Assign user billing addresses
                if (data && data.user_billing_addresses) {
                    this.user_saved_billing_addresses = data.user_billing_addresses;
                    const defaultBillingAddress = data.user_billing_addresses.find(address => address.is_default === 1);
                    this.user_billing_address = defaultBillingAddress || data.user_billing_addresses[Math.floor(Math.random() * data.user_billing_addresses.length)];
                }
            }
            else{
                this.$router.push({name: 'carts.index',query:this.query});
            }
            await this.getItemMenu();
            await this.getFormMenu();
        },

        //---------------------------------------------------------------------

        searchCountry(event) {

            this.country_suggestions = this.countries.filter((department) => {
                return department.toLowerCase().startsWith(event.query.toLowerCase());
            });

        },

        //---------------------------------------------------------------------

        toggleNewAddressTab(){
            this.editing_address=null;
            this.isEditing = false;
            this.item_user_address=vaah().clone(this.assets.item_user_address);
            this.show_new_address_tab=true;
            this.show_tab_for_billing = false;
        },
        toggleNewAddressTabForBilling(type) {

            if (this.many_adresses.length === 0) {

                vaah().toastErrors(['First provide shipping details']);
                return;
            }
            this.editing_address = null;
            this.isEditing = false;
            this.item_user_address = vaah().clone(this.assets.item_user_address);
            if (type === 'billing') {
                this.show_tab_for_billing = true;
                this.show_new_address_tab=true;
            }
        },
        async saveCartUserAddress(item,user_id,type){
            const query = {
                user_address:item,
                user_data:user_id,
                type: type
            };
            const options = {
                params: query,
                method: 'post',

            };

            await vaah().ajax(
                this.ajax_url+'/save/cart-user-address',
                this.saveCartUserAddressAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        async saveCartUserAddressAfter(data,res){
            if (data){
               await this.getCartItemDetailsAtCheckout(data.cart_id);
                this.editing_address = null;
                this.show_new_address_tab=false;
                this.show_tab_for_billing=false;
            }
        },

        //---------------------------------------------------------------------

        async removeAddress(address){
           const query = {
               user_address:address,
           };
           const options = {
               params: query,
               method: 'post',
           };

           await vaah().ajax(
               this.ajax_url+'/remove/cart-user-address',
               this.removeAddressAfter,
               options
           );
        },
        async removeAddressAfter(data,res){
            if (data){
                this.getCartItemDetailsAtCheckout(data.cart_id);
                this.selected_shipping_address=null;
                this.selected_billing_address=null;
                this.bill_form=null;
            }
        },
        //---------------------------------------------------------------------


        editAddress(address,itemUser){

            this.new_user_at_shipping = { ...itemUser };
            this.item_user_address = {
                id:address.id,
                country: address.country,
                name: address.name,
                phone: address.phone,
                address_line_1: address.address_line_1,
                pin_code: address.pin_code,
                city: address.city,
                state: address.state
            };

            this.editing_address = address;
            this.show_new_address_tab = true;
            this.isEditing = true;
        },

        //---------------------------------------------------------------------

        async updateAddress(address,user){
            const query = {
                address_detail:address,
                user_detail:user,
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/update/user-shipping-address',
                this.updateAddressAfter,
                options
            );
        },

        //---------------------------------------------------------------------

       async updateAddressAfter(data,res){
            if (data){
                this.editing_address = null;
                await this.getCartItemDetailsAtCheckout(data.cart_id);
                this.show_new_address_tab = false;
            }

        },
        //---------------------------------------------------------------------


        removeTab(index){
            this.show_new_address_tab=false;
            this.show_tab_for_billing=false;
        },

        //---------------------------------------------------------------------

        saveShippingAddress(itemUserAddress, isNewUser, type) {
            if (this.editing_address) {
                itemUserAddress.id = this.editing_address.id;
            }
            this.saveCartUserAddress(itemUserAddress, isNewUser, type);
        },

        //---------------------------------------------------------------------


        handleSameAsShippingChange() {
            if (this.selected_shipping_address !== undefined && this.bill_form !== undefined) {
                if (this.bill_form) {
                    this.item_billing_address = { ...this.selected_shipping_address };
                } else if (Array.isArray(this.bill_form) && this.bill_form.length === 0) {
                    this.item_new_billing_address = vaah().clone(this.assets.empty_item.item_billing_address);
                }
            }
        },




        //---------------------------------------------------------------------


        async placeOrder(orderParams) {
            const query = {
                order_details: orderParams,
            };

            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/place-order',
                this.placeOrderAfter,
                options
            );
        },
        //---------------------------------------------------------------------

        placeOrderAfter(data,res){
            if (data){
                this.orderConfirmation(data.order)
            }
        },
        //---------------------------------------------------------------------
        orderConfirmation(order){
            this.$router.push({name: 'carts.order_details',params:{order_id:order.id},query:this.query})

        },
        //---------------------------------------------------------------------
        async getOrderDetails(order_id){
            const query = {
                order_id:order_id,
            };
            const options = {
                params: query,
                method: 'get',

            };

            await vaah().ajax(
                this.ajax_url+'/get-order-details/'+order_id,
                this.getOrderDetailsAfter,
                options
            );
        },

        getOrderDetailsAfter(data,res){
            if (data){
                this.ordered_product=data.product_details;
                this.ordered_shipping_address=data.order_items_shipping_address;
                this.ordered_billing_address=data.order_items_billing_address;
                this.ordered_total_mrp = data.total_mrp;
                this.ordered_unique_id = data.unique__order_id;
                this.ordered_at = data.ordered_at;
            }
        },

        //---------------------------------------------------------------------


        async addToWishList(item,user){
            const query = {
                item_detail:item,
                user_detail:user,
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/add-to-wishlist',
                this.addToWishListAfter,
                options
            );
        },
        //---------------------------------------------------------------------

        addToWishListAfter(data,res){
           if (data){
               this.getItem(data.cart.id);
           }
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        },
        returnToProduct(){
            this.$router.push({name: 'products.index'});
        }

        //---------------------------------------------------------------------
        //---------------------------------------------------------------------


    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useCartStore, import.meta.hot))
}

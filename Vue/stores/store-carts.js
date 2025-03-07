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
        is_same_as_shipping:null,
        cart_products:null,
        cart_item_at_checkout:[],
        country_suggestions: null,
        show_new_address_tab:false,
        show_all:false,
        show_all_billing_address:false,
        show_tab_for_billing:false,
        editing_address:null,
        selected_shipping_address:null,
        new_billing_address:null,
        total_amount_at_detail_page:0,
        user_billing_address:null,
        selected_billing_address:null,
        ordered_product:[],
        ordered_total_mrp:null,
        ordered_billing_address:null,
        ordered_shipping_address:null,
        ordered_at:null,
        cash_on_delivery:null,
        item_new_billing_address:null,
        item_user_address:null,
        discount_on_order:0,
        order_paid_amount:0,
        order:null,
        cart_uuid:null,
        open_user_dialog:false,
        window_width: 0,
        screen_size: null,
        float_label_variants: 'on',
    }),
    getters: {

        isMobile: (state) => {
            return state.screen_size === 'small';
        },

        getLeftColumnClasses: (state) => {
            let classes = '';

            if(state.isMobile
                && state.view !== 'list'
            ){
                return null;
            }

            if(state.view === 'list')
            {
                return 'lg:w-full';
            }
            if(state.view === 'list-and-item') {
                return 'lg:w-1/2';
            }

            if(state.view === 'list-and-filters') {
                return 'lg:w-2/3';
            }

        },

        getRightColumnClasses: (state) => {
            let classes = '';

            if (state.isMobile
                && state.view !== 'list'
            ) {
                return 'w-full';
            }

            if (state.isMobile
                && (state.view === 'list-and-item'
                    || state.view === 'list-and-filters')
            ) {
                return 'w-full';
            }

            if (state.view === 'list') {
                return null;
            }
            if (state.view === 'list-and-item') {
                return 'lg:w-1/2';
            }

            if (state.view === 'list-and-filters') {
                return 'lg:w-1/3';
            }
        },

        displayedAddresses() {
            const sorted_addresses = this.shipping_addresses.sort((a, b) => b.is_default - a.is_default);
            const default_address = sorted_addresses.find(address => address.is_default === 1);
            this.selected_shipping_address = default_address || sorted_addresses[0];
            return this.show_all ? sorted_addresses : sorted_addresses.slice(0, 2);
        },

        displayedBillingAddresses() {
            const sorted_addresses = this.user_saved_billing_addresses.sort((a, b) => b.is_default - a.is_default);
            const default_address = sorted_addresses.find(address => address.is_default === 1);
            this.selected_billing_address = default_address || sorted_addresses[0];
            return this.show_all_billing_address ? sorted_addresses : sorted_addresses.slice(0, 2);
        },

        showViewMoreButton() {
            return !this.show_all && this.shipping_addresses.length >= 3;
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
                this.show_all = true;
            };
        },
        showAllBillingAddresses() {
            return () => {
                this.show_all_billing_address = true;
            };
        },
        hideAddressTab() {
            return () => {
                this.show_all = !this.show_all;
            };
        },
        hideBillingAddressTab() {
            return () => {
                this.show_all_billing_address = !this.show_all_billing_address;
            };
        },
        remainingAddressCount (){
            return this.shipping_addresses.length - 2;
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
            if (this.is_editing) {
                if (this.editing_address && this.user_saved_billing_addresses.includes(this.editing_address)) {
                    return "Billing Details (Update Address)";
                } else {
                    return "Shipping Details (Update Address)";
                }
            } else {
                if (this.shipping_addresses.length === 0) {
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
            await this.setScreenSize();

            await(this.query = vaah().clone(this.empty_query));

            await this.countFilters(this.query);

            /**
             * Update query state with the query parameters of url
             */
            await this.updateQueryFromUrl(route);
            // await this.loadAssets();
        },
        //---------------------------------------------------------------------
        setRowClass(data){
            return [{ 'bg-gray-200': data.id == this.route.params.id }];
        },
        async loadAssets(){
            this.assets_is_fetching=true;
            await this.getAssets();
        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {

            this.view = 'list';

            if(route_name.includes('carts.view')
            ){
                this.view = 'list-and-item';
            }

            if(route_name.includes('carts.filters')) {
                this.view = 'list-and-filters';
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

            }
        },
        //---------------------------------------------------------------------

        //---------------------------------------------------------------------

        //---------------------------------------------------------------------

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

        //---------------------------------------------------------------------


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

        //---------------------------------------------------------------------
        cartDetails(item)
        {

            this.$router.push({name: 'carts.details',params:{id:item.id},query:this.query})
            this.cash_on_delivery=null;
            this.item_billing_address=null;
            this.selected_shipping_address=null;
            this.selected_billing_address=null;
            // this.bill_form=!this.bill_form;
            this.is_same_as_shipping=null;

        },
        //---------------------------------------------------------------------
        async checkOut(id)
        {
            await this. getCartItemDetailsAtCheckout(id)

            this.item_user_address = null;
            this.item_new_billing_address = null;

        },
        //---------------------------------------------------------------------
        isListView()
        {
            return this.view === 'list';
        },
        //---------------------------------------------------------------------
        getActionWidth()
        {
            let width = 100;
            if(!this.isListView())
            {
                width = 80;
            }
            return width+'px';
        },
        //---------------------------------------------------------------------
        getActionLabel()
        {
            let text = null;
            if(this.isListView())
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

        //---------------------------------------------------------------------


        //---------------------------------------------------------------------

        //---------------------------------------------------------------------

        //---------------------------------------------------------------------

        totalPrice(){
            return true;
        },
        //---------------------------------------------------------------------


        //---------------------------------------------------------------------
        async updateQuantity(pivot_data,event){
            if (event.value===null ) {
                return;
            }
            const cart_id= pivot_data.vh_st_cart_id;
            const query = {
                products: (Array.isArray(pivot_data) ? pivot_data : [pivot_data]).map(item => ({
                    ...item,
                    quantity: event.value,
                })),

            };


            const options = {
                params: query,
                method: 'put',
            };

            await vaah().ajax(
                this.ajax_url+'/'+cart_id+ '/update',

                this.updateQuantityAfter,
                options
            );
        },
        //---------------------------------------------------------------------

         updateQuantityAfter(data,res){
            if (data){
                this.getItem(data.id);
            }
        },

        //---------------------------------------------------------------------

        async deleteCartItem(data,action=null){
            const query = {
                item:data,
                action: 'delete',
            };
            const options = {
                params: query,
                method: 'delete',
            };

            await vaah().ajax(
                this.ajax_url+'/'+ data.vh_st_cart_id+ '/item/' +action,
                this.deleteCartItemAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        deleteCartItemAfter(data,res){
            data ? this.getItem(data.id) : this.$router.push({ name: 'carts.index', query: this.query });
        },
        //---------------------------------------------------------------------

        async getCartItemDetailsAtCheckout(id) {
            if(id){
                await this.loadAssets();
                await vaah().ajax(
                    ajax_url+'/'+ id+'/checkout',
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
                    this.shipping_addresses = data.user_addresses;
                    const defaultAddress = data.user_addresses.find(address => address.is_default === 1);
                    this.user_address = defaultAddress || data.user_addresses[Math.floor(Math.random() * data.user_addresses.length)];
                }

                // Assign user billing addresses
                if (data && data.user_billing_addresses) {
                    this.user_saved_billing_addresses = data.user_billing_addresses;
                    const defaultBillingAddress = data.user_billing_addresses.find(address => address.is_default === 1);
                    this.user_billing_address = defaultBillingAddress || data.user_billing_addresses[Math.floor(Math.random() * data.user_billing_addresses.length)];
                }
                this.$router.push({name: 'carts.check_out',params:{id:this.item.id},query:this.query})
            }


        },

        //---------------------------------------------------------------------

        searchCountry(event) {

            this.country_suggestions = this.countries.filter((department) => {
                return department.toLowerCase().startsWith(event.query.toLowerCase());
            });

        },

        //---------------------------------------------------------------------
        setSelectedShippingAddress(address)  {
            this.selected_shipping_address = address;
        },
        setSelectedBillingAddress(address)  {
            this.selected_billing_address = address;
        },
        //---------------------------------------------------------------------

        toggleNewAddressTab(){
            this.editing_address=null;
            this.is_editing = false;
            this.item_user_address=vaah().clone(this.assets.item_user_address);
            this.show_new_address_tab=true;
            this.show_tab_for_billing = false;
        },
        toggleNewAddressTabForBilling(type) {

            if (this.shipping_addresses.length === 0) {

                vaah().toastErrors(['First provide shipping details']);
                return;
            }
            this.editing_address = null;
            this.is_editing = false;
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
                await this.getCartItemDetailsAtCheckout(data.cart_id);
                this.selected_shipping_address=null;
                this.selected_billing_address=null;
                this.is_same_as_shipping=null;
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
            this.is_editing = true;
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
            if (this.selected_shipping_address !== undefined && this.is_same_as_shipping !== undefined) {
                if (this.is_same_as_shipping) {
                    this.item_billing_address = { ...this.selected_shipping_address };
                } else if (Array.isArray(this.is_same_as_shipping) && this.is_same_as_shipping.length === 0) {
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
                this.orderConfirmation(data.order);
                this.cash_on_delivery=null;
                this.item_billing_address=null;
                this.selected_shipping_address=null;
                this.selected_billing_address=null;
                this.is_same_as_shipping=null;
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
                this.order=data.order;
                this.ordered_shipping_address=data.order_items_shipping_address;
                this.ordered_billing_address=data.order_items_billing_address;
                this.ordered_total_mrp = data.total_mrp;
                this.ordered_at = data.ordered_at;
                this.order_paid_amount=data.order_paid_amount;
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
        //---------------------------------------------------------------------

        formatDate(dateString) {
            const date = new Date(dateString);
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        },
        //---------------------------------------------------------------------

        returnToProduct(){
            this.$router.push({name: 'products.index'});
        },
        //---------------------------------------------------------------------

        async redirectToCart(){
            await this.$router.replace({query: null});
            this.$router.push({name: 'carts.index'})
        },

        //---------------------------------------------------------------------
        toOrderView(order_id){
            this.$router.push({name: 'orders.view',params:{id:order_id}})
        },
        //---------------------------------------------------------------------
        async openUserDialog(item){
            this.open_user_dialog=true;
            this.cart_uuid=item.uuid;

        },
        //---------------------------------------------------------------------

        async addUserToGuestCart(user){
            const query = {
                user:user,
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/'+this.cart_uuid+'/user',
                this.addUserToGuestCartAfter,
                options
            );
        },
        //---------------------------------------------------------------------

        addUserToGuestCartAfter(data,res){
            if (data){
            this.cart_uuid=null;
            this.getList();
            this.getItem(data.id);
            this.open_user_dialog=false;
            }
        },
        //---------------------------------------------------------------------

        onHideUserDialog(){
            this.item.user_object=null;
            this.cart_uuid=null;
            this.open_user_dialog=false;
        },

        //---------------------------------------------------------------------

        setScreenSize()
        {
            if(!window)
            {
                return null;
            }
            this.window_width = window.innerWidth;

            if(this.window_width < 1024)
            {
                this.screen_size = 'small';
            }

            if(this.window_width >= 1024 && this.window_width <= 1280)
            {
                this.screen_size = 'medium';
            }

            if(this.window_width > 1280)
            {
                this.screen_size = 'large';
            }
        },
        //---------------------------------------------------------------------


    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useCartStore, import.meta.hot))
}

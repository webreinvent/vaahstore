import {computed, toRaw, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import {useRootStore} from "./root";

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Payment';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/payments";

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

export const usePaymentStore = defineStore({
    id: 'payments',
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
        route_prefix: 'payments.',
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
        display_response_modal:false,
        filtered_orders: null,
        payment_method_suggestion: null,
        order_filter_key:'',
        selected_order:null,
        payment_methods_chart_options: {},
        payment_methods_chart_series: [],

    }),
    getters: {
        filteredOrders() {
            const key = this.order_filter_key.toLowerCase().trim();
            const route_search_query = this.route.query.filter && this.route.query.filter.order ? decodeURIComponent(this.route.query.filter.order).toLowerCase().trim() : '';

            if (key || route_search_query) {
                return this.item.order_payments.filter(order_payment => {
                    const order_user_name = order_payment.order.user.name.toLowerCase();
                    return (!key || order_user_name.includes(key)) && (!route_search_query || order_user_name.includes(route_search_query));
                });
            } else {
                return this.item.order_payments;
            }
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

            if(state.isMobile
                && state.view !== 'list'
            ){
                return 'w-full';
            }

            if(state.isMobile
                && (state.view === 'list-and-item'
                    || state.view === 'list-and-filters')
            ){
                return 'w-full';
            }

            if(state.view === 'list')
            {
                return null;
            }
            if(state.view === 'list-and-item') {
                return 'lg:w-full';
            }

            if(state.view === 'list-and-filters') {
                return 'lg:w-1/3';
            }


        },

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

            await(this.query = vaah().clone(this.empty_query));

            await this.countFilters(this.query);

            /**
             * Update query state with the query parameters of url
             */
            await this.updateQueryFromUrl(route);
            if (this.query.filter.order) this.getOrdersByName();
        },
        //---------------------------------------------------------------------
        setRowClass(data){
            return [{ 'bg-gray-200': data.id == this.route.params.id }];
        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {
            // switch(route_name)
            // {
            //     case 'payments.index':
            //         this.view = 'large';
            //         this.list_view_width = 12;
            //         break;
            //     case 'payments.view':
            //         this.view = 'small';
            //         this.list_view_width = 5;
            //         break;
            //     default:
            //         this.view = 'small';
            //         this.list_view_width = 6;
            //         this.show_filters = false;
            //         break
            // }
            this.view = 'list';

            if(route_name.includes('payments.view')
                || route_name.includes('payments.form')
            ){
                this.view = 'list-and-item';
            }

            if(route_name.includes('payments.filters')){
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
                this.payment_methods = data.payment_methods;
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
                this.paymentMethodsPieChartData();
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
                this.payment_amount=data.amount;
            }else{
                this.$router.push({name: 'payments.index',query:this.query});
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
                    this.$router.push({name: 'payments.index',query:this.query});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    this.$router.push({name: 'payments.form',query:this.query,params: { id: null }});
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
            this.selected_order=null;
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
            this.$router.push({name: 'payments.index',query:this.query})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'payments.index',query:this.query})

        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            if (this.query.order_id){
                this.query.order_id=null;
            }

            this.$router.push({name: 'payments.form',query:this.query})
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            if(!this.item || !this.item.id || this.item.id !== item.id){
                this.item = vaah().clone(item);
            }
            this.$router.push({name: 'payments.view', params:{id:item.id},query:this.query})
        },
        //---------------------------------------------------------------------
        toEdit(item)
        {
            if(!this.item || !this.item.id || this.item.id !== item.id){
                this.item = vaah().clone(item);
            }
            this.$router.push({name: 'payments.form', params:{id:item.id},query:this.query})
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

        async searchOrders(event){
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/orders',
                this.searchOrdersAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchOrdersAfter(data,res){
            this.filtered_orders=data;
            if (data && this.item.orders) {
                this.filtered_orders = data.filter((item) => {
                    return !this.item.orders.some((activeItem) => {
                        return activeItem.id === item.id;
                    });
                });
            }
        },
        //---------------------------------------------------------------------
        removeOrderDetail(index) {
            this.item.orders.splice(index, 1);
        },
        //---------------------------------------------------------------------
        searchPaymentMethod(event) {

            this.payment_method_suggestion= this.payment_methods.filter((payment_methods) => {
                return payment_methods.name.toLowerCase().startsWith(event.query.toLowerCase());
            });
        },
        //---------------------------------------------------------------------
        setPaymentMethod(event) {
            let payment_method = toRaw(event.value);
            this.item.vh_st_payment_method_id = payment_method.id;
        },

         totalPaidAmount (event, id) {
            const value = parseFloat(event.value) || 0;
             const item_index = this.item.orders.findIndex(order => order.id === id);

             if (item_index !== -1) {
                 this.item.orders[item_index].pay_amount = value;
                 this.item.amount = this.calculateTotalPayment(this.item.orders);
             }

        },
        calculateTotalPayment(items) {
            if (!Array.isArray(items) || items.length === 0) {
                return 0;
            }
            return items.reduce((total, item) => {
                return total + (parseFloat(item.pay_amount) || 0);
            }, 0);
        },

        //---------------------------------------------------------------------
        toOrderDetails(order_id){
            this.$router.push({name: 'orders.view',params:{id:order_id}})

        },
        //---------------------------------------------------------------------
        openPaymentGateResponseModal(item){
            this.display_response_modal=true;
        },
        //---------------------------------------------------------------------
        formatDateTime (datetimeString) {
            if (!datetimeString) return '';

            const datetime = new Date(datetimeString);

            const options = {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };

            return datetime.toLocaleDateString('en-US', options);
        },
        //---------------------------------------------------------------------
        watchOrderAmount(){
            watch(
                () => this.item?.order,
                (newValue, oldValue) => {
                    if (newValue && Array.isArray(newValue)) {
                        this.item.amount = newValue.reduce((total, detail) => {
                            return total + (parseFloat(detail.pay_amount) || 0);
                        }, 0);
                    } else {
                        if (this.item) {
                            this.item.amount = 0;
                        } else {
                            this.item = { amount: 0 };
                        }
                    }
                },
                { deep: true }
            );
        },
        //---------------------------------------------------------------------
        async getOrdersForFilter(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/filter/get/orders',
                this.getOrdersForFilterAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        getOrdersForFilterAfter(data,res) {
            if(data)
            {
                this.filter_order_suggestion = data;
            }
        },
        //---------------------------------------------------------------------

        addOrderFIlter() {
            const unique_order = Array.from(new Set(this.selected_order.map(v => v.id)));
            this.selected_order = unique_order.map(id => this.selected_order.find(v => v.id === id));
            this.query.filter.order = this.selected_order.map(v => v.user_name);
        },
        //---------------------------------------------------------------------

        async getOrdersByName()
        {

            let decodedOrders = this.query.filter.order.map(order => decodeURIComponent(order));

            let query = {
                filter: {
                    order: decodedOrders,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/filter/order-by-user-name',
                this.getOrdersByNameAfterRefresh,
                options
            );


        },

        //---------------------------------------------------------------------
        getOrdersByNameAfterRefresh(data, res) {

            if (data) {
                this.selected_order= data;
            }
        },
        //---------------------------------------------------

        async paymentMethodsPieChartData() {


            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,
            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/payment-methods-pie-chart-data',
                this.paymentMethodsPieChartDataAfter,
                options
            );
        },
        //---------------------------------------------------

        paymentMethodsPieChartDataAfter(data,res){
            this.updateChartSeries(data.chart_series?.payment_methods_pie_chart);

            const updated_pie_chart_options = {
                ...data.chart_options,
                title: {
                    text: 'Payment Methods',
                    offsetY: 12,
                    align: 'left',
                    style: {
                        fontSize: '14px',
                        fontWeight: 'normal',
                        color: '#263238'
                    }
                },
                chart: {
                    toolbar: {
                        show: false,
                    },
                },
                legend: {
                    position: 'right',
                    horizontalAlign: 'center',
                    floating: false,
                    fontSize: '12px',
                    offsetX: -10,
                    offsetY: 35,
                    formatter: function (val, opts) {
                        return `${val} - ${opts.w.globals.series[opts.seriesIndex]}`;
                    },
                    labels: {
                        useSeriesColors: true,
                    },
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: false,
                                name: {
                                    show: true,
                                    
                                    color: '#263238',
                                },
                                value: {
                                    show: true,
                                    fontSize: '16px',
                                    fontWeight: 'bold',
                                    color: '#000',
                                    formatter: function(val) {
                                        return val;
                                    }
                                },
                            },
                        },
                    },
                },
            };

            this.updateChartOptions(updated_pie_chart_options);
        },
        //---------------------------------------------------

        updateChartOptions(newOptions) {
            this.payment_methods_chart_options = newOptions;
        },

        //---------------------------------------------------
        updateChartSeries(newSeries) {
            this.payment_methods_chart_series = [...newSeries];
        },


    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(usePaymentStore, import.meta.hot))
}

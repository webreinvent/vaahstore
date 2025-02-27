import {toRaw, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import dayjs from 'dayjs';
import dayjsPluginUTC from 'dayjs-plugin-utc'
dayjs.extend(dayjsPluginUTC)
import {useRootStore} from "./root";

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Shipment';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/shipments";

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

export const useShipmentStore = defineStore({
    id: 'shipments',
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
        route_prefix: 'shipments.',
        view: 'large',
        show_filters: false,
        list_view_width: 12,
        form: {
            type: 'Create',
            action: null,
            is_button_loading: null
        },
        options:[{ name: 'Yes', value: 1 },
            { name: 'No', value: 0 }],
        is_list_loading: null,
        count_filters: 0,
        list_selected_menu: [],
        list_bulk_menu: [],
        list_create_menu: [],
        item_menu_list: [],
        item_menu_state: null,
        form_menu_list: [],
        order_list_tables:[],
        status_suggestion:null,
        total_quantity_to_be_shipped:null,
        shipped_items_list:[],
        editingRows:[],
        filter_order_suggestion:[],
        selected_orders:null,
        selected_dates:null,
        shipment_by_order_chart_options: {},
        shipment_by_order_chart_series: [],
        shipment_items_by_status_chart_options: {},
        shipment_items_by_status_chart_series: [],
        shipment_by_items_chart_options:{},
        shipment_by_items_chart_series:[],



    }),
    getters: {
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
            if (route.query && route.query.filter && route.query.filter.date) {
                this.selected_dates = route.query.filter.date;
                this.selected_dates = this.selected_dates.join(' - ');
            }
        },
        //---------------------------------------------------------------------
        setRowClass(data){
            return [{ 'bg-gray-200': data.id == this.route.params.id }];
        },
        setShippedRowClass(data){
            return [{ 'bg-gray-200': data.vh_st_shipment_id == this.route.params.id }];
        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {
            // switch(route_name)
            // {
            //     case 'shipments.index':
            //         this.view = 'large';
            //         this.list_view_width = 12;
            //         break;
            //     case 'shipments.form':
            //         this.view = 'small';
            //         this.list_view_width = 5;
            //         break;
            //     case 'shipments.view':
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

            if(route_name.includes('shipments.view')
                || route_name.includes('shipments.form')
            ){
                this.view = 'list-and-item';
            }

            if(route_name.includes('shipments.filters')){
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
                this.status_option = data.taxonomy.status;
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
                this.ordersShipmentByDateRange();
                this.ordersShipmentItemsByDateRange();
                this.shipmentItemsByStatusBarChart();
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


            }else{
                this.$router.push({name: 'shipments.index',query:this.query});
            }

            let uniqueOrders = [];

            for (let item of this.item.shipment_order_items) {
                let order = item.order;
                let existingOrder = uniqueOrders.find(o => o.id === order.id);

                let formattedItem = {
                    ...item,
                    shipped: item.pivot.quantity,
                    // pending: item.quantity - item.pivot.quantity,
                    is_exist:item.is_items_exist_already,
                    pending:item.pivot.pending,
                    name: item.product_variation.name
                };

                if (!existingOrder) {
                    uniqueOrders.push({
                        ...order,
                        user_name: order.user.display_name,
                        items: [formattedItem],
                    });
                } else {
                    existingOrder.items.push(formattedItem);
                }
            }

            this.item.orders = uniqueOrders;

            if (this.item.orders){

                this.order_list_tables = this.item.orders.map(order => ({
                    name: order.user_name,
                    items: order.items
                }));
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
                    this.$router.push({name: 'shipments.index',query:this.query});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    this.$router.push({name: 'shipments.form',query:this.query,params: { id: null }});
                    await this.getFormMenu();
                    break;
                case 'trash':
                case 'restore':
                case 'save':
                    if(this.item && this.item.id){
                        this.getItem(this.item.id);
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
            this.order_list_tables=null;
            this.item.orders=null;
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
            this.selected_orders=null;
            //reset query strings
            await this.resetQueryString();

            //reload page list
            await this.getList();
        },
        //---------------------------------------------------------------------
        async resetQueryString()
        {
            this.selected_dates = null;
            for(let key in this.query.filter)
            {
                this.query.filter[key] = null;
            }
            await this.updateUrlQueryString(this.query);
        },
        //---------------------------------------------------------------------
        closeForm()
        {
            this.$router.push({name: 'shipments.index',query:this.query})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'shipments.index',query:this.query})
            this.order_list_tables=null;
            this.item.orders=null;
        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.$router.push({name: 'shipments.form',query:this.query})
            this.order_list_tables=null;
            this.item.orders=null;
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            if(!this.item || !this.item.id || this.item.id !== item.id){
                this.item = vaah().clone(item);
            }
            this.$router.push({name: 'shipments.view', params:{id:item.id},query:this.query})
        },
        //---------------------------------------------------------------------
        toEdit(item)
        {
            if(!this.item || !this.item.id || this.item.id !== item.id){
                this.item = vaah().clone(item);
            }
            this.$router.push({name: 'shipments.form', params:{id:item.id},query:this.query})
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
        searchOrdersAfter(data,res){
            this.order_suggestion_list=data;
            if (data && this.item.orders) {
                this.order_suggestion_list = data.filter((item) => {
                    return !this.item.orders.some((activeItem) => {
                        return activeItem.id === item.id;
                    });
                });
            }
        },





        //---------------------------------------------------------------------
        async searchStatus(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/status',
                this.searchStatusAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchStatusAfter(data,res) {
            if(data)
            {
                this.status_suggestion = data;
            }
        },
        //---------------------------------------------------------------------
        setStatus(event){
            let status = toRaw(event.value);
            this.item.taxonomy_id_shipment_status = status.id;
        },
        //---------------------------------------------------------------------
        toOrderDetails(order_id){
            this.$router.push({name: 'orders.view',params:{id:order_id}})

        },//---------------------------------------------------------------------
        redirectToVendor(item){
            this.$router.push({name: 'vendors.view',params:{id:item.vh_st_vendor_id}})

        },
        //---------------------------------------------------------------------
        addOrdersToShipment () {
            if(!this.item.orders || (this.item.orders && this.item.orders.length < 1)){
                vaah().toastErrors(['Select an Order']);
                return false;
            }
            this.order_list_tables = this.item.orders.map(order => ({
                name: order.user_name,
                id:order.id,
                items: order.items
            }));
        },
        //---------------------------------------------------------------------

        removeOrders(event) {
            const id_to_remove = event.value.id;
            this.order_list_tables = this.order_list_tables.filter(order => {
                return order.id !== id_to_remove;
            });

        },

        //---------------------------------------------------------------------

        removeOrderDetail(index) {
            this.item.orders.splice(index, 1);
            this.order_list_tables.splice(index, 1);
        },
        //---------------------------------------------------------------------

        updateQuantities (event,index,item,order) {

            const shipped = parseFloat(event.value) || 0;

            if (shipped >= item.pending) {
                item.to_be_shipped = item.pending;
            }



        },
        //---------------------------------------------------------------------

        calculateTotalQuantity(items) {
            if (!Array.isArray(items) || items.length === 0) {
                return 0;
            }
            return items.reduce((total, item) => total + item.quantity, 0);
        },
        //---------------------------------------------------------------------

        calculateTotalShipped(items) {
            if (!Array.isArray(items) || items.length === 0) {
                return 0;
            }
            return items.reduce((acc, item) => acc + parseInt(item.shipped), 0);
        },
        //---------------------------------------------------------------------

        calculateTotalPending(items) {
            if (!Array.isArray(items) || items.length === 0) {
                return 0;
            }
            return items.reduce((acc, item) => acc + parseInt(item.pending), 0);

        },
        //---------------------------------------------------------------------
        async getShipmentItemList(shipeed_item_id){
            if(shipeed_item_id){
                await vaah().ajax(
                    ajax_url+'/'+shipeed_item_id+'/items',
                    this.getShipmentItemListAfter
                );
            }
        },
        //---------------------------------------------------------------------

        getShipmentItemListAfter(data,res){
             if (data){
                 this.shipped_items_list=data.shipment_items;
                 this.total_quantity_to_be_shipped=data.total_quantity_to_be_shipped;
             }
        },

        //---------------------------------------------------------------------

        async saveShippedItemQuanity(type,item=null,params_id=null){

            if(!item)
            {
                item = this.item;
            }
            this.action.type = type;
            let ajax_url = this.ajax_url;

            let options = {
                method: 'post',
            };
            switch (type) {
                /**
                 * Create a record, hence method is `POST`
                 * https://docs.vaah.dev/guide/laravel.html#create-one-or-many-records
                 */
                case 'update-shipped-item-quantity':
                    options.method = 'POST';
                    options.params = {
                        shipment_items: item,
                        available_quantity: this.total_quantity_to_be_shipped,
                        shipment_id: params_id
                    };
                    ajax_url += '/update-shipped-item-quantity'
                    break;
            }
            await vaah().ajax(
                ajax_url,
                this.saveShippedItemQuanityAfter,
                options
            );
        },
        //---------------------------------------------------------------------

        saveShippedItemQuanityAfter(data,res){
             if (data){
                 this.updatePendingQuantity();
                 this.getItem(data.id);
             }
        },

        //---------------------------------------------------------------------

        getMaxValue(currentIndex){
            const available_quantity_to_be_shipped = this.shipped_items_list.reduce((sum, item, index) => {
                if (index !== currentIndex) {
                    return sum + item.quantity;
                }
                return sum;
            }, 0);
            const current_item = this.shipped_items_list[currentIndex];
            const max_value = current_item.total_quantity - available_quantity_to_be_shipped;
            return max_value;
        },

        //---------------------------------------------------------------------

        onRowEditSave(event)  {
            let { newData, index } = event;
            if (newData.quantity > newData.total_quantity) {
                return;
            }
             this.shipped_items_list[index] = newData;

            this.updatePendingQuantity(newData, index);

        },
        //---------------------------------------------------------------------

        updatePendingQuantity(data=null,index=null)  {
             if (data !== null && index !== null) {
                 const total_shipped_quantity_of_others = this.shipped_items_list.reduce((sum, item, idx) => {
                     if (idx !== index) {
                         return sum + item.quantity;
                     }
                     return sum;
                 }, 0);
                 if (data.total_quantity != null && data.quantity != null) {
                     data.pending = data.total_quantity - (total_shipped_quantity_of_others + data.quantity);
                 }

                 this.shipped_items_list.forEach((current_item, idx) => {
                     if (idx !== index) {
                         const total_shipped_quantity_of_others = this.shipped_items_list.reduce((sum, item, otherIdx) => {
                             if (otherIdx !== idx) {
                                 return sum + item.quantity;
                             }
                             return sum;
                         }, 0);


                         if (current_item.total_quantity != null && current_item.quantity != null) {
                             current_item.pending = current_item.total_quantity - (total_shipped_quantity_of_others + current_item.quantity);
                         }
                     }
                 });
             }
            this.total_shipped_quantity = this.shipped_items_list.reduce((sum, item) => {
                return sum + (item.quantity || 0);
            }, 0);
        },
        //---------------------------------------------------------------------

        async getorders(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/filter/search/orders',
                this.getOrdersAfter,
                options
            );
        },
     //---------------------------------------------------------------------
        getOrdersAfter(data,res) {
            if(data)
            {
                this.filter_order_suggestion = data;

            }
        },
        //---------------------------------------------------------------------

        addOrdersFilter() {
             const unique_order = Array.from(new Set(this.selected_orders.map(v => v.user.user_name)));
            this.selected_orders = unique_order.map(name => this.selected_orders.find(v => v.user.user_name === name));
            this.query.filter.order = this.selected_orders.map(v => v.user.user_name);
        },
        //---------------------------------------------------------------------

        setDateRange() {


            if (!this.selected_dates || !Array.isArray(this.selected_dates)) {
                return false;
            }
            const dates = [];
            for (const selected_date of this.selected_dates) {

                if (!selected_date) {
                    continue;
                }
                let search_date = dayjs(selected_date)
                const UTC_date = search_date.format('YYYY-MM-DD');

                if (UTC_date) {
                    dates.push(UTC_date);
                }
                if (dates[0] != null && dates[1] != null) {
                    this.query.filter.date = dates;
                }
            }
        },
        //---------------------------------------------------------------------
        distinctOrdersCount(orders)
        {

            if (!orders || orders.length === 0) {
                return 0;
            }
            const distinct_order_ids = new Set(orders.map(order => order.id));

            return distinct_order_ids.size;

        },
        //---------------------------------------------------------------------
        async ordersShipmentByDateRange() {


            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,
            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/orders-shipments-by-range',
                this.ordersShipmentByDateRangeAfter,
                options
            );
        },

        //---------------------------------------------------------------------
        ordersShipmentByDateRangeAfter(data, res) {
            if (!data || !Array.isArray(data.chart_series)) {
                return;
            }

            const seriesData = data.chart_series.map(series => ({
                name: series.name,
                type: series.name === "Orders In Shipment" ? "line" : "area",
                data: Array.isArray(series.data) ? series.data : [],
            }));

            this.updateChartSeries(seriesData);


            const updatedChartOptions = {
                chart: {
                    type: "line",
                    background: "#fff",
                    toolbar: { show: false }
                },
                stroke: {
                    curve: "smooth",
                    width: [3, 2] // Thicker line for Orders In Shipment, thinner for area chart
                },
                noData: {
                    text: 'Oops! No Data Available',
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: '#FF0000',
                        fontSize: '14px',
                        fontFamily: undefined
                    }
                }, title: {
                    text: 'Order Shipments Trends',
                    align: 'left',
                    offsetY: 12,
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold',
                        color: '#263238'
                    }
                },
                fill: {
                    type: ["solid", "gradient"],
                    gradient: {
                        shade: "light",
                        type: "vertical",
                        shadeIntensity: 0.4,
                        opacityFrom: 0.5,
                        opacityTo: 0.05,
                        stops: [100, 100, 100]
                    }
                },
                colors: ["#008FFB", "#00E396"], // Blue for Orders In Shipment, Green for Quantities Shipped
                xaxis: {
                    type: "datetime",
                    labels: { show: false },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    title: { text: "Shipments Data" }
                },
                tooltip: {
                    enabled: true,
                    shared: true,
                    custom: function({ series, seriesIndex, dataPointIndex, w }) {
                        const date = w.globals.categoryLabels[dataPointIndex] || w.globals.labels[dataPointIndex];
                        const ordersInShipment = series[0][dataPointIndex] ?? 0;
                        const QuantitiesInShipment = series[1][dataPointIndex] ?? 0;
                        return `<div style="background: #fff; padding: 12px; border-radius: 50%;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15); text-align: center;
                min-width: 120px; border: 2px solid rgba(0, 0, 0, 0.05); font-family: Arial, sans-serif;">
                <strong style="color: #333; font-size: 14px; display: block; margin-bottom: 5px;">${date}</strong>
                <div style="font-size: 14px;">
                    <div style="color: #008FFB;">Orders In Shipment: <strong>${ordersInShipment}</strong></div>
                    <div style="color: #00E396;">Quantities Shipped: <strong>${QuantitiesInShipment}</strong></div>
                </div>
            </div>`;
                    }
                },
                legend: {
                    position: "bottom"
                }
            };

            this.updateChartOptions(updatedChartOptions);
        }
        ,
        updateChartOptions(newOptions) {
            this.shipment_by_order_chart_options = newOptions;
        },

        //---------------------------------------------------
        updateChartSeries(newSeries) {

            this.shipment_by_order_chart_series = [...newSeries];
        },



        //---------------------------------------------------------------------
        async ordersShipmentItemsByDateRange() {


            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,
            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/shipment-items-by-range',
                this.ordersShipmentItemsByDateRangeAfter,
                options
            );
        },

        //---------------------------------------------------------------------
        ordersShipmentItemsByDateRangeAfter(data,res){
            const series_data = data.chart_series.map(series => ({
                name: series.name,
                data: Array.isArray(series.data) ? series.data : [],
            }));

            this.updateShipmentItemsChartSeries(series_data);

            const updated_area_chart_options = {
                ...data.chart_options,
                stroke: { curve: 'smooth', width: 4 },
                title: {
                    text: 'Quantity Shipped Over Date Range',
                    align: 'center',
                    offsetY: 12,
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold',
                        color: '#263238'
                    }
                },
                noData: {
                    text: 'Oops! No Data Available',
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: '#FF0000',
                        fontSize: '14px',
                        fontFamily: undefined
                    }
                },
                chart: {

                    toolbar: {
                        show: false,
                    },
                    background: '#ffffff',

                },
                yaxis: {
                    labels: {
                        show: false,
                    },
                },
                xaxis: {
                    labels: {
                        show: false,
                    },
                },
                legend: {
                    show: true,
                    position: 'bottom',
                    horizontalAlign: 'center',
                    floating: false,
                    fontSize: '11px',
                },

                dataLabels: {
                    enabled: false,
                },
                tooltip: {
                    enabled: true,
                    shared: true,
                    style: { fontSize: '14px' },
                },
                grid: {
                    show: false,
                }
            };

            this.updateShipmentItemsChartOptions(updated_area_chart_options);
        },
        //---------------------------------------------------------------------
        updateShipmentItemsChartOptions(newOptions) {
            this.shipment_by_items_chart_options = newOptions;
        },

        //---------------------------------------------------
        updateShipmentItemsChartSeries(newSeries) {
            this.shipment_by_items_chart_series = [...newSeries];
        },

        //---------------------------------------------------

        async shipmentItemsByStatusBarChart() {


            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,

            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/shipment-items-by-status',
                this.shipmentItemsByStatusBarChartAfter,
                options
            );
        },

        //---------------------------------------------------------------------
        shipmentItemsByStatusBarChartAfter(data,res){
            const series_data = [{
                name: 'Item Qty.',
                data: Array.isArray(data.chart_series?.quantity_data) ? data.chart_series?.quantity_data : [],
            }];


            this.updateShipmentItemsByStatusChartSeries(series_data);

            const updated_bar_chart_options = {
                ...data.chart_options, // Merge existing options
                chart: {
                    background: '#ffffff',
                    toolbar: {
                        show: false,
                    },
                },
                noData: {
                    text: 'Oops! No Data Available',
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: '#FF0000',
                        fontSize: '14px',
                        fontFamily: undefined
                    }
                },
                colors: ['#032c57' , '#0047AB','#0056D2','#7ca3f1','#3A7DFF','#81BFFF','#7CA3F1FF'],
                dataLabels: {
                                    enabled: true,
                                    textAnchor: 'start',
                                    style: {
                                        colors: ['#ffffff'],
                                    },
                                    formatter: function (val, opt) {
                                        const category = opt.w.config.xaxis.categories[opt.dataPointIndex] || 'Unknown';
                                        return `${category}: ${val}`;
                                    },
                                    offsetX: 0,
                                    dropShadow: {
                                        enabled: true,
                                    },
                                },
                plotOptions: {
                    bar: {
                        barHeight: '80%',
                        distributed: true,
                        horizontal: true,
                        borderRadius: 4,
                        borderRadiusApplication: 'end',
                        dataLabels: {
                            position: 'bottom',
                        },
                    },
                },
                yaxis: {
                    labels: {
                        show: false,
                    },
                },
                title: {
                    text: 'Shipped Quantities Status',
                    align: 'left',
                    offsetY: 12,
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold',
                        color: '#263238',
                    },
                },

                subtitle: {
                    text: 'Status as DataLabels inside bars',
                    align: 'left',
                },

                markers: {
                    size: 5,
                    strokeColor: '#fff',
                    strokeWidth: 2,
                    hover: {
                        size: 7,
                    },
                },
                tooltip: {
                    theme: 'light',
                },
                legend: {
                    show: true,

                },



            };

            this.updateShipmentItemsByStatusChartOptions(updated_bar_chart_options);
        },
        //---------------------------------------------------

        updateShipmentItemsByStatusChartOptions(newOptions) {
            this.shipment_items_by_status_chart_options = newOptions;
        },

        //---------------------------------------------------
        updateShipmentItemsByStatusChartSeries(newSeries) {
            this.shipment_items_by_status_chart_series = [...newSeries];
        },
        //---------------------------------------------------------------------





    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useShipmentStore, import.meta.hot))
}

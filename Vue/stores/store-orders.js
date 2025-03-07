import {toRaw,watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import dayjs from 'dayjs';
import dayjsPluginUTC from 'dayjs-plugin-utc'

dayjs.extend(dayjsPluginUTC)
import {useRootStore} from "./root";

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Order';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/orders";

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

export const useOrderStore = defineStore({
    id: 'orders',
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
        route_prefix: 'orders.',
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
        suggestion: null,
        status_suggestion: null,
        status_order_items_suggestion: null,
        payment_method_suggestion: null,
        user_suggestion: null,
        type_suggestion: null,
        products: null,
        vendor_suggestion: null,
        customer_group_suggestion: null,
        form_menu_list: [],
        types : null,
        filtered_product_variations : null,
        filtered_venders :null,
        vendors : null,
        filtered_customer_groups : null,
        filtered_users : null,

        order_list_table_with_vendor:null,
        order_name:null,
        count_chart_options: {},
        count_chart_series: [],

        pie_chart_options: {},
        pie_chart_series: [],
        sales_chart_options: {},
        sales_chart_series: [],

        order_payments_income_chart_options: {},
        order_payments_income_chart_series: [],
        order_payments_chart_series:null,
        selection: 'one_month',
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
            this.setViewAndWidth(route.name);

            /**
             * Update query state with the query parameters of url
             */
            this.updateQueryFromUrl(route);
        },

        //---------------------------------------------------------------------



        //---------------------------------------------------------------------


        //---------------------------------------------------------------------


        //---------------------------------------------------------------------


        //---------------------------------------------------------------------





        setViewAndWidth(route_name)
        {
            // switch(route_name)
            // {
            //     case 'orders.index':
            //         this.view = 'large';
            //         this.list_view_width = 12;
            //         break;
            //     case 'orders.view':
            //         this.view = 'small';
            //         this.list_view_width = 5;
            //         break;
            //     default:
            //         this.view = 'small';
            //         this.list_view_width = 6;
            //         break
            // }
            this.view = 'list';

            if(route_name.includes('orders.view')
                || route_name.includes('orders.form')
            ){
                this.view = 'list-and-item';
            }

            if(route_name.includes('orders.filters')){
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

                    if(this.watch_stopper && !newVal.name.startsWith(this.route_prefix)){
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
        // //---------------------------------------------------------------------



        //---------------------------------------------------------------------

        //---------------------------------------------------------------------



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

                if(data.rows)
                {
                    this.query.rows = data.rows;
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
                this.fetchOrdersChartData();
                this.fetchSalesChartData();
                this.fetchOrderPaymentsData();
                this.fetchOrdersCountChartData();
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
                this.$router.push({name: 'orders.index'});
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
                // case 'create-and-new':
                // case 'create-and-close':
                // case 'create-and-clone':
                //     options.method = 'POST';
                //     options.params = item;
                //     break;

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
                case 'order-items':
                    options.method = 'POST';
                    options.params = item;
                    ajax_url += '/items'
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


                case 'save-and-new':

                    this.setActiveItemAsEmpty();
                    break;

                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'orders.index'});
                    break;
                case 'save-and-clone':

                    this.item.id = null;
                    await this.getFormMenu();
                    break;
                case 'trash':
                case 'restore':
                case 'save':
                    this.item = data;
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
            await this.fetchOrdersChartData();

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
            this.$router.push({name: 'orders.index'})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'orders.index'})
        },
        //---------------------------------------------------------------------

        //---------------------------------------------------------------------

        toView(item)
        {
            this.item = vaah().clone(item);
            this.$router.push({name: 'orders.view', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        toEdit(item)
        {
            this.item = item;
            this.$router.push({name: 'orders.form', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        isListView()
        {
            return this.view === 'list';
        },
        //---------------------------------------------------------------------
        getIdWidth()
        {
            let width = 20;

            if(this.list && this.list.total)
            {
                let chrs = this.list.total.toString();
                chrs = chrs.length;
                width = chrs*20;
            }

            return width+'px';
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
                        await this.listAction('activate-all')
                    }
                },
                {
                    label: 'Mark all as inactive',
                    command: async () => {
                        await this.listAction('deactivate-all')
                    }
                },
                {
                    separator: true
                },
                {
                    label: 'Trash All',
                    icon: 'pi pi-times',
                    command: async () => {
                        await this.listAction('trash-all')
                    }
                },
                {
                    label: 'Restore All',
                    icon: 'pi pi-replay',
                    command: async () => {
                        await this.listAction('restore-all')
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
                    label: 'Create 10 Records',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.listAction('create-10-records');
                    }
                },
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

                ];
                if(this.item.deleted_at)
                {
                    form_menu.push({
                        label: 'Restore',
                        icon: 'pi pi-replay',
                        command: () => {
                            this.itemAction('restore');
                            this.item = null;
                            this.toList();
                        }
                    },)
                }
                else {
                    form_menu.push({
                        label: 'Trash',
                        icon: 'pi pi-times',
                        command: () => {
                            this.itemAction('trash');
                            this.item = null;
                            this.toList();
                        }
                    },)
                }

                form_menu.push({
                    label: 'Delete',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.confirmDeleteItem('delete');
                    }
                },)


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

        toOrderDetails(order){
            this.$router.push({name: 'carts.order_details',params:{order_id:order.id},query:this.query})

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

        toPaymentHistory(payment,user){
            const query = {
                filter: {
                    order: [user.name]
                }
            };
            const route = {
                name: 'payments.view',
                params: { id: payment.id },
                query: query
            };
            this.$router.push(route);

        },
        //---------------------------------------------------------------------

        toOrderPayment(order_id) {
            this.$router.push({
                name: 'payments.form',
                query:{ order_id : order_id}
            });
        },
        //---------------------------------------------------

        async openOrderItems(item)
        {
            this.show_orders_panel = true;
            this.order_id=item.id;
            this.order_name=item.user.display_name;

            if (item.id) {
                await vaah().ajax(
                    ajax_url + '/'+ item.id +'/shipment/items',
                    this.openVendorsPanelAfter
                );
            }
        },
        openVendorsPanelAfter(data, res) {

            if (data) {
                this.order_list_table_with_vendor=data;
            } else {
                this.$router.push({name: 'orders.index', query: this.query});
            }
        },
        //---------------------------------------------------

        async fetchOrdersChartData() {
            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,

            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/data',
                this.fetchOrdersChartDataAfter,
                options
            );
        },
        //---------------------------------------------------
        fetchOrdersChartDataAfter(data, res) {

            this.updatePieChartSeries(data.chart_series?.orders_statuses_pie_chart);

            const updated_pie_chart_options = {
                ...data.chart_options,
                title: {
                    text: 'Status Distribution',
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
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontWeight: 'bold',
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

            this.updatePieChartOptions(updated_pie_chart_options);
        },
        //---------------------------------------------------


        async fetchSalesChartData(store=null) {
            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,
                store: store ?? null,
            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/total-sales-data',
                this.fetchSalesChartDataAfter,
                options
            );
        },

        //---------------------------------------------------

        fetchSalesChartDataAfter(data, res) {
            if (!data || !Array.isArray(data.chart_series?.orders_sales_chart_data)) {
                return;
            }

            this.chart_series = data.chart_series;
            this.overall_sales = data.chart_series?.overall_total_sales;
            this.growth_rate = data.chart_series?.growth_rate;
            this.default_currency_symbol = data.chart_series?.currency_symbol?? '';
            // Store original sales values for tooltip
            const originalDataMap = new Map();
            data.chart_series.orders_sales_chart_data.forEach(series => {
                series.data.forEach(point => {
                    originalDataMap.set(point.x, point.y); // Store exact values
                });
            });

            // Smooth the chart data for visualization
            const smoothData = (dataPoints, windowSize = 3) => {
                return dataPoints.map((point, index, arr) => {
                    const start = Math.max(0, index - windowSize + 1);
                    const subset = arr.slice(start, index + 1);
                    const avg = subset.reduce((sum, p) => sum + p.y, 0) / subset.length;
                    return { x: point.x, y: avg, originalY: point.y }; // Store original y-value
                });
            };

            const series_data = data.chart_series.orders_sales_chart_data.map(series => ({
                name: series.name,
                data: smoothData(series.data),  // Apply smoothing
            }));
            this.updateSalesChartSeries(series_data);

            // Detect negative growth
            const isNegativeGrowth = this.growth_rate < 0;
            const chartColor = isNegativeGrowth ? '#4e78e1' : '#4e78e1';

            // Calculate Y-axis min/max for better scaling
            const allValues = series_data.flatMap(s => s.data.map(d => d.y));
            const minY = Math.min(...allValues) * 0.95; // Reduce 5% for better visualization
            const maxY = Math.max(...allValues) * 1.05; // Increase 5% to avoid sharp deflection

            const updated_sales_chart_options = {
                ...data.chart_options,
                stroke: {
                    curve: 'smooth',
                    width: 2,
                },
                title: {
                    text: '',
                },
                fill: {
                    type: ["gradient"],
                    gradient: {
                        shadeIntensity: 0.2,
                        opacityFrom: 0.7,
                        opacityTo: 0.1,
                        stops: [0, 80, 100],
                    }
                },
                colors: [chartColor],
                noData: {
                    text: 'Oops! No Data Available',
                    align: 'center',
                    verticalAlign: 'middle',
                    style: {
                        color: '#476baf',
                        fontSize: '14px',
                    }
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        show: false,
                        format: 'dd MMM',
                    },
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    }
                },
                yaxis: {
                    min: minY,
                    max: maxY,
                    labels: {
                        show: false,
                    },
                    axisBorder: {
                        show: false,
                    },
                },

                tooltip: {
                    x: {
                        format: 'dd MMM yyyy',
                    },
                    y: {
                        formatter: function (value, { dataPointIndex, w }) {
                            const xValue = w.globals.seriesX[0][dataPointIndex]; // Get X (date)
                            const exactSale = originalDataMap.get(xValue) || value; // Get exact Y value

                            const formattedValue = exactSale >= 1000
                                ? (exactSale / 1000).toFixed(2) + 'k'
                                : exactSale;

                            return `${this.default_currency_symbol}${formattedValue}`;
                        }.bind(this)
                    }
                }

                ,

                chart: {
                    toolbar: {
                        show: false,
                    },
                    type: 'area',
                },
                dataLabels: {
                    enabled: false,
                },
                grid: {
                    show: false,
                },
                legend: {
                    show: false
                },
            };

            this.updateSalesChartOptions(updated_sales_chart_options);
        },

        //---------------------------------------------------

        async fetchOrderPaymentsData(store=null) {
            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,
                store: store ?? null,
            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/order-payments-data',
                this.fetchOrderPaymentsDataAfter,
                options
            );
        },
        //---------------------------------------------------

        fetchOrderPaymentsDataAfter(data, res) {
            if (!data || !Array.isArray(data.order_payments_chart_series?.orders_payment_income_chart_data)) {
                return;
            }

            this.order_payments_chart_series = data.order_payments_chart_series;

            this.overall_income = data.order_payments_chart_series?.overall_income;
            this.income_growth_rate = data.order_payments_chart_series?.income_growth_rate;



            const series_data = data.order_payments_chart_series.orders_payment_income_chart_data.map(series => ({
                name: series.name,
                data: Array.isArray(series.data) ? series.data : [],
            }));
            this.updateOrderPaymentsIncomeChartSeries(series_data);


            const updated_order_payments_income_chart_options = {
                ...data.chart_options,
                stroke: {
                    curve: 'smooth',
                    width: 2,
                },
                title: {
                    text: '',

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
                colors: ["#008FFB"],
                fill: {
                    type: ["gradient"],
                    gradient: {
                        shadeIntensity: 0,
                        opacityFrom: 0.5,
                        opacityTo: 0.05,
                        stops: [50 , 80, 100]
                    }
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        show: false,
                    },
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    }
                },
                yaxis: {
                    labels: {
                        show: false,
                    },
                    axisBorder: {
                        show: false,
                    },
                },
                tooltip: {
                    custom: function({ series, seriesIndex, dataPointIndex, w }) {
                        const date = w.globals.categoryLabels[dataPointIndex] || w.globals.labels[dataPointIndex];
                        const value = series[seriesIndex][dataPointIndex] ?? 0;
                        const formattedValue = value >= 1000 ? (value / 1000).toFixed(2) + 'k' : value;

                        return `<div style="
        background: #fff; padding: 12px; border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15); text-align: center;
        min-width: 120px; border: 2px solid rgba(0, 0, 0, 0.05); font-family: Arial, sans-serif;">
        <strong style="color: #333; font-size: 14px; display: block; margin-bottom: 5px;">${date}</strong>
        <div style="font-size: 14px; color: #008FFB;">
            Amount: <strong>${this.default_currency_symbol}${formattedValue}</strong>
        </div>
        </div>`;
                    }.bind(this)
                }
,

                chart: {
                    toolbar: {
                        show: false,
                    },
                },
                toolbar: {
                    show: false,
                    offsetX: 0,
                    offsetY: 40,
                },

                dataLabels: {
                    enabled: false,
                },
                grid: {
                    show: false,
                },
                legend: {
                    show: false
                },

            };

            this.updateOrderPaymentsIncomeChartOptions(updated_order_payments_income_chart_options);
        },

        //---------------------------------------------------

        updateChartOptions(newOptions) {
            this.count_chart_options = newOptions;
        },

        //---------------------------------------------------
        updateChartSeries(newSeries) {
            this.count_chart_series = [...newSeries];
        },
        //---------------------------------------------------

        //---------------------------------------------------
        updatePieChartOptions(newOptions) {
            this.pie_chart_options = newOptions;
        },

        //---------------------------------------------------
        updatePieChartSeries(newSeries) {
            this.pie_chart_series = [...newSeries];
        },

        //---------------------------------------------------

        updateOrderPaymentsChartOptions(options) {
            this.order_payments_chart_options = options;
        },
        //---------------------------------------------------

        updateSalesChartSeries(series) {
            this.sales_chart_series = series;
        },
        //---------------------------------------------------

        updateSalesChartOptions(options) {
            this.sales_chart_options = options;
        },
        //---------------------------------------------------


        updateOrderPaymentsIncomeChartSeries(series) {
            this.order_payments_income_chart_series = series;
        },
        //---------------------------------------------------

        updateOrderPaymentsIncomeChartOptions(options) {
            this.order_payments_income_chart_options = options;
        },

        //---------------------------------------------------


        async fetchOrdersCountChartData() {
            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,

            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/orders-count-by-range',
                this.fetchOrdersCountChartDataAfter,
                options
            );
        },
        //---------------------------------------------------

        fetchOrdersCountChartDataAfter(data,res){
            if (!data || !Array.isArray(data.chart_series?.orders_count_bar_chart)) {
                return;
            }

            const series_data = [
                {
                    name: "Created",
                    type: "line",
                    data: data.chart_series.orders_count_bar_chart.find(s => s.name === "Created")?.data || []
                },
                {
                    name: "Completed",
                    type: "area",
                    data: data.chart_series.orders_count_bar_chart.find(s => s.name === "Completed")?.data || []
                }
            ];
            const totals = {
                total_orders: series_data.find(series => series.name === "Created")?.data.reduce((sum, item) => sum + item.y, 0) || 0,
                completed_orders: series_data.find(series => series.name === "Completed")?.data.reduce((sum, item) => sum + item.y, 0) || 0
            };
            this.updateChartSeries(series_data);

            const updated_area_chart_options = {
                ...data.chart_options,
                chart: {
                    type: "line",
                    toolbar: { show: false },
                },
                stroke: {
                    curve: "smooth",
                    width: [2, 3],
                },
                grid: {
                    show: false,
                },
                fill: {
                    type: ["solid", "gradient"],
                    gradient: {
                        shade: "light",
                        shadeIntensity: 0.4,
                        inverseColors: false,
                        opacityFrom: 0.5,  // Increased for more visibility
                        opacityTo: 0.15,   // Adjusted to ensure shadow persists at the end
                        stops: [20, 100, 100],
                        type: "vertical"
                    }
                },
                colors: ["#008FFB", "#00E396"], // Created (Blue - Line), Completed (Green - Area)
                xaxis: {
                    type: "datetime",
                    labels: { show: false },
                    axisBorder: { show: false },
                    axisTicks: {
                        show: false,
                    }
                },
                yaxis: {
                    labels: { show: false },
                    axisBorder: { show: false },
                },
                tooltip: {
                    custom: function({ series, seriesIndex, dataPointIndex, w }) {
                        const date = w.globals.categoryLabels[dataPointIndex] || w.globals.labels[dataPointIndex];
                        const createdCount = series[0][dataPointIndex] ?? 0;
                        const completedCount = series[1][dataPointIndex] ?? 0;
                        return `<div style="background: #fff; padding: 12px; border-radius: 50%;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15); text-align: center;
                min-width: 120px; border: 2px solid rgba(0, 0, 0, 0.05); font-family: Arial, sans-serif;">
                <strong style="color: #333; font-size: 14px; display: block; margin-bottom: 5px;">${date}</strong>
                <div style="font-size: 14px;">
                    <div style="color: #008FFB;">Created: <strong>${createdCount}</strong></div>
                    <div style="color: #00E396;">Completed: <strong>${completedCount}</strong></div>
                </div>
            </div>`;
                    }
                },
                legend: {
                    show: true,
                    position: "bottom",
                    horizontalAlign: "left",
                    fontSize: "14px",
                    labels: { colors: "#000" },
                    itemMargin: { horizontal: 10, vertical: 5 },
                    formatter: function(seriesName) {
                        if (seriesName === "Created") {
                            return `${seriesName}: ${totals.total_orders}`;
                        } else if (seriesName === "Completed") {
                            return `${seriesName}: ${totals.completed_orders}`;
                        }
                        return seriesName;
                    }
                }
            };

            this.updateChartOptions(updated_area_chart_options);

        },



    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useOrderStore, import.meta.hot))
}

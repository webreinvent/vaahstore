import {watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'

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

        order_list : [
            { "name": "Order  1", "id": 1, "amount": 22, "deleted_at": null ,
                "items": [{
                    id: 101, name: 'Item 1', quantity: 2, order: {
                        name: 'Order 1',
                        id: 1,
                    },
                    shipped:0,
                    pending:2,
                    shippedQuantity:0
                },{
                    id: 102, name: 'Item 2', quantity: 2, order: {
                        name: 'Order 1',
                        id: 1,
                    },
                    shipped:0,
                    pending:2,
                    shippedQuantity:0
                }] },
            { "name": "Order  2", "id": 2, "amount": 22, "deleted_at": null,"items": [{
                    id: 1000, name: 'Item 3', quantity: 2, order: {
                        name: 'Order 2',
                        id: 2,
                    },
                    shipped:1,
                    pending:1,
                    shippedQuantity:0
                } ]},

        ],
        order_list1 : [

            {
                id: 1000, name: 'Item 1', quantity: 2, order: {
                    name: 'Order 1',
                    image: 'ionibowcher.png'
                },
                vendor:{
                    id:106,
                    name:'vendor 1',
                },
                shipped:2,
                pending:0
            },
            {
                id: 1000,
                name: 'Item 2',
                quantity: 2,
                order: {
                    name: 'Order 1',
                    image: 'ionibowcher.png'
                },
                vendor:{
                    id:106,
                    name:'vendor 1',
                },
                shipped:2,
                pending:0
            },{
                id: 1000,
                name: 'Item 3',
                quantity: 1,
                order: {
                    name: 'Order 2',
                    image: 'ionibowcher.png'
                },
                vendor:{
                    id:107,
                    name:'vendor 2',
                },
                shipped:1,
                pending:0
            },

        ],


    }),
    getters: {

    },
    actions: {
         addOrdersToShipment () {
            this.order_list_tables = this.item.orders.map(order => ({
                name: order.user_name,
                items: order.items
            }));
        },

        removeOrderDetail(index) {
            this.item.orders.splice(index, 1);
            this.order_list_tables.splice(index, 1);
        },
         updateQuantities (event,index,item,order) {


            const shipped = parseFloat(event.value) || 0;

            if (shipped >= item.pending) {
                item.to_be_shipped = item.pending;
            }



        },
        calculateTotalToBeShipped(order){
            if (!order || !Array.isArray(order.items) || order.items.length === 0) {
                return 0;
            }
            return order.items.reduce((total, item) => total + (item.to_be_shipped || 0), 0);


        },
        calculateTotalQuantity(items) {
            if (!Array.isArray(items) || items.length === 0) {
                return 0;
            }
            return items.reduce((total, item) => total + item.quantity, 0);
        },
        calculateTotalShipped(items) {
            if (!Array.isArray(items) || items.length === 0) {
                return 0;
            }
            return items.reduce((total, item) => total + item.shipped, 0);
        },calculateTotalPending(items) {
            if (!Array.isArray(items) || items.length === 0) {
                return 0;
            }
            return items.reduce((total, item) => total + item.pending, 0);
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
        },
        //---------------------------------------------------------------------
        setRowClass(data){
            return [{ 'bg-gray-200': data.id == this.route.params.id }];
        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {
            switch(route_name)
            {
                case 'shipments.index':
                    this.view = 'large';
                    this.list_view_width = 12;
                    break;
                case 'shipments.form':
                    this.view = 'small';
                    this.list_view_width = 5;
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
        async openOrdersPanel(item)
        {
            this.show_orders_panel = true;
            // this.product_id=item.id;
            // this.product_name=item.name;
            // if (item.id) {
            //     await vaah().ajax(
            //         ajax_url + '/get-vendors-list'+'/' + item.id,
            //         this.openVendorsPanelAfter
            //     );
            // }
        },
        async getDomainFilterMenu() {

            this.shipping_status_menu = [
                {
                    label: 'Pending',
                    command: async () => {
                        await this.updateDomainFilter('Pending')
                    }
                },
                {
                    label: 'Picked',
                    command: async () => {
                        await this.updateDomainFilter('Picked')
                    }
                },
                {
                    label: 'In Transit',
                    command: async () => {
                        await this.updateDomainFilter('InTransit')
                    }
                },
                {
                    label: 'Delivered',
                    command: async () => {
                        await this.updateDomainFilter('Delivered')
                    }
                }
            ]

        },
        async updateDomainFilter(name)
        {
            this.shipping_status = name;
        },
        // searchOrders(event) {
        //     const query = event.query.toLowerCase();
        //     this.order_suggestion_list = this.order_list.filter(item => {
        //         return item.name.toLowerCase().includes(query);
        //     });
        // },
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


        searchStatus(event) {
            const query = event.query.toLowerCase();
            this.status_suggestion_list = this.shipment_status.filter(item => {
                return item.name.toLowerCase().includes(query);
            });
        },

        async addOrders() {
            const unique_orders = [];
            const check_names = new Set();

            for (const order of this.item.orders) {
                if (!check_names.has(order.name)) {
                    unique_orders.push(order);
                    check_names.add(order.name);
                }
                else {
                    this.item.orders = unique_orders;
                    vaah().toastErrors(['This Currency is already added']);
                    return false;
                }
            }
            if(unique_orders.length == 0)
            {
                await this.getItem(this.route.params.id);
                return false;
            }
            this.item.orders = unique_orders;
            if ( this.route.name === 'view' && this.item.id ) {
                await this.itemAction('save');
            }
        },
        toViewVendor(product)
        {
            const query = {
                page: 1,
                rows: 20,
                filter: {
                    q: product.vendor.id
                }
            };
            const route = {
                name: 'vendors.index',
                query: query
            };
            this.$router.push(route);
        },
        //---------------------------------------------------------------------
    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useShipmentStore, import.meta.hot))
}

import {watch,toRaw} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import dayjs from 'dayjs';
import dayjsPluginUTC from 'dayjs-plugin-utc'

dayjs.extend(dayjsPluginUTC)

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\ProductVariation';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/productvariations";

let empty_states = {
    query: {
        page: null,
        rows: null,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
            date: null,
            default: null,
            product:null,
            quantity:null,
            product_variation_status:null,
            stock_status:null,
            min_quantity:null,
            max_quantity:null,
        },
    },
    action: {
        type: null,
        items: [],
    }
};

export const useProductVariationStore = defineStore({
    id: 'productvariations',
    state: () => ({
        base_url: base_url,
        ajax_url: ajax_url,
        model: model_namespace,
        assets_is_fetching: true,
        app: null,
        assets: null,
        rows_per_page: [10, 20, 30, 50, 100, 500],
        list: null,
        item: null,
        fillable: null,
        empty_query: empty_states.query,
        empty_action: empty_states.action,
        query: vaah().clone(empty_states.query),
        action: vaah().clone(empty_states.action),
        search: {
            delay_time: 600, // time delay in milliseconds
            delay_timer: 0   // time delay in milliseconds
        },
        route: null,
        watch_stopper: null,
        route_prefix: 'productvariations.',
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
        suggestion: null,
        active_products: null,
        status_suggestion: null,
        product_suggestion: null,
        selected_dates: [],
        date_null: null,
        prev_list: [],
        current_list: [],
        filtered_products:null,
        selected_product : null,
        meta_dialog:false,
        quantity:[],
        min_quantity : 0,
        max_quantity : 0,
        product_variation_status:null,
        first_element: null,
        products_suggestion:null,
        products:null,
        fetched_product_id: null,
        default_variation_message:null,
        stock_options :[

            {
                label: 'In Stock',
                value: 'in_stock'
            },
            {
                label: 'Out of Stock',
                value: 'out_of_stock'
            },
            {
                label: 'Low Stock',
                value: 'low_stock'
            },
        ],
        product_detail:null,
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
                return 'lg:w-1/2';
            }

            if(state.view === 'list-and-filters') {
                return 'lg:w-1/3';
            }

        },
    },
    actions: {
        async fetchDataBasedOnProductId(selected_product_id) {
                this.fetched_product_id=selected_product_id;
                if (selected_product_id && selected_product_id.id) {
                this.item.product=this.fetched_product_id;
                this.item.vh_st_product_id = selected_product_id.id;

            }

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
            this.setViewAndWidth(route.name);
            await this.setScreenSize();
            this.first_element = ((this.query.page - 1) * this.query.rows);

            /**
             * Update query state with the query parameters of url
             */
            this.updateQueryFromUrl(route);
            await this.updateUrlQueryString(this.query);


            if (route.query && route.query.filter && route.query.filter.date) {
                this.selected_dates = route.query.filter.date;
                this.selected_dates = this.selected_dates.join(' - ');
            }


        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {

            this.view = 'list';

            if(route_name.includes('productvariations.view')
                || route_name.includes('productvariations.form')
            ){
                this.view = 'list-and-item';
            }

            if(route_name.includes('productvariations.filters')){
                this.view = 'list-and-filters';
            }
        },

        //---------------------------------------------------------------------
        searchStatus(event) {

            this.status_suggestion= this.status.filter((status) => {
                return status.name.toLowerCase().startsWith(event.query.toLowerCase());
            });
        },

        //---------------------------------------------------------------------
        async searchProduct(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/product',
                this.searchProductAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchProductAfter(data,res){
            if(data){
                this.products_suggestion = data;
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
        //---------------------------------------------------------------------
        watchItem()
        {
            if(this.item){
                watch(() => [this.item.name, this.item.in_stock],
                    ([newName, newInStock], [oldName, oldInStock]) =>
                    {
                        if(newName && newName !== "")
                        {
                            this.item.name = newName;
                            this.item.slug = vaah().strToSlug(newName);
                        }
                        if(newName == "")
                        {
                            this.item.slug="";
                        }

                    },{deep: true}
                )
            }
            if (this.form_menu_list.length === 0) {
                this.getFormMenu();
            }
        },

        //---------------------------------------------------------------------
        setProduct(event){
            let product = toRaw(event.value);
            if(product && product.id)
            {
                this.item.vh_st_product_id = product.id;
            }

        },
        //---------------------------------------------------------------------

        setProductFilter(event){

            this.query.filter.product = this.selected_product.slug;

        },

        //---------------------------------------------------------------------

        setStatus(event){
            let status = toRaw(event.value);
            this.item.taxonomy_id_variation_status = status.id;
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
                this.status = data.taxonomy.status;
                this.product_variation_status = data.taxonomy.vendor_status;
                this.active_products = data.active_products;
                this.products = data.products;
                if(data.rows)
                {

                    data.rows = this.query.rows;
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
            this.default_variation_message = (res && res.data && res.data.message)
                ? 'There is no default product variation. Mark a product variation as default.'
                : null;


            if (res?.data?.active_cart_user) {
                const { active_cart_user: { cart_records, display_name, vh_st_cart_id } } = res.data;
                this.add_to_cart = false;
                this.show_cart_msg = true;
                this.active_user = res.data.active_cart_user;
                this.total_cart_product = cart_records;
                this.active_cart_user_name = display_name;
                this.cart_id = vh_st_cart_id;
            } else {
                this.show_cart_msg = false;
            }
            if(data)
            {
                this.list = data;
                this.first_element = this.query.rows * (this.query.page - 1);
                this.query.rows=data.per_page;
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
                this.$router.push({name: 'productvariations.index'});
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
                case 'save-and-new':
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
                this.item = data;
                this.prev_list =this.list.data;
                await this.getList();
                await this.formActionAfter(data);
                this.getItemMenu();
                this.getFormMenu();
            }
            this.current_list=this.list.data
            this.compareList(this.prev_list,this.current_list)

        },
        compareList(prev_list, current_list) {

            const removed_Items = prev_list.filter(previous_item =>
                !current_list.some(current_item => current_item.id === previous_item.id));

            const removed_item_present_in_current_list = removed_Items.some(removed_item =>
                current_list.some(current_item => current_item.id === removed_item.id)
            );
            if (!removed_item_present_in_current_list) {
                this.action.items = this.action.items.filter(item =>
                    !removed_Items.some(removed_item => removed_item.id === item.id));
            }
        },
        //---------------------------------------------------------------------
        async formActionAfter (data)
        {
            switch (this.form.action)
            {
                case 'create-and-new':
                    this.setActiveItemAsEmpty();
                    break;
                case 'create-and-close':
                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'productvariations.index'});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    this.route.params.id = null;
                    this.$router.push({name: 'productvariations.form'});
                    await this.getFormMenu();
                    break;
                case 'save-and-new':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'productvariations.form'});
                    vaah().toastSuccess(['Action Was Successful']);
                    break;
                case 'trash':
                    vaah().toastSuccess(['Action was successful']);
                    break;
                case 'restore':
                    vaah().toastSuccess(['Action was successful']);
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
                await this.itemAction('deactivate', item);
            } else{
                await this.itemAction('activate', item);
            }
        },
        //---------------------------------------------------------------------
        async paginate(event) {
            this.query.page = event.page+1;
            this.query.rows = event.rows;
            this.first_element = this.query.rows * (this.query.page - 1);
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
            vaah().confirmDialogDeleteAll(this.listAction);
        },

        //---------------------------------------------------------------------
        confirmActivateAll()
        {
            this.action.type = 'activate-all';
            vaah().confirmDialogActivateAll(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmDeActivateAll()
        {
            this.action.type = 'deactivate-all';
            vaah().confirmDialogDeactivateAll(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmTrashAll()
        {
            this.action.type = 'trash-all';
            vaah().confirmDialogTrashAll(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmRestoreAll()
        {
            this.action.type = 'restore-all';
            vaah().confirmDialogRestoreAll(this.listAction);
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
            await this.resetQueryString();
            this.selected_dates=[];
            this.selected_products = null;
            this.date_null= this.route.query && this.route.query.filter ? this.route.query.filter : 0;

            this.quantity =[];


            vaah().toastSuccess(['Action was successful']);
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
            this.$router.push({name: 'productvariations.index'})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.fetched_product_id=null
            this.$router.push({name: 'productvariations.index'})
        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.$router.push({name: 'productvariations.form'})
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            this.item = vaah().clone(item);
            this.$router.push({name: 'productvariations.view', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        toEdit(item)
        {
            this.item = item;
            this.$router.push({name: 'productvariations.form', params:{id:item.id}})
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
        getInStockWidth()
        {
            let width = 120 + 'px';

            if(!this.isListView())
            {
                width = 150 + 'px';
            }

            return width;
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

                        await this.updateList('restore');
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

                        this.confirmActivateAll();
                    }
                },
                {
                    label: 'Mark all as inactive',
                    command: async () => {

                        this.confirmDeActivateAll();
                    }
                },
                {
                    separator: true
                },
                {
                    label: 'Trash All',
                    icon: 'pi pi-times',
                    command: async () => {
                        this.confirmTrashAll();
                    }
                },
                {
                    label: 'Restore All',
                    icon: 'pi pi-replay',
                    command: async () => {
                        this.confirmRestoreAll();
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
        checkPrice(event)
        {
            this.item.price = event.value;
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
                    {
                        label: 'Save & New',
                        icon: 'pi pi-copy',
                        command: () => {

                            this.itemAction('save-and-new');

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
        reloadPage()
        {
            this.getList()

            vaah().toastSuccess(['Page Reloaded']);

        },
        //---------------------------------------------------------------------
        setDateRange()
        {

            if(!this.selected_dates){
                return false;
            }
            const dates =[];
            for (const selected_date of this.selected_dates) {
                if(!selected_date){
                    continue ;
                }
                let search_date = dayjs(selected_date)
                var UTC_date = search_date.format('YYYY-MM-DD');
                if(UTC_date){
                    dates.push(UTC_date);
                }
                if(dates[0] != null && dates[1] !=null)
                {
                    this.query.filter.date = dates;
                }
            }

        },
        //---------------------------------------------------------------------
        shortCharacter  (name)  {
            if (name && name.length > 20) {
                return name.substring(0, 20) + '...';
            }
            return name;
        },

        //---------------------------------------------------------------------
        openMetaModal  ()  {

            this.meta_dialog=true;

        },

        //---------------------------------------------------------------------

        quantityFilterMin(event){

            this.query.filter.min_quantity = event.value;

            },

        //---------------------------------------------------------------------

        quantityFilterMax(event){

            this.query.filter.max_quantity = event.value;

        },

        //---------------------------------------------------------------------

        addSelectedProduct () {

            const unique_products = [];
            const check_names = new Set();

            for (const products of this.selected_products) {
                if (!check_names.has(products.name)) {
                    unique_products.push(products);
                    check_names.add(products.name);
                }
            }
            const products_slug = unique_products.map(product => product.slug);
            this.selected_products = unique_products;
            this.query.filter.products = products_slug;
        },
        //-----------------------------------------------------

        async setProductInFilter()
        {
            let query = {
                filter: {
                    product: this.query.filter.products,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/route-query-products',
                this.setProductInFilterAfter,
                options
            );


        },

        //---------------------------------------------------------------------

        setProductInFilterAfter(data, res) {

            if (data) {
                this.selected_products = data;
            }
        },
        //---------------------------------------------------------------------

        async disableActiveCart(){
            const query = {
                user_info: this.active_user
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/disable/active-cart',
                this.disableUserCartAfter,
                options
            );
        },
        //---------------------------------------------------------------------

        disableUserCartAfter(){
            this.show_cart_msg=false;this.active_user=null;
        },
        //---------------------------------------------------------------------

        async addToCart(item){
            this.product_detail=item;
            if (!this.show_cart_msg){
                this.add_to_cart=true;
            }
            if (this.show_cart_msg && this.active_user !== null) {
                await this.addVariationToCart(item);
            }

        },
        //---------------------------------------------------------------------

        showMsg(){
            this.add_to_cart = false;
            this.show_cart_msg=true;
        },
        //---------------------------------------------------------------------

        async addVariationToCart(product_variation){

            const user_info = this.item.user ? this.item.user : this.active_user;

            const query = {
                user: user_info,
                product_variation: product_variation
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/cart/generate',
                this.addVariationToCartAfter,
                options
            );
        },
        //---------------------------------------------------------------------

        addVariationToCartAfter(data,res){
            if (data){

                this.getList();
            }
            this.add_to_cart = false;
            this.item.user=null;
            this.product_detail=null;
        },
        //---------------------------------------------------------------------

        async searchUser(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/user',
                this.searchUsersAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        searchUsersAfter(data,res) {
            if(data)
            {
                this.user_suggestions = data;


            }
        },
        //---------------------------------------------------------------------

        setUser(event) {
            let user = toRaw(event.value);
            if (user && user.id) {
                this.item.vh_user_id = user.id;
            }

        },
        //---------------------------------------------------------------------

        viewCart(id){
            this.$router.push({name: 'carts.details',params:{id:id},query:this.query})
        },
        //---------------------------------------------------------------------
        onHideCartDialog(){
            this.product_detail=null;
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

    },

    //---------------------------------------------------------------------

    //---------------------------------------------------------------------

    //---------------------------------------------------------------------



});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useProductVariationStore, import.meta.hot))
}

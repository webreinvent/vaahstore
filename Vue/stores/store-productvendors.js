import {toRaw, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import moment from "moment-timezone/moment-timezone-utils";

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\ProductVendor';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/productvendors";

let empty_states = {
    query: {
        page: 1,
        rows: 20,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
            product_vendor_status:null,
            selected_dates:null,
            status:null,
        },
    },
    action: {
        type: null,
        items: [],
    }
};

export const useProductVendorStore = defineStore({
    id: 'productvendors',
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
        options_can_update:['off','no'],
        suggestion: null,
        product: null,
        active_vendors: null,
        fillable:null,
        product_suggestion: null,
        vendor_suggestion: null,
        added_by_suggestion: null,
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
        route_prefix: 'productvendors.',
        view: 'large',
        show_filters: false,
        list_view_width: 12,
        form: {
            type: 'Create',
            action: null,
            is_button_loading: null
        },
        is_list_loading: null,
        active_users: null,
        count_filters: 0,
        list_selected_menu: [],
        list_bulk_menu: [],
        list_create_menu: [],
        item_menu_list: [],
        item_menu_state: null,
        form_menu_list: [],
        auth_users:null,
        active_products: null,
        status_suggestion:null,
        status: null,
        product_variation_suggestion:null,
        disable_approved_by:true,
        status_suggestion_list:null,
        user_suggestion_list:null,
        active_users_list:null,
        active_vendors_list:null,
        prev_list:[],
        current_list:[],
        selected_dates:[],
        date_null:null,
        product_variation_list:[],
        active_stores:null,
        min_price:null,
        max_price:null,

    }),
    getters: {

    },
    actions: {
        //---------------------------------------------------------------------




        async searchVendor(event) {
            const query = {
                filter: {
                    q: event,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/vendor',
                this.searchVendorAfter,
                options
            );
        },

        //---------------------------------------------------------------------
        searchVendorAfter(data,res){
            if(data){

                this.active_vendors_list = data;
            }
        },
        //---------------------------------------------------------------------

        async searchAddedBy(event) {
            const query = {
                filter: {
                    q: event,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/added/by',
                this.searchAddedByAfter,
                options
            );
        },

        //---------------------------------------------------------------------
        searchAddedByAfter(data,res){
            if(data){
                this.active_users_list = data;
            }
        },
        //---------------------------------------------------------------------

        async searchStatus(event) {
            const query = {
                filter: {
                    q: event,
                },
            };
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

        searchStatusAfter(data,res){
            if(data){
                this.status_suggestion_list = data;
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

            /**
             * Update query state with the query parameters of url
             */
            this.updateQueryFromUrl(route);
            await this.updateUrlQueryString(this.query);

            if (this.query.filter.product) this.getProductsBySlug();
            if (route.query && route.query.filter && route.query.filter.date) {
                this.selected_dates = route.query.filter.date;
                this.selected_dates = this.selected_dates.join(' - ');
            }
        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {
            switch(route_name)
            {
                case 'productvendors.index':
                    this.view = 'large';
                    this.list_view_width = 12;
                    break;
                default:
                    this.view = 'small';
                    this.list_view_width = 6;
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
                        this.disable_added_by = false;
                        this.getItem(newVal.params.id);
                    }else{
                        this.disable_added_by = true;
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
              }
          },
        //---------------------------------------------------------------------
        async getProductsListForStore(event){
            const query = {
                q:event.query,
                id:this.item.store_ids
            }
            let options = {
                params: query,
                method: 'POST'
            };
            await vaah().ajax(
                this.ajax_url+'/getProductForStore',
                this.afterGetProductsListforStore,
                options
            );
        },
        //---------------------------------------------------------------------
        afterGetProductsListforStore(data, res)
        {
            if(data){
                this.product_suggestion = data;
            }
        },
        //---------------------------------------------------------------------
        setVendor(event) {
            this.item.vh_st_vendor_id = (event && event.value) ? toRaw(event.value).id : null;
        },

        //---------------------------------------------------------------------
        setProduct(event) {
            this.item.vh_st_product_id = (event && event.value && event.value.id) ? toRaw(event.value).id : null;
        },

        //---------------------------------------------------------------------
        setAddedBy(event){
                let user = toRaw(event.value);
                this.item.added_by = user.id;

        },

        //---------------------------------------------------------------------
        setStatus(event) {
            this.item.taxonomy_id_product_vendor_status = (event && event.value) ? toRaw(event.value).id : null;
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
                this.active_vendors = data.active_vendors;
                this.active_users = data.active_users;
                this.active_products = data.active_products;
                this.default_store = data.active_stores.filter(store => store.is_default === 1);
                this.active_product_variation = data.active_product_variations

                if(data.rows)
                {
                    data.rows=this.query.rows;
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
                this.product = data.productList.data
                this.item.taxonomy_id_product_vendor_status = data.status;
                if (data.store_vendor_product) {
                    this.store_name = data.store_vendor_product;
                    this.item.store_ids = this.store_name.map(store => store.id);
                }

                this.item.vh_st_product_variation_id = data.product_variation;

                if (data.product_variations) {
                    await this.searchVariationOfProduct();
                    this.product_variations = data.product_variations;
                    this.product_variation_list = this.product_variation_list.map(listVariation => {
                        const matchingVariation = this.product_variations.find(
                            dataVariation => dataVariation.id === listVariation.id
                        );
                        return matchingVariation || listVariation;
                    });
                }


            }else{
                this.$router.push({name: 'productvendors.index'});
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
            this.action.type = type;
            this.form.action = type;

            let ajax_url = this.ajax_url;

            let options = {
                method: 'post',
            };

            if (this.product_variation_list) {
                const variations = [];

                // Loop through the product_variation_list and push each variation to the array
                for (const variation of this.product_variation_list) {
                    variations.push({
                        name: variation.name,
                        id: variation.id,
                        amount: variation.amount,
                        deleted_at: variation.deleted_at,
                    });
                }

                // Set the variations array to store.item.product_variation
                this.item.product_variation = variations;
            }

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

                case 'save-productprice':
                    options.method = 'POST';
                    options.params = item;
                    ajax_url += '/product/price'
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
                this.item.added_by = data.added_by;
                this.item.taxonomy_id_product_vendor_status = data.status;
                this.item.vh_st_product_variation_id = data.product_variation;
                if (data.store_vendor_product) {
                    this.store_name = data.store_vendor_product;
                    this.item.store_ids = this.store_name.map(store => store.id);
                }
                await this.getList();
                await this.formActionAfter(data);
                this.getItemMenu();
                this.getFormMenu();
            }
            this.current_list=this.list.data
            this.compareList(this.prev_list,this.current_list)

        },
        compareList(prev_list, current_list) {

            const removed_Items = prev_list.filter(previous_item => !current_list.some(current_item => current_item.id === previous_item.id));

            const removed_item_present_in_current_list = removed_Items.some(removed_item =>
                current_list.some(current_item => current_item.id === removed_item.id)
            );
            if (!removed_item_present_in_current_list) {
                this.action.items = this.action.items.filter(item => !removed_Items.some(removed_item => removed_item.id === item.id));
            }
        },
        //---------------------------------------------------------------------
        async formActionAfter (data)
        {
            switch (this.form.action)
            {
                case 'create-and-new':
                case 'save-and-new':
                    this.item.id = null;
                    this.setActiveItemAsEmpty();
                    await this.getFormMenu();
                    this.$router.push({name: 'productvendors.form'})
                    break;
                case 'create-and-close':
                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'productvendors.index'});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    this.$router.push({name: 'productvendors.form'});
                    await this.getFormMenu();
                    break;
                case 'trash':
                    vaah().toastSuccess(['Action Was Successful']);
                    break;
                case 'restore':
                    vaah().toastSuccess(['Action Was Successful']);
                    break;
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
            vaah().toastSuccess(['Page Reloaded']);
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
                this.item.store_vendor_product = [data.fill.store_vendor_product];
                this.item.store_ids = this.item.store_vendor_product.map(store => store.id);
                let self = this;
                Object.keys(data.fill).forEach(function(key) {
                    if (key !== 'store_vendor_product') {
                    self.item[key] = data.fill[key];
                    }
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
        confirmRestoreAll()
        {
            this.action.type = 'restore-all';
            vaah().confirmDialogRestoreAll(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmTrashAll()
        {
            this.action.type='trash-all';
            vaah().confirmDialogTrashAll(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmActivateAll()
        {
            this.action.type='activate-all';
            vaah().confirmDialogActivate(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmDeactivateAll()
        {
            this.action.type='deactivate-all';
            vaah().confirmDialogDeactivate(this.listAction);
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
            this.selected_product=null;
            await this.resetQueryString();

            //reload page list
            await this.getList();
            vaah().toastSuccess(['Action was successful']);
            return false;
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
            this.$router.push({name: 'productvendors.index'})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'productvendors.index'})
        },
        toProductPrice(item)
        {
            this.item.vh_st_product_id=item.vh_st_product_id;
            this.searchVariationOfProduct();
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'productvendors.productprice', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.getDefaultValues();
            this.$router.push({name: 'productvendors.form'})
        },
        //---------------------------------------------------------------------
        async getDefaultValues()
        {
            const options = {
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/get/default/values',
                this.getDefaultValuesAfter,
                options
            );
        },

        //-----------------------------------------------------------------------

        getDefaultValuesAfter(data,res) {
            if (data && data.default_store) {
                this.item.store_vendor_product = [data.default_store];
            }
            if (data && data.default_vendor) {
                this.item.vendor = data.default_vendor;
            }
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            this.item = vaah().clone(item);
            this.$router.push({name: 'productvendors.view', params:{id:item.id}})
        },
        //---------------------------------------------------------------------

        toEdit(item)
        {
            this.item = item;
            this.item.id = item.id;
            this.getFormMenu();
            this.$router.push({name: 'productvendors.form', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        isViewLarge()
        {
            return this.view === 'large';
        },
        //---------------------------------------------------------------------
        getIdWidth()
        {
            let width = 50;

            if(this.list && this.list.total)
            {
                let chrs = this.list.total.toString();
                chrs = chrs.length;
                width = chrs*40;
            }

            return width+'px';
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
                        this.confirmActivateAll();
                    }
                },
                {
                    label: 'Mark all as inactive',
                    command: async () => {
                        this.confirmDeactivateAll();
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
                        label: 'Save & New',
                        icon: 'pi pi-check',
                        command: () => {

                            this.itemAction('save-and-new');
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
            if (!this.item.store_vendor_product) {
                this.item.store_vendor_product = this.default_store;
                this.item.store_ids = this.item.store_vendor_product.map(store => store.id);
            }

        },

        //---------------------------------------------------------------------
        setDateRange() {

            if (!this.selected_dates) {
                return false;
            }
            const dates = [];
            for (const selected_date of this.selected_dates) {

                if (!selected_date) {
                    continue;
                }
                let search_date = moment(selected_date)
                var UTC_date = search_date.format('YYYY-MM-DD');

                if (UTC_date) {
                    dates.push(UTC_date);
                }
                if (dates[0] != null && dates[1] != null) {
                    this.query.filter.date = dates;
                }
            }
        },
        //---------------------------------------------------------------------

        async searchActiveStores(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/active-store',
                this.searchActiveStoresAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchActiveStoresAfter(data,res) {
            if(data)
            {
                this.active_stores = data;
                if (data && this.item.store_vendor_product) {
                    this.active_stores = data.filter((item) => {
                        return !this.item.store_vendor_product.some((activeItem) => {
                            return activeItem.id === item.id;
                        });
                    });
                }
            }
        },

        //---------------------------------------------------------------------
        async setStores(event) {
            let stores = toRaw(event.value);
            this.item.store_ids = stores.map(store => store.id);
            this.item.vh_st_product_id=null;
            this.item.product=null;
        },
        //---------------------------------------------------------------------

        async getProduct(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/filter/search/product',
                this.getProductAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        getProductAfter(data,res) {
            if(data)
            {
                this.filter_product_suggetion = data;
            }
        },

        //---------------------------------------------------------------------


        addProductFIlter() {
            const unique_product = Array.from(new Set(this.selected_product.map(v => v.name)));
            this.selected_product = unique_product.map(name => this.selected_product.find(v => v.name === name));
            this.query.filter.product = this.selected_product.map(v => v.slug);
        },

        //---------------------------------------------------------------------


        async getProductsBySlug()
        {

            let query = {
                filter: {
                    product: this.query.filter.product,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/products-by-slug',
                this.getProductsBySlugAfterRefresh,
                options
            );


        },

        //---------------------------------------------------------------------
        getProductsBySlugAfterRefresh(data, res) {

            if (data) {
                this.selected_product= data;
            }
        },
        //---------------------------------------------------------------------




        //---------------------------------------------------------------------

        async searchVariationOfProduct() {
            const query = {

                id: this.item.vh_st_product_id
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/product-variation',
                this.searchVariationOfProductAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchVariationOfProductAfter(data,res) {
            if(data)
            {
                this.product_variation_list=data;
                this.product_variation_suggestion = data;
                if (data && this.item.product_variation) {
                    this.product_variation_suggestion = data.filter((item) => {
                        return !this.item.product_variation.some((activeItem) => {
                            return activeItem.id === item.id;
                        });
                    });
                }
            }
        },


        calculatePriceRange(product, productVariationPrices) {
            // Check if product_variations and product_variation_prices are equal in length
            if (
                product.product_variations_for_vendor_product &&
                product.product_variations_for_vendor_product.length === productVariationPrices.length
            ) {
                // If equal, use product_variation_prices directly
                const prices = productVariationPrices.map(variationPrice => variationPrice.pivot.amount);

                // Filter out undefined or null values
                const validPrices = prices.filter(price => price !== undefined && price !== null);

                if (validPrices.length === 0) {
                    return 'No prices available';
                }

                const minPrice = Math.min(...validPrices);
                const maxPrice = Math.max(...validPrices);

                if (minPrice === maxPrice) {
                    return `Price: ${minPrice}`;
                } else {
                    return `Price Range: ${minPrice} - ${maxPrice}`;
                }
            }


            let allPrices = [];

            if (product.product_variations_for_vendor_product && product.product_variations_for_vendor_product.length > 0) {
                // Combine prices from product_variations
                allPrices = product.product_variations_for_vendor_product.reduce((prices, variation) => {
                    if (variation.price !== undefined && variation.price !== null) {
                        prices.push(variation.price);
                    }
                    return prices;
                }, []);
            }

            allPrices = allPrices.concat(
                productVariationPrices.reduce((amounts, variationPrice) => {
                    if (variationPrice.amount !== undefined && variationPrice.amount !== null) {
                        amounts.push(variationPrice.amount);
                    }
                    return amounts;
                }, [])
            );

            if (allPrices.length === 0) {
                return 'No prices available';
            }

            const minPrice = Math.min(...allPrices);
            const maxPrice = Math.max(...allPrices);

            if (minPrice === maxPrice) {
                return `Price: ${minPrice}`;
            } else {
                return `Price:  ${minPrice} - ${maxPrice}`;
            }
        },


        //---------------------------------------------------------------------
        setSelectedProductId(productId) {
            if (productId && productId.deleted_at === null) {
                this.selectedProductInfo = {
                    id: productId.id,
                    name: productId.name,
                    slug: productId.slug
                };
                this.selectedProductId=this.selectedProductInfo
            }
        },


        // toProductVariationCreate(product)
        // {
        //     const query = {
        //         page: 1,
        //         rows: 20,
        //         productId: product,
        //     };
        //     const route = {
        //         name: 'productvariations.form',
        //         query: query
        //     };
        //     this.$router.push(route);
        // },

        toProductVariationCreate(productId) {
            this.setSelectedProductId(productId);
            // this.$router.push({ name: 'productvariations.form' });
            this.$router.push({
                name: 'productvariations.form',
            });
        },


        toViewProductVariations(product)
        {
            const query = {
                page: 1,
                rows: 20,
                filter: {
                    products: [product.slug],trashed: 'include'
                }
            };
            const route = {
                name: 'productvariations.index',
                query: query
            };
            this.$router.push(route);
        },
    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useProductVendorStore, import.meta.hot))
}

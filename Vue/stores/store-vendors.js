import {watch, toRaw} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import moment from "moment";

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Vendor';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/vendors";

let empty_states = {
    query: {
        page: 1,
        rows: 20,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
            store:null,
            vendor_status:null,
            product:null,
        },
    },
    action: {
        type: null,
        items: [],
    }
};

export const useVendorStore = defineStore({
    id: 'vendors',
    state: () => ({
        base_url: base_url,
        ajax_url: ajax_url,
        model: model_namespace,
        assets_is_fetching: true,
        app: null,
        assets: null,
        rows_per_page: [10,20,30,50,100,500],
        active_stores: null,
        active_users: null,
        store_suggestions: null,
        approved_by_suggestions: null,
        owned_by_suggestions: null,
        vendor_status:null,
        vendor_status_suggestions:null,
        disable_approved_by:true,
        list: null,
        user_error_message: [],
        selected_product: null,
        product_vendor_status: null,
        product_status:null,
        active_products: null,
        select_all_product: false,
        product_selected_menu: [],
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
        route_prefix: '/vendors',
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
        selected_dates : null,
        business_types_list : null,
        search_products: null,
        first_element:null,
        selected_user_vendor:null,
        user_data:null,
        user_details:null,
        select_all_user:false,
        vendor_roles:null,
        selected_user:null,
        sel_product:null,
        selected_vendor_role:null
    }),
    getters: {

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
            this.first_element = ((this.query.page - 1) * this.query.rows);

            if (route.query && route.query.filter && route.query.filter.date) {
                this.selected_dates = route.query.filter.date;
                this.selected_dates = this.selected_dates.join(' - ');
            }

            /**
             * Update query state with the query parameters of url
             */
            this.updateQueryFromUrl(route);

        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {
            switch(route_name)
            {
                case 'vendors.index':
                    this.view = 'large';
                    this.list_view_width = 12;
                    break;
                case 'vendors.product':
                    this.view = 'small';
                    this.list_view_width = 4;
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
                    if(this.watch_stopper && !newVal.path.startsWith(this.route_prefix)){
                        this.watch_stopper();

                        return false;
                    }

                    this.route = newVal;

                    if(newVal.params.id){
                        this.disable_approved_by = false;
                        this.getItem(newVal.params.id);
                    }else{
                        this.disable_approved_by = true;
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
                this.item.slug = " ";
            }
        },
        //---------------------------------------------------------------------

        addProduct(){

            if (!this.item.products) {
                this.item.products = [];
            }
            const exists = this.item.products.some(item => item.product.id === this.selected_product.id);

            if (!exists) {
                this.item.products.push({
                    product: this.selected_product,
                    is_selected: false,
                    can_update : false,
                    status:this.selected_product.status,
                });
                this.selected_product = null;
            } else {
                this.showUserErrorMessage(['This Product is already present'], 4000);
            }

        },

        //---------------------------------------------------------------------
        showUserErrorMessage(message, time = 2500){
            this.user_error_message = message;
            setTimeout(()=>{
                this.user_error_message = [];
            },time);
        },
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        async removeProduct(product) {
            this.item.products = this.item.products.filter(function (item) {
                return item['product']['id'] !== product['product']['id']
            });
            this.select_all_product = false;

        },

        //---------------------------------------------------------------------
        selectAllProduct() {
            if (this.select_all_product) {
                this.item.products.forEach((item) => {
                    item.is_selected = false;
                });
            } else {
                this.item.products.forEach((item) => {
                    item.is_selected = true;
                });
            }
        },

        //---------------------------------------------------------------------
        async removeAllProduct()
        {
            this.item.products = [];
            this.select_all_product = false;
        },

        //---------------------------------------------------------------------
        async bulkRemoveProduct() {

            let selected_products = this.item.products.filter(product => product.is_selected);
            let temp = null;
            this.select_all_product = false;
            temp = this.item.products.filter((item) => {
                return item['is_selected'] === false;
            });

            if (selected_products.length === 0) {
                vaah().toastErrors(['Select a product']);
                return false;
            }

            else if (temp.length === this.item.products.length) {
                this.item.products = [];
            }
            else {

                this.item.products = temp;
            }

        },

        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
       async bulkRemoveProductAfter(){
             await this.getList();
             this.item.products = [];
             this.select_all_product=false;
        },
        //---------------------------------------------------------------------
        async removeProductAfter(data,res){
            if(data)
            {
                this.item = data;
                await this.getList();
                await this.formActionAfter(data);
                this.getItemMenu();
                this.getFormMenu();
            }
        },
        //---------------------------------------------------------------------
        setStore(event){
            let store = toRaw(event.value);
            this.item.vh_st_store_id = store.id;

        },
        //---------------------------------------------------------------------
        setApprovedBy(event){
            let user = toRaw(event.value);
            this.item.approved_by = user.id;

        },
        //---------------------------------------------------------------------
        setOwnedBy(event){
            let user = toRaw(event.value);
            this.item.owned_by = user.id;

        },
        //---------------------------------------------------------------------
        setStatus(event){
            let status = toRaw(event.value);
            this.item.taxonomy_id_vendor_status = status.id;
        },

        //---------------------------------------------------------------------

        setDateRange(){

            if(!this.selected_dates){
                return false;
            }

            const dates =[];

            for (const selected_date of this.selected_dates) {

                if(!selected_date){
                    continue ;
                }

                let search_date = moment(selected_date)
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
                this.active_stores = data.active_stores;
                this.active_users = data.active_users;
                this.business_types_list = data.business_type;
                this.vendor_status = data.taxonomy.vendor_status;
                this.active_products = data.active_products;
                this.selected_product = data.default_product;
                this.business_types_list = data.taxonomy.business_type;
                this.product_vendor_status = data.taxonomy.product_vendor_status;
                this.product_status = data.taxonomy.product_status;
                this.vendor_roles = data.vendor_roles.roles;

                this.disable_approved_by = this.route.params && this.route.params.id && this.route.params.id.length == 0;
                if(data.rows)
                {

                    data.rows = this.query.rows
                }

                if(this.route.params && !this.route.params.id){
                    this.item = vaah().clone(data.empty_item);
                }

            }
        },
        //---------------------------------------------------------------------
        async searchStore(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/store',
                this.searchStoreAfter,
                options
            );

        },
        //-----------------------------------------------------------------------

        searchStoreAfter(data,res) {
            if(data)
            {
                this.store_suggestions = data;
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
                this.vendor_status_suggestions = data;
            }
        },
        //---------------------------------------------------------------------
        async searchApprovedBy(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/approved/by',
                this.searchApprovedByAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchApprovedByAfter(data,res){
            if(data){
                this.approved_by_suggestions = data;
            }
        },
        //---------------------------------------------------------------------

        setBusinessType(event){
            let business_type = toRaw(event.value);
            this.item.taxonomy_id_vendor_business_type = business_type.id;
        },

        //---------------------------------------------------------------------

        async searchOwnedBy(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/owned/by',
                this.searchOwnedByAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchOwnedByAfter(data,res){
            if(data){
                this.owned_by_suggestions = data;
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
                this.first_element = this.query.rows * (this.query.page - 1);
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
                this.$router.push({name: 'vendors.index'});
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

                case 'save-product':
                    options.method = 'POST';
                    options.params = item;
                    ajax_url += '/product'
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
                this.select_all_product = false;
                await this.getList();
                await this.formActionAfter(data);
                this.getItemMenu();
                this.getFormMenu();
            }
            this.current_list=this.list.data
            this.compareList(this.prev_list,this.current_list)
        },

        //----------------------------------------------------------------------------

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
                    this.setActiveItemAsEmpty();
                    break;
                case 'create-and-close':
                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'vendors.index'});
                    break;
                case 'save-and-new':
                    this.setActiveItemAsEmpty();
                    vaah().toastSuccess(['Action Was Successful']);
                    this.$router.push({name: 'vendors.form'});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    this.route.params.id = null;
                    this.$router.push({name: 'vendors.form'});
                    await this.getFormMenu();
                    break;
                case 'trash':
                case 'restore':
                    this.item = data;
                    vaah().toastSuccess(['Action was successful']);
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

        confirmTrashAll()
        {
            this.action.type = 'trash-all';
            vaah().confirmDialogTrashAll(this.listAction);
        },

        confirmRestoreAll()
        {
            this.action.type = 'restore-all';
            vaah().confirmDialogRestoreAll(this.listAction);
        },
        //---------------------------------------------------------------------

        confirmActivateAll()
        {
            this.action.type = 'activate-all';
            vaah().confirmDialogActivateAll(this.listAction);
        },
        //---------------------------------------------------------------------

        confirmDeactivateAll()
        {
            this.action.type = 'deactivate-all';
            vaah().confirmDialogDeactivateAll(this.listAction);
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
            this.sel_product = null;
            this.selected_dates=[];

            this.date_null= this.route.query && this.route.query.filter ? this.route.query.filter : 0;
            vaah().toastSuccess(['Action was successful']);
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
            this.filter_selected_products = null;
            await this.updateUrlQueryString(this.query);
        },
        //---------------------------------------------------------------------
        closeForm()
        {
            this.$router.push({name: 'vendors.index'})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'vendors.index'})
        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.$router.push({name: 'vendors.form'})
        },
        //---------------------------------------------------------------------
        toProduct(item)
        {
            this.select_all_product = false;
            this.item = vaah().clone(item);
            this.selected_product = null;
            this.$router.push({name: 'vendors.product', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            this.item = vaah().clone(item);
            this.$router.push({name: 'vendors.view', params:{id:item.id}})
        },
        //---------------------------------------------------------------------

        async reloadPage()
        {
            await this.getList();
            vaah().toastSuccess(["Page Reloaded"]);
        },

        //---------------------------------------------------------------------

        toEdit(item)
        {
            this.item = item;
            this.$router.push({name: 'vendors.form', params:{id:item.id}})
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

            this.product_selected_menu = [
                {
                    label: 'Remove',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.bulkRemoveProduct()
                    }
                },
                {
                    label: 'Remove All',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.removeAllProduct()
                    }
                },

            ]

            this.user_selected_menu = [
                {
                    label: 'Remove',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.bulkRemoveUser()
                    }
                },
                {
                    label: 'Remove All',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.removeAllUser()
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




        toVendor(item)
        {
            this.$router.push({name: 'vendors.product'})
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
                        label: 'Save & New',
                        icon: 'pi pi-check',
                        command: () => {

                            this.itemAction('save-and-new');
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

        removeImage()
        {
            this.item.business_document_file = null;
        },


        //---------------------------------------------------------------------

        shortProductName(products) {
            if (!products || !Array.isArray(products)) {
                return [];
            }
            return products.map(product => {
                if (product.name && product.name.length > 20) {
                    return {
                        ...product,
                        name: product.name.substring(0, 20) + '...'
                    };
                } else {
                    return product;
                }
            });
        },

        //-------------------------------------------------------------------------

        getFullName(option) {

            if (option) {
                const matching_product = this.active_products.find(product => product.id === option.id);
                if (matching_product) {
                    return matching_product.name;
                } else {
                    return option.slug; // show the slug value of the option if product not match .
                }
            }

            return '';
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
        //-----------------------------------------------------------------------

        searchProductAfter(data,res) {
            if(data)
            {
                this.search_products = data;
            }
        },

        //-----------------------------------------------------------------------

        async saveProduct()
        {
            let ajax_url = this.ajax_url;
            let options = {
                method: 'post',
                params : this.item,
            };
            ajax_url += '/add/product';
            await vaah().ajax(
                ajax_url,
                this.saveProductAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        async saveProductAfter(data, res)
        {
            if(data)
            {
                await this.getList();
                this.toList();

            }
        },

        //---------------------------------------------------------------------
        toViewProducts(vendor)
        {
            const query = {
                page: 1,
                rows: 20,
                filter: {
                    vendors: [vendor.slug]
                }
            };
            const route = {
                name: 'products.index',
                query: query
            };
            this.$router.push(route);
        },


        //-----------------------------------------------------------------------

        async saveUser()
        {

            let ajax_url = this.ajax_url;
            let options = {
                method: 'post',
                params: {
                    item: this.item,
                    user_details: this.item.users,
                }
            };
            ajax_url += '/add/user';
            await vaah().ajax(
                ajax_url,
                this.saveUserAfter,
                options
            );
            this.getItem(this.item.id);
        },

        //---------------------------------------------------------------------

        async saveUserAfter(data, res)
        {
            if(data)
            {
                await this.getList();

            }
        },

        //-----------------------------------------------------------------------

        //-----------------------------------------------------------------------

        async removeUser(remove_item)
        {
            this.item.users = this.item.users.filter(function (item) {
                return item.id !== remove_item.id || item.pivot.vh_role_id !== remove_item.pivot.vh_role_id;
            });
            this.select_all_user = false;
        },

        //---------------------------------------------------------------------

        async removeUserAfter(data, res)
        {
            if(data)
            {
                await this.getList();
            }
        },

        //-----------------------------------------------------------------------


        toVendorRole(item)
        {
            this.item = item;
            this.select_all_user = false;
            this.$router.push({name: 'vendors.role', params:{id:item.id}})
        },

        //---------------------------------------------------------------------
        async searchUser(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/vendor/user',
                this.searchUserAfter,
                options
            );

        },
        //-----------------------------------------------------------------------

        searchUserAfter(data,res) {
            if(data)
            {
                this.user_data = data;
            }
        },

        //--------------------------------------------------------------------------

        addUser() {
            if (!this.item.users) {
                this.item.users = [];
            }

            if (this.selected_user && this.selected_vendor_role) {
                const exists = this.item.users.some(item =>
                    item.id === this.selected_user.id &&
                    item.pivot.vh_role_id === this.selected_vendor_role.id
                );

                if (!exists) {
                    this.item.users.push({
                        id: this.selected_user.id,
                        name: this.selected_user.first_name,
                        pivot: {
                            vh_user_id: this.selected_user.id,
                            vh_role_id: this.selected_vendor_role.id
                        }
                    });

                } else {
                    this.showUserErrorMessage(['This Record already present in the list.'], 4000);
                }

                this.selected_user = null;
                this.selected_vendor_role = null;
            } else {
                this.showUserErrorMessage(['No user or role selected.'], 4000);
            }
        },





        //---------------------------------------------------------------------

        selectAllUser() {
            // Toggle select_all_user
            this.select_all_user = !this.select_all_user;

            // Update is_selected property for each item
            this.item.users.forEach((i) => {
                i['is_selected'] = this.select_all_user;
            });

        },


        //----------------------------------------------------------------------------
        async removeAllUser()
        {
            this.item.users = [];
            this.select_all_user = false;
        },

        //---------------------------------------------------------------------
        async bulkRemoveUser() {

            const selectedItems = this.item.users.filter(item => item.is_selected);

            this.item.users = this.item.users.filter(item => !item.is_selected);
        },


        addSelectedProduct () {

            const unique_products = [];
            const check_names = new Set();

            for (const products of this.sel_product) {
                if (!check_names.has(products.name)) {
                    unique_products.push(products);
                    check_names.add(products.name);
                }
            }
            const products_slug = unique_products.map(product => product.slug);
            this.sel_product = unique_products;
            this.query.filter.products = products_slug;
        },

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
                this.sel_product = data;
            }
        },





    }
        //---------------------------------------------------------------------



});

// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useVendorStore, import.meta.hot))
}

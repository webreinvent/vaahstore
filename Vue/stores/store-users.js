import { watch } from 'vue'
import { acceptHMRUpdate, defineStore } from 'pinia'
import { vaah } from '../vaahvue/pinia/vaah'
import { useRootStore } from "./root";
import qs from 'qs'
import dayjs from 'dayjs';
import dayjsPluginUTC from 'dayjs-plugin-utc'

dayjs.extend(dayjsPluginUTC)

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\User';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/users";

let empty_states = {
    query: {
        page: 1,
        rows: 20,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
            date:null,
        },
        recount: null,
    },

    action: {
        type: null,
        items: [],
    },

    user_roles_query: {
        q: null,
        page: null,
        rows: null,
    }
};

export const useUserStore = defineStore({
    id: 'users',
    state: () => ({
        title: 'Users',
        base_url: base_url,
        ajax_url: ajax_url,
        model: model_namespace,
        assets_is_fetching: true,
        app: null,
        assets: null,
        user_roles:null,
        displayModal:false,
        modalData:null,
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
        route_prefix: 'users.',
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
        item_menu_list: [],
        list_create_menu: [],
        item_menu_state: null,
        filtered_timezone_codes:[],
        filtered_country_codes:[],
        form_menu_list: [],
        gender_options: [
            {label:'Male',value:'m',icon: ''},
            {label:'Female',value:'f',icon: ''},
            {label:'Others',value:'o',icon: ''},
        ],
        status_options: [
            {
                label: 'Active',
                value: 'active'
            },
            {
                label: 'Inactive',
                value: 'inactive'
            },
            {
                label: 'Blocked',
                value: 'blocked'
            },
            {
                label: 'Banned',
                value: 'banned'
            },

        ],

        user_roles_menu: null,
        meta_content: null,
        user_roles_query: vaah().clone(empty_states.user_roles_query),
        is_btn_loading: false,
        display_meta_modal: false,
        custom_fields_data:[],
        display_bio_modal: null,
        bio_modal_data: null,
        firstElement: null,
        rolesFirstElement: null,
        selected_dates:null,
        prev_list:[],
        current_list:[],
        email_error:{
            class:'',
            msg:''

        },
        customer_count_chart_options: {},
        customer_count_chart_series: [],
        total_customers:null,
        total_orders:null,
        avg_orders_per_customer :null,
        average_order_value :null,
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
                return 'lg:w-1/2';
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
            this.firstElement = ((this.query.page - 1) * this.query.rows);
            this.rolesFirstElement = ((this.user_roles_query.page - 1) * this.user_roles_query.rows);
            /**
             * Update query state with the query parameters of url
             */
            this.updateQueryFromUrl(route);
            if (this.query.filter.customer_group) this.getCustomerGroupsBySlug();
            if (route.query && route.query.filter && route.query.filter.date) {
                this.selected_dates = route.query.filter.date;
                this.selected_dates = this.selected_dates.join(' - ');
            }
        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {
            // switch(route_name)
            // {
            //     case 'users.index':
            //         this.view = 'large';
            //         this.list_view_width = 12;
            //         break;
            //     default:
            //         this.view = 'small';
            //         this.list_view_width = 7;
            //         break
            // }
            this.view = 'list';

            if(route_name.includes('users.view')
                || route_name.includes('users.form')
            ){
                this.view = 'list-and-item';
            }

            if(route_name.includes('users.filters')){
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

                    if(this.watch_stopper && !newVal.name.includes(this.route_prefix)){
                        this.watch_stopper();

                        return false;
                    }

                    this.route = newVal;
                    if (newVal.params.id) {
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
            );

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

                if (data.rows) {
                    if (!this.query.rows) {
                        this.query.rows = data.rows;
                    } else {
                        this.query.rows = parseInt(this.query.rows);
                    }
                    this.user_roles_query.rows = data.rows;
                }

                if (this.route.params && !this.route.params.id) {
                    this.item = vaah().clone(data.empty_item);
                }

            }
        },
        //--------------------------------------------------------------------
        searchTimezoneCode: function (event){
            this.timezone_name_object = null;
            this.timezone = null;
            setTimeout(() => {
                if (!event.query.trim().length) {
                    this.filtered_timezone_codes = this.assets.timezones;
                }else {
                    this.filtered_timezone_codes = this.assets.timezones.filter((timezone) => {
                        return timezone.name.toLowerCase().startsWith(event.query.toLowerCase());
                    });
                }
            }, 250);

        },
        //---------------------------------------------------------------------
        onSelectTimezoneCode: function (event){
            this.item.timezone = event.value.slug;
        },
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        searchCountryCode: function (event){
            this.country_name_object = null;
            this.country = null;

            setTimeout(() => {
                if (!event.query.trim().length) {
                    this.filtered_country_codes = this.assets.countries;
                }
                else {
                    this.filtered_country_codes = this.assets.countries.filter((country) => {
                        return country.name.toLowerCase().startsWith(event.query.toLowerCase());
                    });
                }
            }, 250);
        },
        //---------------------------------------------------------------------
        onSelectCountryCode: function (event){
            this.item.country = event.value.name;
        },
        //---------------------------------------------------------------------
        async getList() {
            let options = {
                query: vaah().clone(this.query)
            };
            await this.updateUrlQueryString(this.query);
            await vaah().ajax(
                this.ajax_url,
                await this.afterGetList,
                options
            );
        },
        //---------------------------------------------------------------------
        async afterGetList (data, res) {

            this.is_btn_loading = false;
            this.query.recount = null;

            if (data) {
                this.list = data;
                this.firstElement = this.query.rows * (this.query.page - 1);
                await this.fetchCustomerCountChartData();
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
                this.$router.push({name: 'users.index'});
            }
            this.getItemMenu();
            await this.getFormMenu();
        },
        //---------------------------------------------------------------------
        storeAvatar(data) {

            data.user_id = this.item.id;

            let option = {
                params: data,
                method: 'post'
            };

            let url = ajax_url+'/avatar/store';

            vaah().ajax(
                url,
                this.storeAvatarAfter,
                option
            );

        },
        //---------------------------------------------------------------------
        storeAvatarAfter(data, res)
        {
            if(data){
                this.item.avatar = data.avatar;
                this.item.avatar_url = data.avatar_url;
            }
        },
        //---------------------------------------------------------------------
        removeAvatar() {

            let option = {
                params: {
                    user_id: this.item.id
                },
                method: 'post'
            };

            let url = ajax_url+'/avatar/remove';

            vaah().ajax(
                url,
                this.removeAvatarAfter,
                option
            );

        },
        //---------------------------------------------------------------------
        removeAvatarAfter(data, res)
        {
            if(data){
                this.item.avatar = data.avatar;
                this.item.avatar_url = data.avatar_url;
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
        //--------------------------------------------------------------------

        //---------------------------------------------------------------------
        showModal(item){
            this.displayModal = true;
            this.modalData = item.json;
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
            options.params.query = vaah().clone(this.query);
            await vaah().ajax(
                url,
                this.updateListAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        itemAction(type, item=null){
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
                    // console.log(item);return
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

            vaah().ajax(
                ajax_url,
                this.itemActionAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        async itemActionAfter(data, res) {
            if (data) {
                this.item = data;
                this.prev_list =this.list.data;
                await this.getList();
                await this.formActionAfter();
                this.getItemMenu();

                if (this.route.params && this.route.params.id) {
                    await this.getItem(this.route.params.id);
                }
            }
            this.current_list=this.list.data
            this.compareList(this.prev_list,this.current_list)
        },
        //---------------------------------------------------------------------
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
        async formActionAfter ()
        {
            switch (this.form.action)
            {
                case 'create-and-new':
                case 'save-and-new':
                    this.setActiveItemAsEmpty();
                    this.route.params.id = null;
                    this.$router.push({name: 'users.form'});
                    break;
                case 'create-and-close':
                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'users.index'});
                    break;
                case 'save-and-clone':
                    this.item.id = null;
                    this.route.params.id = null;
                    this.$router.push({name: 'users.form'});
                    break;
                case 'trash':
                    this.item = null;
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
        async getFaker () {
            let params = {
                model_namespace: this.model,
                except: this.assets.fillable.except,
            };

            let url = this.ajax_url+'/fill';
            let options = {
                params: params,
                method: 'post',
            };

            vaah().ajax(
                url,
                this.getFakerAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        getFakerAfter: function (data, res) {
            if(data)
            {
                let self = this;
                Object.keys(data.fill).forEach(function(key) {
                    self.item[key] = data.fill[key];
                });
            }
        },

        //---------------------------------------------------------------------
        async sync() {
            this.is_btn_loading = true;
            // this.query.recount = true;

            await this.getList();
            vaah().toastSuccess(['Page Reloaded']);
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
            this.selected_customer_group = null;
            this.selected_dates=[];
            //reset query strings
            await this.resetQueryString();

            //reload page list
            await this.getList();
            vaah().toastSuccess(['Action Was Successful']);
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
            this.$router.push({name: 'users.index'})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = null;
            this.$router.push({name: 'users.index'})
        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.$router.push({name: 'users.form'})
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            this.item = vaah().clone(item);
            this.$router.push({name: 'users.view', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        toEdit(item)
        {
            this.item = item;
            this.$router.push({name: 'users.form', params:{id:item.id}})
        },

        //---------------------------------------------------------------------
        isListView()
        {
            return this.view === 'list';
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
        async getItemMenu()
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
        //--------------------------------------------------------------------
        onUpload(){
            this.user_avatar = e.files[0];

            let formData = new FormData();

            formData.append('file', this.user_avatar);
            formData.append('folder_path', 'public/media');

            vaah().ajax(
                this.ajax_url+'/upload',
                this.uploadAfter,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    method: 'post',
                    params:  formData
                }
            );
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
                        icon: 'pi pi-plus',
                        command: () => {

                            this.itemAction('save-and-new');

                        }
                    },
                    {
                        label: 'Trash',
                        icon: 'pi pi-times',
                        command: () => {
                            this.itemAction('trash');
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
                    this.getFaker();
                }
            },)

            this.form_menu_list = form_menu;

        },
        //---------------------------------------------------------------------
        isHidden(key) {
            if (this.assets && this.assets.fields && this.assets.fields[key]) {
                return this.assets.fields[key].is_hidden
            }

            return false;
        },
        //---------------------------------------------------------------------
        showProgress()
        {
            this.show_progress_bar = true;
        },
        //---------------------------------------------------------------------
        hideProgress()
        {
            this.show_progress_bar = false;
        },
        //---------------------------------------------------------------------
        checkHidden(item)
        {
            if (this.assets && this.assets.custom_fields){
                let select_array = vaah().findInArrayByKey(this.assets.custom_fields.value, 'slug', item);
                return select_array.is_hidden;
            }
            return false;
        },
        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
        setIsActiveStatus() {
            if (this.item.status === 'active') {
                this.item.is_active = 1;
            } else {
                this.item.is_active = 0;
            }
        },
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        validateEmail() {
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.item.email)) {
                this.email_error = { class: '',msg:''};
            } else {
                this.email_error = { class: 'p-invalid',msg:'Please enter a valid email address'};
            }
        },
        //---------------------------------------------------------------------
        toViewCustomerGroups(customer)
        {
            const query = {
                page: 1,
                rows: 20,
                filter: {
                    customers: [customer.display_name]
                }
            };
            const route = {
                name: 'customergroups.index',
                query: query
            };
            this.$router.push(route);
        },

        //---------------------------------------------------------------------


        async searchCustomerGroup(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/customergroup',
                this.searchCustomerGroupAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchCustomerGroupAfter(data,res) {
            if(data)
            {
                this.customer_group_suggestion = data;
            }
        },

        //---------------------------------------------------------------------
        addCustomerGroup() {
            const unique_customer_group = Array.from(new Set(this.selected_customer_group.map(v => v.name)));
            this.selected_customer_group = unique_customer_group.map(name => this.selected_customer_group.find(v => v.name === name));
            this.query.filter.customer_group = this.selected_customer_group.map(v => v.slug);
        },
        //---------------------------------------------------------------------
        async getCustomerGroupsBySlug()
        {

            let query = {
                filter: {
                    customer_group: this.query.filter.customer_group,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/customer-group-by-slug',
                this.getCustomerGroupsAfterRefresh,
                options
            );


        },

        //---------------------------------------------------------------------
        getCustomerGroupsAfterRefresh(data, res) {

            if (data) {
                this.selected_customer_group= data;
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
                let search_date = dayjs(selected_date)
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
        async fetchCustomerCountChartData() {
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
                this.fetchCustomerCountChartDataAfter,
                options
            );
        },
        //---------------------------------------------------
        fetchCustomerCountChartDataAfter(data,res){
            if (!data || !Array.isArray(data.chart_series)) {
                return;
            }

            this.total_customers =data.summary.total_customers;
            this.total_orders =data.summary.total_orders;
            this.avg_orders_per_customer =data.summary.avg_orders_per_customer;
            this.average_order_value  =data.summary.average_order_value;
            console.log(this.total_customers)

            const seriesData = data.chart_series.map(series => ({
                name: series.name ,
                data: Array.isArray(series.data) ? series.data : [],
            }));
            this.updateChartSeries(seriesData);
            const updatedOptions = {
                ...data.chart_options,


                legend: {
                    position: 'bottom',
                    horizontalAlign: 'left',
                    floating: false,
                    fontSize: '14px',
                    formatter: function (val, opts) {
                        const seriesIndex = opts.seriesIndex;
                        const seriesData = opts.w.globals.series[seriesIndex];
                        const seriesName = opts.w.globals.seriesNames[seriesIndex];
                        const sum = seriesData.reduce((acc, value) => acc + value, 0);
                        const newLabel = val === 'Customer Created' ? 'Total Customers' : val;
                        // Only display the last value for the "Total" series
                        if (seriesName === 'Total') {
                            const lastValue = seriesData[seriesData.length - 1]; // Last value of the "Total" series
                            return `${seriesName} - ${lastValue}`;
                        }

                        // For other series, just show the normal label
                        return `${newLabel} - ${sum}`;
                    },
                },

                stroke: {
                    curve: "smooth",
                    width: [2, 3],
                },colors: ["#008FFB", "#00E396"],
                chart: {
                    toolbar: {
                        show: false
                    },
                    type:'area',


                },
                grid: {
                    borderColor: '#e0e0e0',
                    strokeDashArray: 0,
                    position: 'back',
                    xaxis: {
                        lines: {
                            show: false,
                        },
                        axisTicks: {
                            show: false,
                        },
                        axisBorder: {
                            show: false,
                        },
                    },
                    yaxis: {
                        lines: {
                            show: false,
                        }
                    },
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0
                    }
                },

                tooltip: {
                    enabled: true,
                    shared: true,
                    custom: function({ series, seriesIndex, dataPointIndex, w }) {
                        const date = w.globals.categoryLabels[dataPointIndex] || w.globals.labels[dataPointIndex];
                        const joined = series[0][dataPointIndex] ?? 0;
                        const orderActivity = series[1][dataPointIndex] ?? 0;
                        return `<div style="background: #fff; padding: 12px; border-radius: 50%;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15); text-align: center;
                min-width: 120px; border: 2px solid rgba(0, 0, 0, 0.05); font-family: Arial, sans-serif;">
                <strong style="color: #333; font-size: 14px; display: block; margin-bottom: 5px;">${date}</strong>
                <div style="font-size: 14px;">
                    <div style="color: #008FFB;">Joined: <strong>${joined}</strong></div>
                    <div style="color: #00E396;">Order Activity: <strong>${orderActivity}</strong></div>
                </div>
            </div>`;
                    }
                },
                dataLabels: {
                    enabled: false,
                },
                yaxis: {
                    title: {
                        text: 'Customers Count',
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold',
                            color: '#263238'
                        },
                        offsetX: -2,
                    },
                    labels: {
                        padding: 15,
                    }
                },
                title: {
                    text: 'Customers Track',
                    align: 'left',
                    offsetY: 12,
                    style: {
                        fontSize: '14px',
                        fontWeight: 'normal',
                        color: '#263238'
                    }
                },fill: {
                    type: ["solid", "gradient"],
                    gradient: {
                        shadeIntensity: 0,
                        opacityFrom: 0.5,
                        opacityTo: 0.05,
                        stops: [0, 80, 100]
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

            };
            this.updateChartOptions(updatedOptions);

        },
        //---------------------------------------------------
        updateChartOptions(newOptions) {
            this.customer_count_chart_options = newOptions;
        },

        //---------------------------------------------------
        updateChartSeries(newSeries) {

            this.customer_count_chart_series = [...newSeries];
        },
        //---------------------------------------------------

        //---------------------------------------------------

    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useUserStore, import.meta.hot))
}




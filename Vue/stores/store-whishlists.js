import {watch,toRaw } from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import moment from 'moment';

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Whishlist';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/whishlists";

let empty_states = {
    query: {
        page: null,
        rows: null,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
            wishlist_status: null,
            wishlist_type:null,
            date:null,
            users : null,
            products : null,
        },
    },
    action: {
        type: null,
        items: [],
    }
};

export const useWhishlistStore = defineStore({
    id: 'whishlists',
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
        route_prefix: 'whishlists.',
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
        status_suggestion: null,
        type_suggestion: null,
        user_suggestion: null,
        form_menu_list: [],
        selected_dates:null,
        date_null:null,
        product_suggestion:null,
        selected_product : null,
        user_error_message: [],
        select_all_product : false,
        product_selected_menu : [],
        selected_users : null,

    }),
    getters: {

    },
    actions: {
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
      async searchType(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/type',
                this.searchTypeAfter,
                options
            );
            },
        //---------------------------------------------------------------------
        searchTypeAfter(data,res) {
            if(data)
            {
                this.type_suggestion = data;
            }
        },
        //---------------------------------------------------------------------
       async searchUsers(event) {

            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/users',
                this.searchUsersAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchUsersAfter(data,res) {
            if (data) {

                this.user_suggestion = data;

                // Filter out the selected users from the user_suggestion list
                if(this.selected_users)
                {
                    const filtered_suggestions = data.filter((user) => {
                        return !this.selected_users.some((selectedUser) => selectedUser.id === user.id);
                    });

                    this.user_suggestion = filtered_suggestions;
                }

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
                case 'whishlists.index':
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
              }
          },
        //---------------------------------------------------------------------
        setUser(event) {
            let user = toRaw(event.value);
            this.item.vh_user_id = user.id;
        },
        //---------------------------------------------------------------------
        setWhishlistsType(event) {
            let whishlist_type = toRaw(event.value);
            this.item.taxonomy_id_whishlists_types = whishlist_type.id;
        },
        //---------------------------------------------------------------------
        setStatus(event) {
            let status = toRaw(event.value);
            this.item.taxonomy_id_whishlists_status = status.id;
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
                this.active_users = data.active_users;
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
            }else{
                this.$router.push({name: 'whishlists.index'});
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
            }
            this.current_list=this.list.data;

            this.compareList(this.prev_list,this.current_list);
        },
        //---------------------------------------------------------------------
        compareList(prev_list, current_list) {
            const prev_set = new Set(prev_list.map(item => item.id));

            const current_set = new Set(current_list.map(item => item.id));

            const removed_items = prev_list.filter(item => !current_set.has(item.id));

            this.action.items = this.action.items.filter(item => current_set.has(item.id));

            if (removed_items.length > 0) {
                // Do something with removed items

                //may update this in future
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
                    this.$router.push({name: 'whishlists.index'});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    await this.getFormMenu();
                    break;
                case 'save-and-new':
                    this.item.id = null;
                    await this.getFormMenu();
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'whishlists.form'});
                    vaah().toastSuccess(['Action Was Successful']);
                    break;
                case 'trash':
                    vaah().toastSuccess(['Action Was Successful']);
                    break;
                case 'restore':
                    vaah().toastSuccess(['Action Was Successful']);
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
        confirmTrash()
        {
            if(this.action.items.length < 1)
            {
                vaah().toastErrors(['Select a record']);
                return false;
            }
            this.action.type = 'trash';
            vaah().confirmDialogTrash(this.listAction);
        },

        //---------------------------------------------------------------------
        confirmApproved()
        {
            if(this.action.items.length < 1)
            {
                vaah().toastErrors(['Select a record']);
                return false;
            }
            this.action.type = 'approved';
            vaah().confirmDialogApproved(this.listAction);
        },

        //---------------------------------------------------------------------
        confirmPending()
        {
            if(this.action.items.length < 1)
            {
                vaah().toastErrors(['Select a record']);
                return false;
            }
            this.action.type = 'pending';
            vaah().confirmDialogPending(this.listAction);
        },

        //---------------------------------------------------------------------
        confirmRejected()
        {
            if(this.action.items.length < 1)
            {
                vaah().toastErrors(['Select a record']);
                return false;
            }
            this.action.type = 'reject';
            vaah().confirmDialogRejected(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmRestore()
        {
            if(this.action.items.length < 1)
            {
                vaah().toastErrors(['Select a record']);
                return false;
            }
            this.action.type = 'restore';
            vaah().confirmDialogRestore(this.listAction);
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

        //---------------------------------------------------------------------

        confirmRestoreAll()
        {
            this.action.type = 'restore-all';
            vaah().confirmDialogRestoreAll(this.listAction);
        },
        //---------------------------------------------------------------------

        confirmApprovedAll()
        {
            this.action.type = 'approved-all';
            vaah().confirmDialogApproveAll(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmPendingAll()
        {
            this.action.type = 'pending-all';
            vaah().confirmDialogPendingAll(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmRejectedAll()
        {
            this.action.type = 'reject-all';
            vaah().confirmDialogRejectAll(this.listAction);
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

            this.selected_dates=[];
            this.selected_users = null;
            this.selected_products = null;
            this.date_null= this.route.query && this.route.query.filter ? this.route.query.filter : 0;

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
            vaah().toastSuccess(['Action Was Successful']);
            await this.updateUrlQueryString(this.query);
        },
        //---------------------------------------------------------------------
        closeForm()
        {
            this.$router.push({name: 'whishlists.index'})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'whishlists.index'})
        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.$router.push({name: 'whishlists.form'})
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            this.item = vaah().clone(item);
            this.$router.push({name: 'whishlists.view', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        toEdit(item)
        {
            this.item = item;
            this.$router.push({name: 'whishlists.form', params:{id:item.id}})
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
                    label: 'Mark as Approved',
                    command: async () => {
                        await this.updateList('approve')
                    }
                },
                {
                    label: 'Mark as Pending',
                    command: async () => {
                        await this.updateList('pending')
                    }
                },
                {
                    label: 'Mark as Rejected',
                    command: async () => {
                        await this.updateList('reject')
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

        },
        //---------------------------------------------------------------------
        getListBulkMenu()
        {
            this.list_bulk_menu = [
                {
                    label: 'Mark all as approved',
                    command: async () => {
                        this.confirmApprovedAll();
                    }
                },
                {
                    label: 'Mark all as pending',
                    command: async () => {
                        this.confirmPendingAll();
                    }
                },
                {
                    label: 'Mark all as rejected',
                    command: async () => {
                        this.confirmRejectedAll();
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
                    },
                     {
                    label: 'Delete',
                        icon: 'pi pi-trash',
                        command: () => {
                        this.confirmDeleteItem('delete');
                    }
                },)
                }
                else{
                    form_menu.push({
                        label: 'Trash',
                        icon: 'pi pi-times',
                        command: () => {
                            this.itemAction('trash');
                            this.item = null;
                            this.toList();
                        }
                    } ,
                      {
                        label: 'Delete',
                            icon: 'pi pi-trash',
                            command: () => {
                            this.confirmDeleteItem('delete');
                        }
                    },)

                }

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

            form_menu.push(
                {
                label: 'Fill',
                icon: 'pi pi-pencil',
                command: () => {
                    this.getFormInputs();
                }
            },)

            this.form_menu_list = form_menu;

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

        toProduct(item)
        {
            this.selected_product = null;
            this.$router.push({name: 'whishlists.products', params:{id:item.id}})
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
        searchProductAfter(data,res) {
            if(data)
            {
                this.product_suggestion = data;
                if(this.selected_products)
                {
                    const filtered_suggestions = data.filter((product) => {
                        return !this.selected_products.some((selected_product) => selected_product.id === product.id);
                    });

                    this.product_suggestion = filtered_suggestions;
                }
            }
        },
        //---------------------------------------------------------------------
        addProduct() {

            if (!this.item.products) {
                this.item.products = []; // Initialize the products array if it's undefined
            }
            const exist = this.item.products.some(item => item.product.id === this.selected_product.id);

           if(!exist)
           {
               const new_product = {
                   product: this.selected_product,
                   is_selected: false,
               };
               this.item.products.push(new_product);
               this.selected_product = null;
           }
           else {
               this.showUserErrorMessage(['This product is already present'], 4000);
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
        async removeProduct(product) {
            this.item.products = this.item.products.filter(function (item) {
                return item['product']['id'] != product['product']['id']
            })

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

        setFilterSelectedUsers() {

            const unique_users = [];
            const check_names = new Set();

            for (const users of this.selected_users) {
                if (!check_names.has(users.first_name)) {
                    unique_users.push(users);
                    check_names.add(users.first_name);
                }
            }
            const users_slug = unique_users.map(users => users.first_name);
            this.selected_users = unique_users;
            this.query.filter.users = users_slug;

        },

        //---------------------------------------------------------------------

        setFilterSelectedProducts() {

            const products_slug = this.selected_products.map(product => product.slug);
            this.query.filter.products = products_slug;

        },

        //---------------------------------------------------------------------

        watchProducts()
        {
            watch(this.item, (newVal,oldVal) =>
                {
                    const anyDeselected = newVal.products.some(item => !item.is_selected);
                    // Update select_all_product variable
                    this.select_all_product = !anyDeselected;
                },{deep: true}
            )
        },

    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useWhishlistStore, import.meta.hot))
}

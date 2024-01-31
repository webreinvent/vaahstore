import {computed, ref, toRaw, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import moment from "moment-timezone/moment-timezone-utils";

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\ProductMedia';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/productmedias";

let empty_states = {
    query: {
        page: 1,
        rows: 20,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
            status:null,
            product_variation:null,
            date:null,
            media_status:null,
            attributes:null
        },
    },
    action: {
        type: null,
        items: [],
    }
};

export const useProductMediaStore = defineStore({
    id: 'productmedias',
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
        route_prefix: 'productmedias.',
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
        suggestion:null,
        active_products:null,
        active_product_variations:null,
        status_suggestion:null,
        product_variation_suggestion:null,
        product_suggestion:null,
        form_menu_list: [],
        selectedFiles:null,
        selected_dates:[],
        date_null:null,
        prev_list:[],
        current_list:[],
        product_variation:null,
        selected_variation:null,
        selected_media:null,
        product_variation_list:[],

    }),
    product_suggestion_list:null,
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
            }
        },
        //---------------------------------------------------------------------
        async onImageUpload(event){
            this.selectedFiles = event.files;
            this.item.type = Array.from(new Set(this.selectedFiles.map(file => file.type.split('/')[0]))).join(', ');
            if(!this.selectedFiles.length)
            {
                return false;
            }
            let attachment = []
            for(let i=0; i<this.selectedFiles.length; i++){
                attachment.push(this.selectedFiles[i]);
            }
            let formData  = new FormData();
            for(let i=0; i < attachment.length; i++){
                formData.append('images[]',attachment[i]);
            }
            let options = {
                params: formData,
                method: "POST",
                headers: {'content-type': 'multipart/form-data'}
            };
            await vaah().ajax(
                this.ajax_url+'/image/upload',
                this.afterUploadImage,
                options
            );
        },
        //---------------------------------------------------------------------
        afterUploadImage(data, res){
            if(data)
            {
                this.item.images = data;
                this.item.name = data[0].name;
            }
        },
        //---------------------------------------------------------------------
         onRemoveTemplatingFile(productMediaImages,index){
             const removedImage = this.item.images[index];
             const removedType = removedImage ? removedImage.type : null;
             this.item.images.splice(index, 1);
             if (this.item.images.length > 0) {
                 const remainingTypes = Array.from(new Set(this.item.images.map(file => file.type)));
                 this.item.type = remainingTypes.join(', ');
             } else {
                 this.item.type = null;
                 this.item.name=null;
             }
        },
        //---------------------------------------------------------------------
        removeUploadedFile(e){
            const indexName = e.file.name;
            const removedImage = this.item.images.find(file => file.name === indexName);
            const removedType = removedImage ? removedImage.type : null;

            this.item.images = this.item.images.filter(file => file.name !== indexName);
            if (this.item.images.length > 0) {
                const remainingTypes = Array.from(new Set(this.item.images.map(file => file.type)));
                this.item.type = remainingTypes.join(', ');
            } else {
                this.item.type = null;
                this.item.name=null;
            }
            this.getItem(this.route.params.id);
        },
        //---------------------------------------------------------------------

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

            if (this.query.filter.product_variation) this.setVariationsAfterPageRefresh();
            if (this.query.filter.type) this.setMediaAfterPageRefresh();

            const { filter } = route.query;
            if (filter && filter.date) {
                this.selected_dates = filter.date.join(' - ');
            }
        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {
            switch(route_name)
            {
                case 'productmedias.index':
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
        async watchRoutes(route)
        {
            //watch routes
            this.watch_stopper = watch(route, async(newVal,oldVal) =>
                {

                    if(this.watch_stopper && !newVal.name.startsWith(this.route_prefix)){
                        this.watch_stopper();

                        return false;
                    }

                    this.route = newVal;

                    if(newVal.params.id){
                       await this.getItem(newVal.params.id);
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
        setStatus(event){
            let status = toRaw(event.value);
            this.item.taxonomy_id_product_media_status = status.id;
        },
        //---------------------------------------------------------------------
        setMediaStatus(){
            if(this.item.is_active == '1'){
                let approved_status = this.status_suggestion.find((item) => item.name === "Approved");
                this.item.status =approved_status;
                this.item.taxonomy_id_product_media_status = approved_status.id;
            }else {
                let pending_status = this.status_suggestion.find((item) => item.name === "Rejected");
                this.item.status =pending_status;
                this.item.taxonomy_id_product_media_status = pending_status.id;
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
                this.status = data.status;
                this.active_products = data.active_products;
                this.active_product_variations = data.active_product_variations;
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
                const images=data.images;

                this.item.product_variation=data.listed_variation;
                if (images.length > 0) {
                    this.item.type=data.type;
                } else {
                    this.item.type= '';
                }
            }else{
                this.$router.push({name: 'productmedias.index'});
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
                this.removeFile();
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
        async formActionAfter (data)
        {
            switch (this.form.action)
            {
                case 'create-and-new':
                case 'save-and-new':
                    this.item.id = null;
                    await this.getFormMenu();
                    this.$router.push({name: 'productmedias.form'})
                    this.setActiveItemAsEmpty();
                    break;
                case 'create-and-close':
                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'productmedias.index'});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    // this.item.images = null;
                    // this.item.product_media_images = null;
                    this.item.product_variation=data.listed_variation;
                    this.$router.push({name: 'productmedias.form'})
                    await this.getFormMenu();
                    break;
                case 'trash':
                    vaah().toastSuccess(['Action Was Successful']);
                    break;
                case 'restore':
                    vaah().toastSuccess(['Action Was Successful']);
                    break;
                case 'save':
                    if (data) {
                        this.item = data;
                        this.item.product_variation=data.listed_variation;
                    }
                    break;
                case 'delete':
                    this.item = null;
                    this.toList();
                    break;
            }
        },
        //---------------------------------------------------------------------
        removeFile(){
            if (!this.selectedFiles) {
                this.selectedFiles = [];
            }
            this.selectedFiles.splice(0, this.selectedFiles.length);
        },


        //---------------------------------------------------------------------
        async toggleIsActive(item)
        {
            if(item.is_active)
            {
                await this.itemAction('activate', item);
                vaah().toastSuccess(['Action Was Successful']);
            } else{
                await this.itemAction('deactivate', item);
                vaah().toastSuccess(['Action Was Successful']);
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
        async getFormInputsAfter (data, res) {

            if (data) {
                if (data.fill.product) {
                    this.item.product_variation=data.fill.listed_variation;
                }
                if (data.fill.images) {
                    this.item.images = [data.fill.images];
                    this.item.type = data.fill.images.type;
                }

                let self = this;
                Object.keys(data.fill).forEach(function(key) {
                    if (key !== 'images' && key!== 'type') {
                        self.item[key] = data.fill[key];
                    }
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
        confirmRestoreAll()
        {
            this.action.type = 'restore-all';
            vaah().confirmDialogRestore(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmTrashAll()
        {
            this.action.type='trash-all';
            vaah().confirmDialogTrash(this.listAction);
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
             this.selected_variation = this.selected_media = null;
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
            this.$router.push({name: 'productmedias.index'})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'productmedias.index'})
        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.item.vh_st_product_id=null;
            this.item.is_active=1;
            this.$router.push({name: 'productmedias.form'})
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            this.item = vaah().clone(item);
            this.$router.push({name: 'productmedias.view', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        toEdit(item)
        {
            this.item = item;
            this.$router.push({name: 'productmedias.form', params:{id:item.id}})
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

        setDateRange() {
            if (!this.selected_dates) return false;

            const dates = this.selected_dates
                .filter(selected_date => selected_date)
                .map(selected_date => moment(selected_date).format('YYYY-MM-DD'));

            if (dates.length === 2) {
                this.query.filter.date = dates;
            }
        },


        async addProduct(event){
            if (event && event.value) {
                let product = toRaw(event.value);
                this.item.vh_st_product_id = product.id;
                this.item.product_variation = null;
            }
        },

        //---------------------------------------------------------------------


        //---------------------------------------------------------------------
        async searchVariation(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/variation',
                this.searchVariationAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchVariationAfter(data,res) {
            if(data)
            {
                this.variation_suggestion = data;
            }
        },

        addVariation() {
            const uniqueVariation = Array.from(new Set(this.selected_variation.map(v => v.name)));
            this.selected_variation = uniqueVariation.map(name => this.selected_variation.find(v => v.name === name));
            this.query.filter.product_variation = this.selected_variation.map(v => v.slug);
        },

        async setVariationsAfterPageRefresh()
        {

            let query = {
                filter: {
                    product_variation: this.query.filter.product_variation,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/variations-using-slug',
                this.setVariationsPageRefreshAfter,
                options
            );


        },

        //---------------------------------------------------------------------
        setVariationsPageRefreshAfter(data, res) {

            if (data) {
                this.selected_variation= data;
            }
        },
        //---------------------------------------------------------------------


        async searchMediaType(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/media',
                this.searchMediaTypeAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        searchMediaTypeAfter(data, res) {
            if (data) {
                let unique_media_type = Array.from(new Set(data.map(item => item.type)));
                this.media_suggestion = unique_media_type.map(name => {
                    let matching_type = data.filter(item => item.type === name);
                    return matching_type[0];
                });
            }
        },


        addMedia() {
            const uniqueMediaType = Array.from(new Set(this.selected_media.map(media => media.type)));
            this.selected_media = uniqueMediaType.map(type => this.selected_media.find(media => media.type === type));
            this.query.filter.type = uniqueMediaType;
        },

        async setMediaAfterPageRefresh()
        {

            let query = {
                filter: {
                    type: this.query.filter.type,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/media-type',
                this.setMediaPageRefreshAfter,
                options
            );


        },

        //---------------------------------------------------------------------
        setMediaPageRefreshAfter(data, res) {

            if (data) {
                this.selected_media= data;
            }
        },

        async searchVariationOfProduct(event) {
            const query = {
                q:event.query,
                id:this.item.vh_st_product_id
            }
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
                this.product_variation_list = data;
                if (data && this.item.product_variation) {
                    this.product_variation_list = data.filter((item) => {
                        return !this.item.product_variation.some((activeItem) => {
                            return activeItem.id === item.id;
                        });
                    });
                }
            }
        },


    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useProductMediaStore, import.meta.hot))
}

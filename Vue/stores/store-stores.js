import {toRaw, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import dayjs from 'dayjs';
import dayjsPluginUTC from 'dayjs-plugin-utc'

dayjs.extend(dayjsPluginUTC)

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Store';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/stores";

let empty_states = {
    query: {
        page: null,
        rows: null,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
            status: null,
            is_multi_language: null,
            is_multi_currency: null,
            is_default: null,
            store_ids: null,
            is_multi_vendor: null,
            currencies: null,
            date: null,
        },
    },
    action: {
        type: null,
        items: [],
    }
};

export const useStoreStore = defineStore({
    id: 'stores',
    state: () => ({
        base_url: base_url,
        ajax_url: ajax_url,
        model: model_namespace,
        assets_is_fetching: true,
        app: null,
        status_option: null,
        showCurrencyDefault: false,
        currencies_list: null,
        status_suggestion_list: null,
        currency_suggestion_list: null,
        language_suggestion_list: null,
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
            delay_timer: 0 // time delay in milliseconds
        },
        route: null,
        watch_stopper: null,
        route_prefix: 'stores.',
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
        currency_list: null,
        selected_dates: null,
        prev_list:[],
        current_list:[],
        first_element: null,
        message:null,
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
        //---------------------------------------------------------------------
        async onLoad(route) {
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

        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name) {
            this.view = 'list';
            if(route_name.includes('stores.view')
                || route_name.includes('stores.form')
            ){
                this.view = 'list-and-item';
            }

            if(route_name.includes('stores.filters')) {
                this.view = 'list-and-filters';
            }
        },
        //---------------------------------------------------------------------
        async updateQueryFromUrl(route) {
            if (route.query) {
                if (Object.keys(route.query).length > 0) {
                    for (let key in route.query) {
                        this.query[key] = route.query[key]
                    }
                    this.countFilters(route.query);
                }
            }
        },
        //---------------------------------------------------------------------
        watchRoutes(route) {
            //watch routes
            this.watch_stopper = watch(route, (newVal, oldVal) => {

                    if (this.watch_stopper && !newVal.name.startsWith(this.route_prefix)) {
                        this.watch_stopper();

                        return false;
                    }

                    this.route = newVal;

                    if (newVal.params.id) {
                        this.getItem(newVal.params.id);
                    }
                    this.setViewAndWidth(newVal.name);

                }, {deep: true}
            )
        },
        //---------------------------------------------------------------------
        watchStates() {
            watch(this.query.filter, (newVal, oldVal) => {
                this.delayedSearch();
            }, {deep: true});
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

        watchItem() {
            if (this.item) {
                watch(() => this.item.name, (newVal, oldVal) => {
                        if (newVal && newVal !== "") {
                            this.item.name = vaah().capitalising(newVal);
                            this.item.slug = vaah().strToSlug(newVal);
                        }
                        if (newVal == "") {
                            this.item.slug = "";
                        }
                        ;
                    }, {deep: true}
                )

                watch(() => this.item.currencies, (newVal, oldVal) => {
                        let flag = 1;
                        if (this.item.currency_default && this.item.currency_default.length > 1) {
                            this.item.currencies.forEach((value) => {
                                if (this.item.currency_default.code == value.code) {
                                    flag = 0;
                                }
                            })
                            if (flag == 1) {
                                this.item.currency_default = null;
                            }
                        }
                    }, {deep: true}
                )
                watch(() => this.item.languages, (newVal, oldVal) => {
                        let flag = 1;
                        if (this.item.language_default && this.item.language_default.length > 1) {
                            this.item.languages.forEach((value) => {
                                if (this.item.language_default.name == value.name) {
                                    flag = 0;
                                }
                            })
                            if (flag == 1) {
                                this.item.language_default = null;
                            }
                        }
                    }, {deep: true}
                )
            }
            if (this.form_menu_list.length === 0) {
                this.getFormMenu();
            }
        },
        //---------------------------------------------------------------------
        async getAssets() {

            if (this.assets_is_fetching === true) {
                this.assets_is_fetching = false;

                await vaah().ajax(
                    this.ajax_url + '/assets',
                    this.afterGetAssets,
                );
            }
        },
        //---------------------------------------------------------------------
        afterGetAssets(data, res) {
            if (data) {
                this.assets = data;
                this.status_option = data.taxonomy.store_status;
                this.currencies_list = data.currencies;
                this.languages_list = data.languages;
                if (data.rows) {

                    data.rows = this.query.rows
                }

                if (this.route.params && !this.route.params.id) {
                    this.setActiveItemAsEmpty();
                }

            }
        },
        //---------------------------------------------------------------------


        searchCurrencies(event) {
            const query = event.query.toLowerCase();
            this.currency_suggestion_list = this.currencies_list.filter(item => {
                return item.name.toLowerCase().includes(query) &&
                    (!this.item.default_currency || item.name !== this.item.default_currency.name);
            });
        },

        //---------------------------------------------------------------------



        //---------------------------------------------------------------------

        async addCurrencies() {
            const unique_currencies = [];
            const check_names = new Set();

            for (const currency of this.item.currencies) {
                if (!check_names.has(currency.name)) {
                    unique_currencies.push(currency);
                    check_names.add(currency.name);
                }
                else {
                    this.item.currencies = unique_currencies;
                    vaah().toastErrors(['This Currency is already added']);
                    return false;
                }
            }
            if(unique_currencies.length == 0)
            {
                await this.getItem(this.route.params.id);
                return false;
            }
            this.item.currencies = unique_currencies;
            if ( this.route.name === 'view' && this.item.id ) {
                await this.itemAction('save');
            }
        },

        //---------------------------------------------------------------------

        async saveCurrencies() {
            const unique_currencies = [];
            const check_names = new Set();

            for (const currency of this.item.currencies) {
                if (!check_names.has(currency.name)) {
                    unique_currencies.push(currency);
                    check_names.add(currency.name);
                } else {
                    this.item.currencies = unique_currencies;
                    vaah().toastErrors(['This Currency is already added']);
                    return false;
                }
            }
            if (unique_currencies.length == 0) {
                vaah().toastErrors(['Currency is required when Is Multi Currency is true']);
                await this.getItem(this.route.params.id);
                return false;
            }
            this.item.currencies = unique_currencies;
            await this.itemAction('save');
        },

        //---------------------------------------------------------------------

        addCurrenciesFilter() {


            const unique_currencies = [];
            const check_names = new Set();

            for (const currency of this.currency_list) {
                if (!check_names.has(currency.name)) {
                    unique_currencies.push(currency);
                    check_names.add(currency.name);
                }
            }
            this.query.filter.currencies = unique_currencies.slug;

        },

        //---------------------------------------------------------------------

        addLanguages() {

            const unique_languages = [];
            const check_names = new Set();

            for (const language of this.item.languages) {
                if (!check_names.has(language.name)) {
                    unique_languages.push(language);
                    check_names.add(language.name);
                }
            }
            this.item.languages = unique_languages;
        },

        //---------------------------------------------------------------------

        async saveLanguages() {

            const unique_languages = [];
            const check_names = new Set();

            for (const language of this.item.languages) {
                if (!check_names.has(language.name)) {
                    unique_languages.push(language);
                    check_names.add(language.name);
                } else {
                    this.item.languages = unique_languages;
                    vaah().toastErrors(['This Language is already added']);
                    return false;
                }
            }
            if (unique_languages.length === 0) {
                vaah().toastErrors(['Language is required when Is Multi Language is true']);
                await this.getItem(this.route.params.id);
                return false;
            }
            this.item.languages = unique_languages;
            await this.itemAction('save');
        },

        //---------------------------------------------------------------------



        searchLanguages(event) {
            const query = event.query.toLowerCase();
            this.language_suggestion_list = this.languages_list.filter(item => {
                return item.name.toLowerCase().includes(query)&&
                    (!this.item.default_language || item.name !== this.item.default_language.name);
            });
        },

        //---------------------------------------------------------------------


        //---------------------------------------------------------------------

        setStatus(event) {
            let status = toRaw(event.value);
            this.item.taxonomy_id_store_status = status.id;
        },

        //---------------------------------------------------------------------

        async reloadPage() {
            await this.getList();
            vaah().toastSuccess(["Page Reloaded"]);
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
        afterGetList: function (data, res) {
            this.message = (res && res.data && res.data.message)
                ? 'There is no default store. Mark a store as default.'
                : null;

            if (data) {
                this.list = data;
                this.first_element = this.query.rows * (this.query.page - 1);
                this.query.rows = data.per_page;
            }
        },
        //---------------------------------------------------------------------

        searchStatus(event) {

            this.status_suggestion_list = this.status_option.filter((department) => {
                return department.name.toLowerCase().startsWith(event.query.toLowerCase());
            });
        },

        //---------------------------------------------------------------------

        searchCurrencyDefault(event) {
            setTimeout(() => {
                if (!event.query.trim().length) {
                    this.currency_default_suggestion_list = this.item.currencies;
                } else {
                    this.currency_default_suggestion_list = this.item.currencies.filter((department) => {
                        return department.name.toLowerCase().startsWith(event.query.toLowerCase());
                    });
                }
            }, 250);
        },
        //---------------------------------------------------------------------
        searchCurrency(event) {
            setTimeout(() => {
                if (!event.query.trim().length) {
                    this.currency_suggestion_list = this.currencies_list;
                } else {
                    this.currency_suggestion_list = this.currencies.filter((department) => {
                        return department.name.toLowerCase().startsWith(event.query.toLowerCase());
                    });
                }
            }, 250);
        },
        //---------------------------------------------------------------------

        async getItem(id) {
            if (id) {
                await vaah().ajax(
                    ajax_url + '/' + id,
                    this.getItemAfter
                );
            }
        },
        //---------------------------------------------------------------------
        async getItemAfter(data, res) {
            if (data) {
                this.item = data;
                this.item.currency_default = data.currency_default;
                let temp = data.currency_default;
            } else {
                this.$router.push({name: 'store.index'});
            }
            await this.getItemMenu();
            await this.getFormMenu();
        },
        //---------------------------------------------------------------------
        isListActionValid() {

            if (!this.action.type) {
                vaah().toastErrors(['Select an action type']);
                return false;
            }

            if (this.action.items.length < 1) {
                vaah().toastErrors(['Select records']);
                return false;
            }

            return true;
        },
        //---------------------------------------------------------------------
        async updateList(type = null) {

            if (!type && this.action.type) {
                type = this.action.type;
            } else {
                this.action.type = type;
            }

            if (!this.isListActionValid()) {
                return false;
            }


            let method = 'PUT';

            switch (type) {
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
            if (data) {
                this.action = vaah().clone(this.empty_action);
                await this.getList();
            }
        },
        //---------------------------------------------------------------------
        async listAction(type = null) {

            if (!type && this.action.type) {
                type = this.action.type;
            } else {
                this.action.type = type;
            }

            let url = this.ajax_url + '/action/' + type
            let method = 'PUT';

            switch (type) {
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
        async itemAction(type, item = null) {
            if (!item) {
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
            switch (type) {
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
                    ajax_url += '/' + item.id
                    break;
                /**
                 * Delete a record, hence method is `DELETE`
                 * and no need to send entire `item` object
                 * https://docs.vaah.dev/guide/laravel.html#delete-a-record-hard-deleted
                 */
                case 'delete':
                    options.method = 'DELETE';
                    ajax_url += '/' + item.id
                    break;
                /**
                 * Update a record's one column or very few columns,
                 * hence the method is `PATCH`
                 * https://docs.vaah.dev/guide/laravel.html#update-a-record-update-soft-delete-status-change-etc
                 */
                default:
                    options.method = 'PATCH';
                    ajax_url += '/' + item.id + '/action/' + type;
                    break;
            }

            await vaah().ajax(
                ajax_url,
                this.itemActionAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        async itemActionAfter(data, res) {
            if (data) {
                this.item = data;
                await this.getList();
                this.prev_list =this.list.data;
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
        async formActionAfter(data) {
            switch (this.form.action) {
                case 'create-and-new':
                case 'save-and-new':
                    this.setActiveItemAsEmpty();
                    await this.getFormMenu();
                    this.$router.push({name: 'stores.form'})
                    break;
                case 'create-and-close':
                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'stores.index'});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    this.$router.push({name: 'stores.form'})
                    await this.getFormMenu();
                    break;
                case 'trash':
                case 'restore':
                    // this.item = data;
                    vaah().toastSuccess(['Action was successful']);
                    break;
                case 'save':
                    this.item = data;
                    await this.getItem(data.id)
                    break;
                case 'delete':
                    this.item = null;
                    this.toList();
                    break;
            }
        },
        //---------------------------------------------------------------------
        async toggleIsActive(item) {
            if (item.is_active) {
                await this.itemAction('deactivate', item);

            } else {
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
        async reload() {
            await this.getAssets();
            await this.getList();
        },
        //---------------------------------------------------------------------
        async getFormInputs() {
            let params = {
                model_namespace: this.model,
                except: this.assets.fillable.except,
            };

            let url = this.ajax_url + '/fill';

            await vaah().ajax(
                url,
                this.getFormInputsAfter,
            );
        },
        //---------------------------------------------------------------------
        getFormInputsAfter: function (data, res) {
            if (data) {
                let self = this;
                Object.keys(data.fill).forEach(function (key) {
                    self.item[key] = data.fill[key];
                });
            }
        },

        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
        onItemSelection(items) {
            this.action.items = items;
        },
        //---------------------------------------------------------------------
        setActiveItemAsEmpty() {
            this.item = vaah().clone(this.assets.empty_item);
        },
        //---------------------------------------------------------------------
        confirmDelete() {
            if (this.action.items.length < 1) {
                vaah().toastErrors(['Select a record']);
                return false;
            }
            this.action.type = 'delete';
            vaah().confirmDialogDelete(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmDeleteAll() {
            this.action.type = 'delete-all';
            vaah().confirmDialogDeleteAll(this.listAction);
        },
        //---------------------------------------------------------------------

        confirmActivateAll() {
            this.action.type = 'activate-all';
            vaah().confirmDialogActivate(this.listAction);
        },

        //---------------------------------------------------------------------

        confirmDeactivateAll() {
            this.action.type = 'deactivate-all';
            vaah().confirmDialogDeactivate(this.listAction);
        },

        //---------------------------------------------------------------------

        confirmTrashAll() {
            this.action.type = 'trash-all';
            vaah().confirmDialogTrash(this.listAction);
        },

        //---------------------------------------------------------------------

        confirmRestoreAll() {
            this.action.type = 'restore-all';
            vaah().confirmDialogRestore(this.listAction);
        },

        //---------------------------------------------------------------------

        async delayedSearch() {
            let self = this;
            this.query.page = 1;
            this.action.items = [];
            clearTimeout(this.search.delay_timer);
            this.search.delay_timer = setTimeout(async function () {
                await self.updateUrlQueryString(self.query);
                await self.getList();
            }, this.search.delay_time);
        },
        //---------------------------------------------------------------------
        async updateUrlQueryString(query) {
            //remove reactivity from source object
            query = vaah().clone(query)

            //create query string
            let query_string = qs.stringify(query, {
                skipNulls: true,
            });
            let query_object = qs.parse(query_string);

            if (query_object.filter) {
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
        countFilters: function (query) {
            this.count_filters = 0;
            if (query && query.filter) {
                let filter = vaah().cleanObject(query.filter);
                this.count_filters = Object.keys(filter).length;
            }
        },
        //---------------------------------------------------------------------
        async clearSearch() {
            this.query.filter.q = null;
            await this.updateUrlQueryString(this.query);
            await this.getList();
        },
        //---------------------------------------------------------------------
        async resetQuery() {
            //reset query strings
            await this.resetQueryString();
            this.selected_dates = null;
            vaah().toastSuccess(['Action was successful']);

            //reload page list
            await this.getList();
        },
        //---------------------------------------------------------------------
        async resetQueryString() {
            for (let key in this.query.filter) {
                this.query.filter[key] = null;
            }
            await this.updateUrlQueryString(this.query);
        },
        //---------------------------------------------------------------------
        closeForm() {
            this.$router.push({name: 'stores.index'})
        },
        //---------------------------------------------------------------------
        toList() {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'stores.index'})
        },
        //---------------------------------------------------------------------
        toForm() {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.$router.push({name: 'stores.form'})
        },
        //---------------------------------------------------------------------
        toView(item) {
            this.item = vaah().clone(item);
            this.$router.push({name: 'stores.view', params: {id: item.id}})
        },
        //---------------------------------------------------------------------
        toEdit(item) {
            this.item = item;
            this.$router.push({name: 'stores.form', params: {id: item.id}})
        },
        //---------------------------------------------------------------------
        isListView() {
            return this.view === 'list';
        },
        //---------------------------------------------------------------------
        getIdWidth() {
            let width = 50;

            if (this.list && this.list.total) {
                let chrs = this.list.total.toString();
                chrs = chrs.length;
                width = chrs * 40;
            }

            return width + 'px';
        },
        //---------------------------------------------------------------------
        getActionWidth() {
            let width = 100;
            if (!this.isListView()) {
                width = 80;
            }
            return width + 'px';
        },
        //---------------------------------------------------------------------
        getActionLabel() {
            let text = null;
            if (this.isListView()) {
                text = 'Actions';
            }

            return text;
        },

        //---------------------------------------------------------------------

        async getListSelectedMenu() {
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
        getListBulkMenu() {
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
        getItemMenu() {
            let item_menu = [];

            if (this.item && this.item.deleted_at) {

                item_menu.push({
                    label: 'Restore',
                    icon: 'pi pi-refresh',
                    command: () => {
                        this.itemAction('restore');
                    }
                });
            }

            if (this.item && this.item.id && !this.item.deleted_at) {
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

        //---------------------------------------------------------------------
        async getListCreateMenu() {
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
        confirmDeleteItem() {
            this.form.type = 'delete';
            vaah().confirmDialogDelete(this.confirmDeleteItemAfter);
        },
        //---------------------------------------------------------------------
        confirmDeleteItemAfter() {
            this.itemAction('delete', this.item);
        },
        //---------------------------------------------------------------------
        async getFormMenu() {
            let form_menu = [];

            if (this.item && this.item.id) {
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
                        label: is_deleted ? 'Restore' : 'Trash',
                        icon: is_deleted ? 'pi pi-refresh' : 'pi pi-times',
                        command: () => {
                            this.itemAction(is_deleted ? 'restore' : 'trash');
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

            } else {
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
                            vaah().toastSuccess(['Action was successful']);

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
        storeids(ids) {
            let query = {
                filter: {
                    store_ids: ids,
                    trashed: 'include'
                }
            };
            this.$router.push({ name: 'stores.index', query: query });

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
        //---------------------------------------------------------------------
    }
});


// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useStoreStore, import.meta.hot))
}

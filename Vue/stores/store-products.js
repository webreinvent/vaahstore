import {toRaw, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import {useRootStore} from "./root";
import dayjs from 'dayjs';
import dayjsPluginUTC from 'dayjs-plugin-utc'
dayjs.extend(dayjsPluginUTC)

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Product';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/store/products";

let empty_states = {
    query: {
        page: null,
        rows: null,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
            product_variations : null,
            vendors : null,
            stores : null,
            brands : null,
            product_types : null,
            quantity : null,
            status:null,
            min_quantity:null,
            max_quantity:null,
            export_menu :[],
        },
        include: {
            stores: [],
        },
    },
    action: {
        type: null,
        items: [],
    }
};

export const useProductStore = defineStore({
    id: 'products',
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
        active_brands:null,
        active_stores:null,
        selected_vendor:null,
        select_all_vendor:false,
        select_all_attribute : false,
        user_error_message: [],
        empty_variation_item : null,
        variation_item: {
            attribute_option_type: 1,
            product_attributes: [],
            selected_attribute: null,
            attribute_options: null,
            show_create_form: false,
            select_all_variation: false,
            create_variation_data: null,
            new_variation: [],
        },
        status:null,
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
        route_prefix: 'products.',
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
        variation_selected_menu: [],
        vendor_selected_menu: [],
        attribute_selected_menu : [],
        suggestion:null,
        product_status:null,
        filtered_status:null,
        filtered_stores:null,
        filtered_brands:null,
        type_suggestion:null,
        name:null,
        list_selected_menu: [],
        list_bulk_menu: [],
        list_create_menu: [],
        item_menu_list: [],
        item_menu_state: null,
        form_menu_list: [],
        searched_stored : null,
        selected_dates : null,
        filtered_product_variations : null,
        selected_product_variations : null,
        selected_vendors : null,
        filtered_vendors : null,
        filter_selected_store : null,
        filter_selected_brands : null,
        display_seo_modal : false,
        seo_meta_value : null,
        quantity:
            {
                from :null,
                to :null,
            },
        prev_list:[],
        current_list:[],
        vendor_suggestion : null,
        min_quantity : null,
        max_quantity : null,
        filter_category:null,
        product_category_filter:null,
        add_to_cart:false,
        show_cart_msg:false,
        product_name:null,
        user_suggestions:null,
        active_cart_user_name:null,
        product_detail:[],
        active_user:null,
        total_cart_product:0,
        top_selling_products:null,
        top_selling_brands:null,
        top_selling_categories:null,
        quick_filter_menu:[],filter_all: null,
        column_to_export: {
            columns: [],
        },
        is_custom_meta_export:false,
        window_width: 0,
        screen_size: null,
        selected_store_at_list: null,
        default_store: null,
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
        searchTaxonomyProduct(event) {

            this.type_suggestion = this.types.filter((product) => {
                return product.name.toLowerCase().startsWith(event.query.toLowerCase());
            });
        },

        //---------------------------------------------------------------------

        async searchBrand(event) {
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
                this.ajax_url + '/search/brand',
                this.searchBrandAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        searchBrandAfter(data, res) {

            if (data) {
                this.filtered_brands = data;
            }
        },

        //--------------------------------------------------------------------

        searchStatus(event) {

            this.filtered_status = this.product_status.filter((status) => {
                return status.name.toLowerCase().startsWith(event.query.toLowerCase());
            });
        },

        //---------------------------------------------------------------------

        async searchProductVariation(event) {
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
                this.ajax_url + '/search/product-variation',
                this.searchProductVariationAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        searchProductVariationAfter(data, res) {

            if (data) {
                this.filtered_product_variations = data;
            }
        },

        //---------------------------------------------------------------------


        async searchStore(event) {
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
                this.ajax_url + '/search/store',
                this.searchStoreAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        searchStoreAfter(data, res) {

            if (data) {
                this.filtered_stores = data;
            }
        },

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

            /**
             * Update query state with the query parameters of url
             */
            this.updateQueryFromUrl(route);
            await this.updateUrlQueryString(this.query);
            if (this.route.query.filter && this.route.query.filter.category) {
                await this.setCategoryInFilterAfterRefresh();
            }
            if(this.query.filter.vendors)
            {
                this.setVendorsAfterPageRefresh();
            }
            if(this.query.filter.brands)
            {
                this.setBrandsAfterPageRefresh();
            }
            if(this.query.filter.product_variations)
            {
                this.setProductVariationsAfterPageRefresh();
            }
            if(this.query.filter.stores)
            {
                this.setStoresAfterPageRefresh();
            }
            if(this.query.filter.product_types)
            {
                this.setProductTypeAfterPageRefresh();
            }

            if (route.query && route.query.filter && route.query.filter.date) {
                this.selected_dates = route.query.filter.date;
                this.selected_dates = this.selected_dates.join(' - ');
            }

            if(route.query && route.query.filter && route.query.filter.quantity)
            {
                this.quantity=route.query.filter.quantity;
                this.min_quantity = this.quantity[0];
                this.max_quantity = this.quantity[1];
            }

        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name) {

            this.view = 'list';

            if(route_name.includes('products.view')
                || route_name.includes('products.form')
                || route_name.includes('products.variation')
                || route_name.includes('products.vendor')
            ){
                this.view = 'list-and-item';
            }

            if(route_name.includes('products.filters')){
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

                    if (this.watch_stopper && !newVal.name.includes(this.route_prefix)) {
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
                }, {deep: true}
            )
        },
        //---------------------------------------------------------------------
        watchItem() {
            if (this.item) {
                watch(() => this.item.name, (newVal, oldVal) => {
                        if (newVal && newVal !== "") {
                            this.item.name = newVal;
                            this.item.slug = vaah().strToSlug(newVal);
                        }else{
                            this.item.slug="";
                        }
                    }, {deep: true}
                )
                watch(() => this.variation_item.attribute_option_type, (newVal, oldVal) => {
                        if (newVal != oldVal) {
                            this.getAttributeList();
                        }
                    }, {deep: true}
                )

            }
            if (this.form_menu_list.length === 0) {
                this.getFormMenu();
            }
        },

        //---------------------------------------------------------------------

        watchQuantity()
        {
            watch(() => [this.quantity?.from, this.quantity?.to], ([min, max]) => {
                // Check if both from and too quantity are entered

                if (min !== null && min !== '' && max !== null && max !== '') {
                        this.query.filter.quantity = this.quantity;
                }

            });

        },

        //---------------------------------------------------------------------
        setStore(event) {
            let store = toRaw(event.value);
            this.item.vh_st_store_id = store.id;
        },

        //---------------------------------------------------------------------

        setFilterStore(event) {
            const unique_store = [];
            const check_names = new Set();

            for (const store of this.filter_selected_store) {
                if (!check_names.has(store.name)) {
                    unique_store.push(store);
                    check_names.add(store.name);
                }
            }
            const store_slugs = unique_store.map(type => type.slug);
            this.filter_selected_store = unique_store;
            this.query.filter.stores = store_slugs;
        },

        //---------------------------------------------------------------------
        updateQuantityFilter(event) {
            this.query.filter.quantity = event.value;
        },
        //---------------------------------------------------------------------
        setBrand(event) {
            let brand = toRaw(event.value);
            this.item.vh_st_brand_id = null;
            if(brand)
            {
                this.item.vh_st_brand_id = brand.id;
            }
        },
        //---------------------------------------------------------------------
        setType(event) {
            let type = toRaw(event.value);
            this.item.taxonomy_id_product_type = type.id;
        },

        //---------------------------------------------------------------------

        addFilterProductType(event) {
            const unique_product_type = [];
            const check_names = new Set();

            for (const product_type of this.filter_selected_product_type) {
                if (!check_names.has(product_type.name)) {
                    unique_product_type.push(product_type);
                    check_names.add(product_type.name);
                }
            }
            const product_type_slugs = unique_product_type.map(type => type.slug);
            this.filter_selected_product_type = unique_product_type;
            this.query.filter.product_types = product_type_slugs;

        },

        //---------------------------------------------------------------------

        setProductStatus(event) {
            let status = toRaw(event.value);
            this.item.taxonomy_id_product_status = status.id;
        },

        //---------------------------------------------------------------------

        addVendor() {
            if (this.selected_vendor != null) {
                let exist = 0;
                this.item.vendors.forEach((item) => {
                    if (item['id'] == this.selected_vendor['id']) {
                        exist = 1;
                    }
                })
                if (exist == 0) {
                    this.item.vendors.push({
                        ...this.selected_vendor,
                        is_selected: false,
                        can_update: false,
                        status_notes: null,
                    });
                    this.selected_vendor = null;
                } else {
                    this.showUserErrorMessage(['This vendor is already present'], 4000);
                }

            }
        },
        //---------------------------------------------------------------------
        selectAllVendor() {
            this.item.vendors.forEach((i) => {
                i['is_selected'] = !this.select_all_vendor;
            })
        },
        //---------------------------------------------------------------------

        selectAllAttribute() {
            this.variation_item.product_attributes.forEach((i) => {
                i['is_selected'] = !this.select_all_attribute;
            })
        },

        //---------------------------------------------------------------------

        async removeVendor(attribute) {
            this.item.vendors = this.item.vendors.filter(function (item) {
                return item['id'] != attribute['id']
            })
            this.select_all_vendor = false;
        },

        //---------------------------------------------------------------------
        async removeAllVendor()
        {
            this.item.vendors = [];
            this.select_all_vendor = false;
        },

        //---------------------------------------------------------------------

        async removeAllProductVariation()
        {
            this.item.product_variations = {};
        },

        //---------------------------------------------------------------------
        async removeAllAttributes()
        {
            this.variation_item.product_attributes = [];
        },

        //---------------------------------------------------------------------
        async bulkRemoveAttribute() {
            let selected_attribute = this.variation_item.product_attributes.filter(attribute => attribute.is_selected);
                let temp = null;
                this.select_all_attribute = false;
                temp = this.variation_item.product_attributes.filter((item) => {
                    return item['is_selected'] === false;
                });

            if (selected_attribute.length === 0) {
                vaah().toastErrors(['Select a Attribute']);
                return false;
            }
            else if (temp.length === this.variation_item.product_attributes.length) {
                    this.variation_item.product_attributes = [];
                }
                else {
                    this.variation_item.product_attributes = temp;
                }

        },
        //---------------------------------------------------------------------

        async bulkRemoveVendor() {

            let selected_vendors = this.item.vendors.filter(vendor => vendor.is_selected);
            let temp = null;
            this.select_all_vendor = false;
            temp = this.item.vendors.filter((item) => {
                return item['is_selected'] === false;
            });

            if (selected_vendors.length === 0) {
                vaah().toastErrors(['Select a vendor']);
                return false;
            }

            else if (temp.length === this.item.vendors.length) {
                alert("hello");
                this.item.vendors = [];
            }
            else {

                this.item.vendors = temp;
            }

        },

        //---------------------------------------------------------------------

        async bulkRemoveVendorAfter() {
            await this.getList();
            this.item.vendors = [];
        },

        //---------------------------------------------------------------------

        async getAttributeList(callback= null, get_attribute_from_group = false) {

            let params = {
                attribute_type: this.variation_item.attribute_option_type == 0 ? 'attribute' : 'attribute_group',
                product_id: this.item.id,
                selected_attribute: this.variation_item.selected_attribute,
                get_attribute_from_group: get_attribute_from_group
            }

            let options = {
                params: params,
                method: "POST"
            };

            await vaah().ajax(
                this.ajax_url+'/getAttributeList',
                callback ?? this.afterGetAttributeList,
                options
            );

        },
        //---------------------------------------------------------------------
        afterGetAttributeList(data, res){

            this.variation_item.attribute_options = data;

        },
        //---------------------------------------------------------------------
        async getAttributeValues(callback, method = 'generate') {

            let params = {
                attribute: this.variation_item.product_attributes,
                product_id: this.item.id,
                method: method
            }

            let options = {
                params: params,
                method: "POST"
            };

            await vaah().ajax(
                this.ajax_url+'/getAttributeValue',
                callback,
                options
            );

        },
        //---------------------------------------------------------------------
        getUnique(data, key){
            return [...new Map(data.map(x => [key(x), x])).values()];
        },
        //---------------------------------------------------------------------
        addNewProductAttribute(data = null){

            this.select_all_attribute = null;
            if (this.variation_item.selected_attribute && this.variation_item.attribute_option_type == 0){
                let  new_attribute = {
                    ...this.variation_item.selected_attribute,
                    is_selected: false
                };
                if (this.variation_item.product_attributes.some(attr => attr.id === new_attribute.id)) {
                    vaah().toastErrors(['Attribute already added']);
                    return false;
                }
                this.variation_item.product_attributes.push(new_attribute);
                this.variation_item.selected_attribute = null;
                this.variation_item.product_attributes = this.getUnique(this.variation_item.product_attributes, it=> it.id);
            }
            else if(this.variation_item.selected_attribute && this.variation_item.attribute_option_type == 1){
                this.getAttributeList(this.addProductAttributeFromGroup, true)
            }
        },
        //---------------------------------------------------------------------
        addProductAttributeFromGroup(data){
            data.forEach((i)=>{
                i.is_selected = false;
                this.variation_item.product_attributes.push(i);
            })
            this.variation_item.product_attributes = this.getUnique(this.variation_item.product_attributes, it=> it.id);
        },
        //---------------------------------------------------------------------
        removeProductAttribute(attribute){
            this.variation_item.product_attributes = this.variation_item.product_attributes.filter(function(item){ return item.name != attribute.name })
        },
        //---------------------------------------------------------------------

        generateProductVariation(){

            if (this.variation_item.product_attributes && this.variation_item.product_attributes.length > 0){
                this.getAttributeValues(this.afterGenerateVariations, 'generate');
                this.select_all_variation = null;
            }
        },
        //---------------------------------------------------------------------
        afterGenerateVariations(data, res){
            if (data){
                this.item.product_variations = data;
                this.variation_item.show_create_form = false;
                this.variation_item.create_variation_data = [];
            }
        },

        //---------------------------------------------------------------------
        createProductVariation(){
            if (this.variation_item.product_attributes && this.variation_item.product_attributes.length > 0){
                this.getAttributeValues(this.afterCreateVariation, 'create');
            }
        },
        //---------------------------------------------------------------------
        afterCreateVariation(data, res){
            if (data){

                this.variation_item.create_variation_data = data;
                this.variation_item.show_create_form = true;
                this.variation_item.new_variation = [];
            }
        },
        //---------------------------------------------------------------------
        removeProductVariation(item){
            let item_key = this.getIndexOfArray(this.item.product_variations.structured_variations, item);
            if (item_key >= 0){
                this.item.product_variations.structured_variations.splice(item_key, 1);
            }
        },
        //---------------------------------------------------------------------
        bulkRemoveProductVariation(){

            let selected_variation = this.item.product_variations.structured_variations.filter(variation => variation.is_selected);
            let temp = null;
            this.variation_item.select_all_variation = false;
            temp = this.item.product_variations.structured_variations.filter((item) => {
                return item['is_selected'] === false;
            });

            if (selected_variation.length === 0) {
                vaah().toastErrors(['Select a Variation to remove']);
                return false;
            }
            else if(temp.length === 0)
                {

                    this.item.product_variations = {};
                }

            else {
                this.item.product_variations.structured_variations = temp;
            }


        },
        //---------------------------------------------------------------------
        getIndexOfArray(array, findArray){
            let index = -1;
            array.some((item, i)=>{
                if(JSON.stringify(item) === JSON.stringify(findArray)) {
                    index = i;
                    return true;
                }
            });
            return index;
        },
        //---------------------------------------------------------------------
        selectAllVariation(){
            this.item.product_variations.structured_variations.forEach((i)=>{
                i['is_selected'] = !this.variation_item.select_all_variation;
            })
        },
        //---------------------------------------------------------------------
        setDefault(){
            this.item.product_variations.structured_variations.forEach((variation) => {
                if (variation['is_default'] !== this.variation_item.is_default) {
                    variation['is_default'] = false;
                }
            });
        },
        //---------------------------------------------------------------------
        addNewProductVariation(new_record){

            if (this.variation_item.new_variation
                && Object.keys(this.variation_item.new_variation).length
                > Object.keys(this.variation_item.create_variation_data.all_attribute_name).length){

                if (this.item.product_variations && Object.keys(this.item.product_variations).length > 0){

                    let error_message = [];
                    let variation_match_key = null;
                    this.item.product_variations.structured_variations.forEach((i,k)=>{
                        if (i.variation_name == this.variation_item.new_variation.variation_name.trim()){
                            error_message.push('variation name must be unique');
                        }
                        this.variation_item.create_variation_data.all_attribute_name.forEach((i_new, k_new)=>{
                            if (i[i_new]['value'] == this.variation_item.new_variation[i_new]['value']){
                                if (variation_match_key == k){
                                    error_message.push('variation already exist');
                                }else{
                                    variation_match_key = k;
                                }
                            }
                        });

                        if (variation_match_key != null && Object.keys(this.variation_item.create_variation_data.all_attribute_name).length == 1){
                            error_message.push('variation already exist');
                        }
                    })

                    if(error_message && error_message.length == 0){
                        let new_variation = Object.assign({}, this.variation_item.new_variation);
                        new_variation.is_selected = false;
                        this.item.product_variations.structured_variations.push(new_variation);
                        this.variation_item.create_variation_data = null;
                        this.variation_item.show_create_form = false;

                    }else{
                        this.showUserErrorMessage(error_message, 4000);
                    }

                }else{
                    let temp = {
                        structured_variations: [Object.assign({},this.variation_item.new_variation)],
                        all_attribute_name: this.variation_item.create_variation_data.all_attribute_name
                    };

                    this.item.product_variations = temp;
                    this.variation_item.create_variation_data = null;
                    this.variation_item.show_create_form = false;
                }
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
                this.product_status = data.taxonomy.product_status;
                this.active_brands = data.active_brands;
                this.active_stores = data.active_stores;
                this.types = data.taxonomy.types;
                this.active_vendors = data.active_vendors;
                this.product_vendor_status = data.taxonomy.product_vendor_status;
                this.min_quantity = data.min_quantity;
                this.max_quantity = data.max_quantity;
                this.categories_data=data.category;
                if(this.route.query && this.route.query.filter && this.route.query.filter.quantity)
                {
                    this.min_quantity=this.route.query.filter.quantity[0];
                    this.max_quantity=this.route.query.filter.quantity[1];
                }
                if(data.rows)
                {
                    data.rows = this.query.rows;
                }

                if(this.route.params && !this.route.params.id){
                    this.item = vaah().clone(data.empty_item);
                }


            }
        },
        convertToTreeSelectFormat(data) {
            if (!Array.isArray(data)) {
                data = [data];
            }
            let categories = [];

            data.forEach(category => {
                let categoryItem = {
                    key: category.id,
                    label: category.name,
                    data : category.slug,
                    children: []
                };

                if (category.sub_categories && category.sub_categories.length > 0) {
                    categoryItem.children = this.convertToTreeSelectFormat(category.sub_categories);
                }

                categories.push(categoryItem);
            });

            return categories;
        },

        setParentId() {
            if (this.item.categories) {
                const checkedItem = Object.entries(this.item.categories).find(([key, value]) => value === true);
                if (checkedItem) {
                    this.item.parent_id = checkedItem[0];
                } else {
                    this.item.parent_id = null;
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


        async onStoreSelect(selected_store_at_list) {
            let storeData = selected_store_at_list?.value;

            if (storeData && storeData.slug) {
                this.query = {
                    ...this.query,
                    include: {
                        ...this.query.include,
                        stores: [`${storeData.slug}`]
                    }
                };
            } else {
                this.query = {
                    ...this.query,
                    include: {
                        ...this.query.include,
                        stores: null
                    }
                };
            }
            await this.getList();
        },
        //---------------------------------------------------------------------
        afterGetList: function (data, res)
        {
            if (res?.data?.active_cart_user) {
                const { active_cart_user: { cart_records, email, vh_st_cart_id } } = res.data;
                this.add_to_cart = false;
                this.show_cart_msg = true;
                this.active_user = res.data.active_cart_user;
                this.total_cart_product = cart_records;
                this.active_cart_user_name = email;
                this.cart_id = vh_st_cart_id;
            } else {
                this.show_cart_msg = false;
            }
            if(data)
            {
                this.list = data;
                this.query.rows=data.per_page;
                this.topSellingProducts(this.selected_store_at_list);
                this.topSellingBrands(this.selected_store_at_list);
                this.topSellingCategories(this.selected_store_at_list);
            }
        },
        viewCart(id){
            this.$router.push({name: 'carts.details',params:{id:id},query:this.query})
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
                if (this.categories_dropdown_data && this.categories_dropdown_data.length > 0) {
                    this.item.categories = this.convertToTreeSelectData(data.product_categories);
                }

            }else{
                this.$router.push({name: 'products.index'});
            }
            await this.getItemMenu();
            await this.getFormMenu();
        },

        convertToTreeSelectData(category) {
            if (category && category.length) {
                return category.reduce((acc, curr) => {
                    const categoryId = curr.id.toString();
                    acc[categoryId] = true;

                    return acc;
                }, {});
            }
            return {};
        },









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

        async attachVendors(item)
        {
            const query = {
                store: item.store,
                vendors: item.vendors
            };
            const options = {
                params: query,
                method: 'post',
            };

            let ajax_url = this.ajax_url;

            ajax_url += '/'+ item.id+ '/vendors';
            await vaah().ajax(
                ajax_url,
                this.attachVendorsAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        async attachVendorsAfter(data, res)
        {
            if(data)
            {
                await this.getList();
                this.toList();

            }
        },

        //---------------------------------------------------------------------

        async saveVariation(id)
        {
            let ajax_url = this.ajax_url;
            let options = {
                method: 'post',
            };
            options.method = 'POST';
            options.params = this.item;
            ajax_url +=  '/'+id+'/variations/generate'
            await vaah().ajax(
                ajax_url,
                this.saveVariationAfter,
                options
            );
        },


        //---------------------------------------------------------------------

        async saveVariationAfter(data, res)
        {
            if(data)
            {
                await this.getList();
                this.toList();

            }
        },

        //---------------------------------------------------------------------
        async removeCategory(type, item=null){

            if(!item)
            {
                item = this.item;
            }

            let ajax_url = this.ajax_url;

            let options = {
                method: 'POST',
                params:item.pivot
            };
            ajax_url += '/category/'+type;
            await vaah().ajax(
                ajax_url,
                this.removeCategoryAfter,
                options
            );
        },
        removeCategoryAfter(data,res){
            if (data){
                this.getList();
                if (this.route.name === 'products.view'){
                    this.getItem(data.product.id);
                }
            }
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

                case 'save-vendor':
                    options.method = 'POST';
                    options.params = item;
                    ajax_url += '/vendor'
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
                this.prev_list =this.list.data;
                this.item = data;
                await this.getList();
                await this.formActionAfter(data);
                this.getItemMenu();
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
                case 'create-and-close':
                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'products.index'});
                    break;
                case 'save-and-new':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'products.form'});
                    await this.getFormMenu();
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    this.item.categories = this.convertToTreeSelectData(data.product_categories);
                    this.route.params.id = null;
                    this.$router.push({name: 'products.form'});
                    await this.getFormMenu();
                    break;
                case 'trash':
                case 'restore':
                    this.item = data;
                    vaah().toastSuccess(['Action was successful']);
                    break;
                case 'save':
                    this.item = data;
                    this.item.categories = this.convertToTreeSelectData(data.product_categories);
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
                if (data.fill.category) {
                    const category_after_fill = [data.fill.category]
                    this.item.categories = this.convertToTreeSelectData(category_after_fill);
                }
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
            vaah().confirmDialogActivate(this.listAction);
        },
        //---------------------------------------------------------------------

        confirmDeactivateAll()
        {
            this.action.type = 'deactivate-all';
            vaah().confirmDialogDeactivate(this.listAction);
        },
        //---------------------------------------------------------------------

        confirmTrashAll()
        {
            this.action.type = 'trash-all';
            vaah().confirmDialogTrash(this.listAction);
        },

        //---------------------------------------------------------------------

        confirmRestoreAll()
        {
            this.action.type = 'restore-all';
            vaah().confirmDialogRestore(this.listAction);
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
            this.searched_store = null;
            this.selected_product_variations = null;
            this.filter_selected_brands = null;
            this.filter_selected_store = null;
            this.selected_vendors = null;
            this.filter_selected_product_type = null;
            this.selected_dates = null;
            this.selected_store_at_list = null;
            this.min_quantity = this.assets.min_quantity;
            this.max_quantity = this.assets.max_quantity;
            this.product_category_filter=null;
            this.quantity = null;
            this.query = vaah().clone(empty_states.query);
            vaah().toastSuccess(['Action was successful']);
            //reload page list
            await this.getList();
            await this.setDefaultStoreForProductList();
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
            this.$router.push({name: 'products.index'})
        },
        //---------------------------------------------------------------------
        toList()
        {

            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'products.index'})
            this.selected_vendor = null;

        },
        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.getDefaultStore();
            this.getCategories();

            this.$router.push({name: 'products.form'})
        },
        //---------------------------------------------------------------------
        async getCategories(){
            const options = {
                method: 'get',
            };

            await vaah().ajax(
                this.ajax_url+'/get/categories',
                this.getCategoriesAfter,
                options
            );
        },
        getCategoriesAfter(data,res){
            this.categories_dropdown_data = this.convertToTreeSelectFormat(data.categories);
        },
        //---------------------------------------------------------------------
        async getDefaultStore()
        {
            const options = {
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/get/default/store',
                this.getDefaultStoreAfter,
                options
            );
        },

        //-----------------------------------------------------------------------

        getDefaultStoreAfter(data,res) {
            if(data)
            {
                this.item.store = data;
                this.item.vh_st_store_id=data.id;
            }
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            this.item = vaah().clone(item);
            this.$router.push({name: 'products.view', params:{id:item.id}})
        },
        //---------------------------------------------------------------------
        toVariation(item)
        {
            this.variation_item.attribute_option_type =1;
            this.variation_item.product_attributes =[];
            this.variation_item.selected_attribute =null;
            this.variation_item.attribute_options =null;
            this.variation_item.show_create_form =false;
            this.variation_item.select_all_variation =false;
            this.create_variation_data= null;
            this.new_variation= [];
            this.item = vaah().clone(item);
            // this.variation_item = vaah().clone(this.variation_item);
            this.$router.push({name: 'products.variation', params:{id:item.id}})

        },
        //---------------------------------------------------------------------
        toVendor(item)
        {
            this.item = vaah().clone(item);
            this.$router.push({name: 'products.vendor', params:{id:item.id}})
        },
        //---------------------------------------------------------------------

        toEdit(item)
        {
            this.item = item;
            this.$router.push({name: 'products.form', params:{id:item.id}})
            this.getCategories();
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
            this.variation_selected_menu = [
                {
                    label: 'Remove',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.bulkRemoveProductVariation()
                    }
                },
                {
                    label: 'Remove All',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.removeAllProductVariation()
                    }
                },
            ]

            this.vendor_selected_menu = [
                {
                    label: 'Remove',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.bulkRemoveVendor()
                    }
                },
                {
                    label: 'Remove All',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.removeAllVendor()
                    }
                },

            ]
            this.attribute_selected_menu = [
                {
                    label: 'Remove',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.bulkRemoveAttribute()
                    }
                },
                {
                    label: 'Remove All',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.removeAllAttributes()
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
                }
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
        setDateRange(){

            if(!this.selected_dates){
                return false;
            }

            const dates =[];

            for (const selected_date of this.selected_dates) {

                if(!selected_date){
                    continue ;
                }

                let search_date = dayjs(selected_date)
                let UTC_date = search_date.format('YYYY-MM-DD');

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

        async reloadPage()
        {
            await this.getList();
            vaah().toastSuccess(["Page Reloaded"]);
        },
        //---------------------------------------------------------------------

        addProductVariation() {

            const unique_product_variations = [];
            const check_names = new Set();

            for (const product_variations of this.selected_product_variations) {
                if (!check_names.has(product_variations.name)) {
                    unique_product_variations.push(product_variations);
                    check_names.add(product_variations.name);
                }
            }
            const product_variations_slugs = unique_product_variations.map(variation => variation.slug);
            this.selected_product_variations = unique_product_variations;
            this.query.filter.product_variations = product_variations_slugs;

        },

        //---------------------------------------------------------------------

        addProductVendor() {

            const unique_vendors = [];
            const check_names = new Set();

            for (const product_vendors of this.selected_vendors) {
                if (!check_names.has(product_vendors.name)) {
                    unique_vendors.push(product_vendors);
                    check_names.add(product_vendors.name);
                }
            }
            const product_vendors_slugs = unique_vendors.map(vendors => vendors.slug);
            this.selected_vendors = unique_vendors;
            this.query.filter.vendors = product_vendors_slugs;

        },

        //---------------------------------------------------------------------

        addFilterBrand() {

            const unique_brands = [];
            const check_names = new Set();

            for (const brands of this.filter_selected_brands) {
                if (!check_names.has(brands.name)) {
                    unique_brands.push(brands);
                    check_names.add(brands.name);
                }
            }
            const brands_slugs = unique_brands.map(brands => brands.slug);
            this.filter_selected_brands = unique_brands;
            this.query.filter.brands = brands_slugs;

        },

        //---------------------------------------------------------------------

        async searchProductVendor(event) {
            const query = event;
            const options = {
                params: query,
                method: 'post',
            };
            await vaah().ajax(
                this.ajax_url + '/search/product-vendor',
                this.searchProductVendorAfter,
                options
            );
        },

        //---------------------------------------------------------------------

        searchProductVendorAfter(data, res) {

            if (data) {
                this.vendor_suggestion = data;
            }
        },

        //---------------------------------------------------------------------

        openModal(item){
            this.seo_meta_value = JSON.stringify(item,null,2);
            this.display_seo_modal=true;
        },

        //---------------------------------------------------------------------
        async setVendorsAfterPageRefresh()
        {

            let query = {
                filter: {
                    vendor: this.query.filter.vendors,
                },
            };
                const options = {
                    params: query,
                    method: 'post',
                };

                await vaah().ajax(
                    this.ajax_url+'/search/vendors-using-slug',
                    this.setVendorsAfterPageRefreshAfter,
                    options
                );


        },

        //---------------------------------------------------------------------
        setVendorsAfterPageRefreshAfter(data, res) {

            if (data) {
                this.selected_vendors = data;
            }
        },

        //---------------------------------------------------------------------

        async setBrandsAfterPageRefresh()
        {

            let query = {
                filter: {
                    brand: this.query.filter.brands,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/brands-using-slug',
                this.setBrandsAfterPageRefreshAfter,
                options
            );


        },

        //---------------------------------------------------------------------
        setBrandsAfterPageRefreshAfter(data, res) {

            if (data) {
                this.filter_selected_brands = data;
            }
        },

        //---------------------------------------------------------------------

        async setProductVariationsAfterPageRefresh()
        {
            let query = {
                filter: {
                    variation: this.query.filter.product_variations,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/variations-using-slug',
                this.setProductVariationsAfterPageRefreshAfter,
                options
            );


        },

        //---------------------------------------------------------------------
        setProductVariationsAfterPageRefreshAfter(data, res) {

            if (data) {
                this.selected_product_variations = data;
            }
        },

        //---------------------------------------------------------------------

        async setStoresAfterPageRefresh()
        {
            let query = {
                filter: {
                    store: this.query.filter.stores,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/stores-using-slug',
                this.setStoresAfterPageRefreshAfter,
                options
            );


        },

        //---------------------------------------------------------------------
        setStoresAfterPageRefreshAfter(data, res) {

            if (data) {
                this.filter_selected_store = data;
            }
        },

        //---------------------------------------------------------------------
        async setProductTypeAfterPageRefresh()
        {

            let query = {
                filter: {
                    product_type: this.query.filter.product_types,
                },
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/search/product-types-using-slug',
                this.setProductTypeAfterPageRefreshAfter,
                options
            );


        },

        //---------------------------------------------------------------------
        setProductTypeAfterPageRefreshAfter(data, res) {

            if (data) {
                this.filter_selected_product_type = data;
            }
        },

        //---------------------------------------------------------------------

        toViewVendors(product)
        {
            const query = {
                page: 1,
                rows: 20,
                filter: {
                    products: [product.slug],trashed: 'include'
                }
            };
            const route = {
                name: 'vendors.index',
                query: query
            };
            this.$router.push(route);
        },

        //---------------------------------------------------------------------

        toViewVariation(product)
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

        //---------------------------------------------------------------------



        //---------------------------------------------------------------------


        minQuantity(event){
            this.query.filter.min_quantity = event.value

        },

        //---------------------------------------------------------------------

        maxQuantity(event){
            this.query.filter.max_quantity = event.value
        },

        //---------------------------------------------------------------------





        //---------------------------------------------------------------------
        updateMinQuantity(event)
        {
            this.quantity.from = event.value;
        },

        //---------------------------------------------------------------------

        updateMaxQuantity(event)
        {
            this.quantity.to = event.value;
        },

        //---------------------------------------------------------------------

        async searchVendor(event) {

            const query = event;
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

        searchVendorAfter(data,res) {
            if(data)
            {
                this.filtered_vendors = data;
            }
        },

        //---------------------------------------------------------------------

        checkDate()
        {
            if (dayjs(this.item.available_at).isBefore(this.item.launch_date))
            {
                this.item.available_at = null;
                vaah().toastErrors(['Available date should be after launch date']);

            }
        },

        //---------------------------------------------------------------------

        watchAttributes()
        {
            watch(this.variation_item, (newVal,oldVal) =>
                {
                    const anyDeselected = newVal.product_attributes.some(item => !item.is_selected);
                    // Update select_all_product variable
                    this.select_all_attribute = !anyDeselected;
                },{deep: true}
            )
        },

        //---------------------------------------------------------------------
        watchVariations()
        {

            watch(this.item, (newVal,oldVal) =>
                {
                    const anyDeselected = newVal.product_variations.structured_variations.some(item => !item.is_selected);
                    this.variation_item.select_all_variation = !anyDeselected;
                },{deep: true}
            )

        },

        //---------------------------------------------------------------------

        watchVendors()
        {

            watch(this.item, (newVal,oldVal) =>
                {
                    const anyDeselected = newVal.vendors.some(item => !item.is_selected);
                    this.select_all_vendor = !anyDeselected;
                },{deep: true}
            )

        },
        async addToCart(item){
            this.product_detail.push(item);

            if (!this.show_cart_msg){
                this.add_to_cart=true;
            }
            if (this.show_cart_msg && this.active_user !== null) {
                await this.addProductToCart(item);
            }

        },
        showMsg(){
            this.add_to_cart = false;
            this.show_cart_msg=true;
        },
        async addProductToCart(product){

            const user_info = this.item.user ? this.item.user : this.active_user;
            const products = this.product_detail.map(product => ({
                id: product.id,
                quantity: 1
            }));
            const query = {
                user: user_info,
                products: products,
            };
            const options = {
                params: query,
                method: 'post',
            };

            await vaah().ajax(
                this.ajax_url+'/cart/generate',
                this.saveProductInCartAfter,
                options
            );
        },
        //---------------------------------------------------------------------

        saveProductInCartAfter(data,res){
           if (data){

               this.getList();

           }
            this.add_to_cart = false;
            this.item.user=null;
            this.product_detail=[];
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

        setUser(event) {
            let user = toRaw(event.value);
            if (user && user.id) {
                this.item.vh_user_id = user.id;
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
            this.show_cart_msg=false;
            this.active_user=null;
            this.product_detail=[];
            this.getList();
        },

        //---------------------------------------------------------------------
        async openVendorsPanel(item)
        {
            this.show_vendor_panel = true;
            this.product_id=item.id;
            this.product_name=item.name;
            if (item.id) {
                await vaah().ajax(
                    ajax_url + '/'+ item.id+ '/vendors',
                    this.openVendorsPanelAfter
                );
            }
        },

        selectCategoryForFilter(event) {
            const selected_categories = this.query.filter.category || [];
            const existing_category = selected_categories.find(category => category === event.data.toLowerCase());
            if (!existing_category) {
                selected_categories.push(event.data.toLowerCase());
                this.query.filter.category = selected_categories;
            }
        },


        //---------------------------------------------------------------------

        removeCategoryForFilter(event) {
            let selected_categories = Array.isArray(this.query.filter.category) ? [...this.query.filter.category] : [];

            if (Array.isArray(selected_categories)) {
                const index = selected_categories.findIndex(category => category === event.data.toLowerCase());

                if (index !== -1) {
                    selected_categories.splice(index, 1);
                }

                this.query.filter.category = selected_categories;
            }
        },


        //---------------------------------------------------------------------

        async setCategoryInFilterAfterRefresh() {
            if (this.route.query.filter && this.route.query.filter.category) {
                let category = this.route.query.filter.category;

                let query = {
                    filter: {
                        category: category
                    }
                };

                const options = {
                    params: query,
                    method: 'post'
                };

                await vaah().ajax(
                    this.ajax_url+'/search/category-using-slug',
                    this.setCategoryInFilterAfterRefreshAfter,
                    options
                );
            }
        },

        //---------------------------------------------------------------------


        setCategoryInFilterAfterRefreshAfter(data, res) {

            if (data) {
                this.product_category_filter = this.convertFilterCategoryToTreeSelect(data);
            }
        },
        //---------------------------------------------------------------------

        convertFilterCategoryToTreeSelect(data) {
            const tree_select_data = {};
            for (const category_name in data) {
                if (data.hasOwnProperty(category_name)) {
                    const category = data[category_name];
                    const category_id = category.id.toString();
                    tree_select_data[category_id] = true;
                }
            }
            return tree_select_data;
        },


        //---------------------------------------------------------------------










//---------------------------------------------------------------------

        openVendorsPanelAfter(data, res) {

            if (data) {
                data.sort((a, b) => {
                    const preferred_vendor = b.is_preferred - a.is_preferred;
                    if (preferred_vendor !== 0) {
                        return preferred_vendor;
                    }

                    const default_vendor = b.is_default - a.is_default;
                    if (default_vendor !== 0) {
                        return default_vendor;
                    }

                    return b.quantity - a.quantity;
                });

                this.item.vendor_data = data;
            } else {
                this.$router.push({name: 'products.index', query: this.query});
            }
        },

        //---------------------------------------------------------------------



        //---------------------------------------------------------------------

        async toggleIsPreferred(vendor_item)
        {

            const action = vendor_item.is_preferred ? 'not-preferred' : 'preferred';
            await this.vendorPreferredAction(action, vendor_item);
        },
        //---------------------------------------------------------------------

        async vendorPreferredAction(action, vendor_item=null){

            if(!vendor_item)
            {
                vendor_item = this.item;
            }

            this.form.action = action;
            let ajax_url = this.ajax_url;

            let options = {
                params: { action },
                method: 'PATCH',
            };
            ajax_url += '/'+vendor_item.vh_st_product_id+'/vendors/'+vendor_item.id+ '/action';
            await vaah().ajax(
                ajax_url,
                this.vendorPreferredActionAfter,
                options
            );
        },
        //---------------------------------------------------------------------

        async vendorPreferredActionAfter(data, res)
        {
            if(data)
            {
                await this.getList();
                await this.openVendorsPanel(data);
            }

        },
        //---------------------------------------------------------------------

        minPrice(event){
          this.query.filter.min_price=event.value;
        },
        maxPrice(event){
            this.query.filter.max_price=event.value;

        },
        //---------------------------------------------------------------------

        redirectToVendorProducts()
        {
            this.$router.push({name: 'productvendors.index'});
        },
        //---------------------------------------------------------------------

        getPriceRangeOfProduct(prices) {

            if (!prices || !Array.isArray(prices)) {
                return 'Not available';
            }

            const numericPrices = prices.filter(price => typeof price === 'number');

            if (numericPrices.length === 0) {
                return '';
            }

            const minPrice = Math.min(...numericPrices);
            const maxPrice = Math.max(...numericPrices);

            if (minPrice === maxPrice) {
                return `${minPrice}`;
            }

            return `${minPrice} - ${maxPrice}`;
        },
        //---------------------------------------------------------------------
        async topSellingProducts(store=null) {
            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,
                filter_all: this.filter_all ?? null,
                store: store ?? null,
            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/top-selling-products',
                this.topSellingProductsAfter,
                options
            );
        },
        topSellingProductsAfter(data,res){
            if (data) {
                this.top_selling_products = data;
            }
        },

        async topSellingBrands(store=null) {
            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,
                filter_all: this.filter_all ?? null,
                store: store ?? null,
            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/top-selling-brands',
                this.topSellingBrandsAfter,
                options
            );
        },
        topSellingBrandsAfter(data,res){
            if (data) {
                this.top_selling_brands = data;
            }
        },
        async topSellingCategories(store=null) {
            let params = {

                start_date: useRootStore().filter_start_date ?? null,
                end_date: useRootStore().filter_end_date ?? null,
                filter_all: this.filter_all ?? null,
                store: store ?? null,
            }
            let options = {
                params: params,
                method: 'POST'
            }
            await vaah().ajax(
                this.ajax_url + '/charts/top-selling-categories',
                this.topSellingCategoriesAfter,
                options
            );
        },
        topSellingCategoriesAfter(data,res){
            if (data) {
                this.top_selling_categories = data;
            }
        },

        getQuickFilterMenu() {

            this.quick_filter_menu = [
                {
                    label: 'All',
                    command: () => {
                        this.updateQuickFilter('all');
                    }
                },

            ];

        },
        updateQuickFilter(time)
        {
            this.filter_all = time;
            this.topSellingProducts();
             this.topSellingCategories();
             this.topSellingBrands();
        },
        //---------------------------------------------------------------------
        async loadProductChartsData(){
            this.filter_all=null;
           await this.topSellingProducts();
           await this.topSellingCategories();
           await this.topSellingBrands();
        },
        onHideCartDialog(){
            this.product_detail=[];
        },

        toImport()
        {
            this.$router.push({ name : 'import.upload' });
        },
        //----------------------------------------------------------------------

        async getExportMenu()
        {
            let total = this.list?.total;
            let items_selected = this.action.items.length;
            this.export_menu = [
                {
                    label: `Export Selected(${items_selected})`,

                    command: () => {
                        if (items_selected > 0) {
                            this.$router.push({ name: 'import.export' });
                        }
                    },
                    disabled: items_selected === 0
                },

                {
                    label: `Export All (${this.list?.total})`,
                    command: async () => {
                        this.$router.push({ name: 'import.export', query: { type: 'export-all' } });

                    }
                },
            ]
        },
        //----------------------------------------------------------------------

        watchSelectedItem()
        {

            watch(this.action, (newVal,oldVal) =>
                {
                    this.getExportMenu();
                },{deep: true}
            )
        },
        //----------------------------------------------------------------------

        async exportProducts(type,data){

            this.action.type = type;
            const [selected_columns, is_export_custom_meta] = data;
            if (type === 'export') {
                if (this.action.items.length < 1) {
                    vaah().toastErrors(['Select records']);
                    return false;
                }
            }
            this.action.columns = selected_columns;
            this.action.is_export_custom_meta = is_export_custom_meta;
            let url = this.ajax_url + '/export/data';
            let method = 'POST';
            let options = {
                params: this.action,
                method: method,
                show_success: false
            };

            await vaah().ajax(
                url,
                this.exportProductsAfter,
                options,

            );
        },

        //----------------------------------------------------------------------

        async exportProductsAfter(data, res)
        {
            if(data && data.content && data.headers)
            {
                const blob = new Blob([data.content], { type: data.headers['Content-Type'] });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'exported-products.csv';
                link.click();
                window.URL.revokeObjectURL(url);
                await this.getList();
                this.getItemMenu();
                this.getFormMenu();
                this.column_to_export.columns = [];
                this.is_export_custom_meta=false;
                this.action.type=null;
                this.action.items=[];
            }

        },

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
        confirmAction(action_type,action_header)
        {
            this.action.type = action_type;
            vaah().confirmDialog(action_header,'Are you sure you want to do this action?',
                this.listAction,null,'p-button-primary');
        },
        //---------------------------------------------------------------------

        searchStoreForListQuery(event){
            const query = event.query.toLowerCase();
            this.filteredStores = this.active_stores.filter(store =>
                store.name.toLowerCase().includes(query)
            );
        },
        //---------------------------------------------------------------------

        setDefaultStoreForProductList(){
            this.default_store = this.active_stores.find(store => store.is_default === 1);
            if (this.default_store) {
                this.selected_store_at_list = this.default_store;
            }
        },
        //---------------------------------------------------------------------


    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useProductStore, import.meta.hot))
}


import ListTable from './partials/ListTable';

let namespace = 'stores';

export default {
    computed:{
        root() {return this.$store.getters['root/state']},
        ajax_url() {return this.$store.getters[namespace+'/state'].ajax_url},
        assets() {return this.$store.getters[namespace+'/state'].assets},
        data() {return this.$store.getters[namespace+'/state'].data},
    },
    components:{
        ListTable,
    },
    data()
    {
        return {
            namespace: namespace,
            page: null,
            is_btn_loading: null,
            selected_date: null,
            search_delay: null,
            search_delay_time: 800,
            ids: [],
        }
    },
    watch: {
        $route(to, from) {
            this.$store.dispatch(
                this.namespace+'/updateView',
                this.$route
            );
        },
        'data.query': {
            handler: function(newVal, oldValue) {
                let url_query = JSON.stringify(this.$route.query);
                let page_query = JSON.stringify(newVal);
                if(url_query !== page_query)
                {
                    this.$router.replace({query: newVal});
                }
            },
            deep: true
        }
    },
    created()
    {

    },
    mounted() {
        //----------------------------------------------------
        //----------------------------------------------------
        this.data.query = this.$vh.clone(this.$route.query);
        //----------------------------------------------------
        this.onLoad();
        //----------------------------------------------------

        //----------------------------------------------------
    },
    methods: {
        //---------------------------------------------------------------------
        onLoad: function()
        {
            this.updateView();
            this.getAssets();
        },
        //---------------------------------------------------------------------
        updateData: function(newPageObject)
        {
            let payload = {
                key: 'data',
                value: newPageObject
            };
            this.$store.commit(this.namespace+'/updateState', payload)
        },
        //---------------------------------------------------------------------
        updateView: function()
        {
            this.$store.dispatch(this.namespace+'/updateView', this.$route);
        },
        //---------------------------------------------------------------------
        async getAssets() {
            await this.$store.dispatch(this.namespace+'/getAssets');
            this.getList();
        },
        //---------------------------------------------------------------------
        getList: function () {
            this.$Progress.start();
            //this.$vaah.updateCurrentURL(this.query_string, this.$router);

            console.log('--->', this.ajax_url);

            let url = this.ajax_url;
            this.$vh.ajax(url, this.query_string, this.getListAfter);
        },
        //---------------------------------------------------------------------
        getListAfter: function (data, res) {
            if(data)
            {
                this.data.list = data;
            }
            this.$Progress.finish();
        },
        //---------------------------------------------------------------------
        toggleFilters: function()
        {
            this.data.show_filters = !this.data.show_filters;
        },
        //---------------------------------------------------------------------
        clearSearch: function () {
            this.query_string.q = null;
            this.update('query_string', this.query_string);
            this.getList();
        },
        //---------------------------------------------------------------------
        setDateFilter: function()
        {
            if(this.query_string.from){
                let from = new Date(this.query_string.from);

                this.selected_date=[
                    from
                ];
            }

            if(this.query_string.to){
                let to = new Date(this.query_string.to);

                this.selected_date[1] = to;
            }
        },
        //---------------------------------------------------------------------
        resetPage: function()
        {

            //reset query strings
            this.resetQueryString();

            this.resetSelectedDate();

            //reset bulk actions
            this.resetBulkAction();

            //reload page list
            this.getList();

        },
        //---------------------------------------------------------------------
        resetSelectedDate: function()
        {
            this.selected_date = null;
        },
        //---------------------------------------------------------------------
        resetQueryString: function()
        {
            for(let key in this.query_string)
            {
                if(key == 'page')
                {
                    this.query_string[key] = 1;
                } else
                {
                    this.query_string[key] = null;
                }
            }

            this.update('query_string', this.query_string);
        },
        //---------------------------------------------------------------------
        resetBulkAction: function()
        {
            this.data.bulk_action = {
                selected_items: [],
                data: {},
                action: null,
            };
            this.update('bulk_action', this.data.bulk_action);
        },
        //---------------------------------------------------------------------
        paginate: function(page=1)
        {
            this.query_string.page = page;
            this.update('query_string', this.query_string);
            this.getList();
        },
        //---------------------------------------------------------------------
        delayedSearch: function()
        {
            let self = this;
            clearTimeout(this.search_delay);
            this.search_delay = setTimeout(function() {
                self.getList();
            }, this.search_delay_time);

            this.query_string.page = 1;
            this.update('query_string', this.query_string);

        },
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
        actions: function () {

            if(!this.data.bulk_action.action)
            {
                this.$vaah.toastErrors(['Select an action']);
                return false;
            }

            if(this.data.bulk_action.action == 'bulk-change-status'){
                if(!this.data.bulk_action.data.status){
                    this.$vaah.toastErrors(['Select a status']);
                    return false;
                }
            }

            if(this.data.bulk_action.selected_items.length < 1)
            {
                this.$vaah.toastErrors(['Select a record']);
                return false;
            }

            this.$Progress.start();
            this.update('bulk_action', this.data.bulk_action);
            let ids = this.$vaah.pluckFromObject(this.data.bulk_action.selected_items, 'id');

            let params = {
                inputs: ids,
                data: this.data.bulk_action.data
            };

            console.log('--->params', params);

            let url = this.ajax_url+'/actions/'+this.data.bulk_action.action;
            this.$vaah.ajax(url, params, this.actionsAfter);
        },
        //---------------------------------------------------------------------
        actionsAfter: function (data, res) {
            if(data)
            {
                this.$root.$emit('eReloadItem');
                this.resetBulkAction();
                this.getList();
                this.$store.dispatch('root/reloadPermissions');
            } else
            {
                this.$Progress.finish();
            }
        },
        //---------------------------------------------------------------------
        sync: function () {

            /*this.data.query_string.recount = true;

            this.is_btn_loading = true;

            this.update('query_string', this.data.query_string);*/
            this.getList();
        },
        //---------------------------------------------------------------------
        updateActiveItem: function () {

            if(this.$route.fullPath.includes('stores/?')){
                this.update('active_item', null);
            }
        },
        //---------------------------------------------------------------------
        hasPermission: function(slug)
        {
            return this.$vaah.hasPermission(this.permissions, slug);
        },
        //---------------------------------------------------------------------
        setDateRange: function()
        {

            if(this.selected_date.length > 0){
                let current_datetime = new Date(this.selected_date[0]);
                this.query_string.from = current_datetime.getFullYear() + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getDate();

                current_datetime = new Date(this.selected_date[1]);
                this.query_string.to = current_datetime.getFullYear() + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getDate();

                this.getList();
            }



        },
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
    }
}

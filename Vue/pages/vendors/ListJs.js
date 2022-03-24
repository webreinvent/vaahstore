
import ListTable from './partials/ListTable';
import qs from "qs";

let namespace = 'vendors';

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

                this.data.action = this.$vh.clone(this.data.empty.action);

                //create query string
                let query_string = qs.stringify(newVal, {
                        skipNulls: true
                    });
                let query_object = qs.parse(query_string);

                //replace url query string
                this.$router.replace({query: query_object});

                //update applied filters
                this.data.count_filters = Object.keys(query_object).length;

                //reload list on query update
                this.getList();
            },
            deep: true
        }
    },
    created()
    {

    },
    mounted() {

        //----------------------------------------------------
        document.title = "Vendors";
        //----------------------------------------------------
        this.onLoad();
        //----------------------------------------------------
    },
    methods: {
        //---------------------------------------------------------------------
        onLoad: function()
        {
            this.updateQueryFromUrl();
            this.updateView();
            this.getAssets();
        },
        //---------------------------------------------------------------------
        updateQueryFromUrl: function ()
        {
            let query = this.$vh.clone(this.$route.query);
            if(Object.keys(query).length > 0)
            {
                for(let k in query)
                {
                    this.data.query[k] = query[k];
                }
            }
        },
        //---------------------------------------------------------------------
        updateView: function()
        {
            this.$store.dispatch(this.namespace+'/updateView', this.$route);
        },
        //---------------------------------------------------------------------
        async getAssets() {
            await this.$store.dispatch(this.namespace+'/getAssets');
            this.data.is_list_loading = true;
            this.getList();
        },
        //---------------------------------------------------------------------
        getList: function () {
            this.$Progress.start();

            let url = this.ajax_url;
            this.$vh.ajax(
                url, this.data.query, this.getListAfter
            );
        },
        //---------------------------------------------------------------------
        getListAfter: function (data, res) {
            if(data)
            {
                this.data.list = data;
            }
            this.data.is_list_loading = null;
            this.$Progress.finish();
        },
        //---------------------------------------------------------------------
        clearSearch: function () {
            this.data.query.q = null;
            this.getList();
        },
        //---------------------------------------------------------------------
        resetQuery: function()
        {
            //reset query strings
            this.resetQueryString();

            //reload page list
            this.getList();
        },
        //---------------------------------------------------------------------
        resetQueryString: function()
        {
            this.data.query = this.$vh.clone(this.data.empty.query);
            this.data.query.sort = null;
        },
        //---------------------------------------------------------------------
        paginate: function(page=1)
        {
            // set reactive property to query
            if(page===1)
            {
                page = null;
            }
            this.$set(this.data.query, 'page', page)
            this.getList();
        },
        //---------------------------------------------------------------------
        delayedSearch: function()
        {
            let self = this;
            clearTimeout(this.data.search.delay_timer);
            this.data.search.delay_timer = setTimeout(function() {
                self.getList();
            }, this.data.search.delay_time);
            this.data.query.page = 1;
        },
        //---------------------------------------------------------------------
        updateList: function (type) {

            if(!type)
            {
                this.$vh.toastErrors(['Select an action type']);
                return false;
            }
            this.data.action.type = type;
            if(this.data.action.items.length < 1)
            {
                this.$vh.toastErrors(['Select a record']);
                return false;
            }

            this.$Progress.start();
            let params = this.data.action;
            let url = this.ajax_url;
            this.$vh.ajax(url, params, this.updateListAfter, 'put');
        },
        //---------------------------------------------------------------------
        updateListAfter: function (data, res) {
            if(data)
            {
                this.data.action = this.$vh.clone(this.data.empty.action);
                this.$root.$emit('eReloadItem');
                this.getList();
            }
        },
        //---------------------------------------------------------------------
        confirmDelete: function()
        {

            if(this.data.action.items.length < 1)
            {
                this.$vh.toastErrors(['Select a record']);
                return false;
            }

            let self = this;
            this.$buefy.dialog.confirm({
                title: 'Deleting record',
                message: 'Are you sure you want to <b>delete</b> the records? This action cannot be undone.',
                confirmText: 'Delete',
                type: 'is-danger',
                hasIcon: true,
                onConfirm: function () {
                    self.deleteList('delete');
                }
            })
        },
        //---------------------------------------------------------------------
        deleteList: function (type) {
            this.data.action.type = type;
            this.$Progress.start();
            let params = this.data.action;
            let url = this.ajax_url;
            this.$vh.ajax(url, params, this.deleteListAfter, 'delete');
        },
        //---------------------------------------------------------------------
        deleteListAfter: function (data, res) {
            if(data)
            {
                this.data.action = this.$vh.clone(this.data.empty.action);
                this.$root.$emit('eReloadItem');
                this.getList();
            }
        },
        //---------------------------------------------------------------------
        hasPermission: function(slug)
        {
            return this.$vaah.hasPermission(this.permissions, slug);
        },
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
    }
}

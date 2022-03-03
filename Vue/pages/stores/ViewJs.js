let namespace = 'stores';

export default {
    computed:{
        root() {return this.$store.getters['root/state']},
        ajax_url() {return this.$store.getters[namespace+'/state'].ajax_url},
        assets() {return this.$store.getters[namespace+'/state'].assets},
        data() {return this.$store.getters[namespace+'/state'].data},
    },
    components:{

    },
    data()
    {
        return {
            namespace: namespace,
            item: null,
            is_btn_loading: null,
            is_content_loading: null,
        }
    },
    watch: {
        $route(to, from) {
            this.$store.dispatch(
                this.namespace+'/updateView',
                this.$route
            );
            this.getItem();
        },
        'data.item': {
            handler: function(newVal, oldValue) {
                this.item = newVal;
            },
            deep: true
        }
    },
    mounted() {
        //----------------------------------------------------
        this.onLoad();
        //----------------------------------------------------
        this.item = this.data.item;
        //----------------------------------------------------
        //----------------------------------------------------
        //----------------------------------------------------
    },
    methods: {
        //---------------------------------------------------------------------
        onLoad: function()
        {
            this.getItem();
        },

        //---------------------------------------------------------------------
        getItem: function () {
            this.$Progress.start();
            this.params = {};
            let url = this.ajax_url+'/'+this.$route.params.id;
            this.$vh.ajax(url, this.params, this.getItemAfter);
        },
        //---------------------------------------------------------------------
        getItemAfter: function (data, res) {
            this.$Progress.finish();
            this.is_content_loading = false;
            if(data)
            {
                this.data.item = data;
            } else
            {
                //if item does not exist or delete then redirect to list
                this.data.item = null;
                this.$router.push({name: 'stores.list'});
            }
        },
        //---------------------------------------------------------------------
        updateItem: function (action) {
            this.$set(this.item, 'action', action)
            this.$Progress.start();
            let params = this.item;
            let url = this.ajax_url+'/'+this.item.id;
            this.$vh.ajax(url, params, this.updateItemAfter, 'patch');
        },
        //---------------------------------------------------------------------
        updateItemAfter: function (data, res) {
            this.$Progress.finish();
            if(data)
            {
                this.item = data;
            }
            this.$emit('eReloadList');
        },
        //---------------------------------------------------------------------
        confirmDelete: function()
        {
            let self = this;
            this.$buefy.dialog.confirm({
                title: 'Deleting record',
                message: 'Are you sure you want to <b>delete</b> the record? This action cannot be undone.',
                confirmText: 'Delete',
                type: 'is-danger',
                hasIcon: true,
                onConfirm: function () {
                    self.deleteItem();
                }
            })
        },
        //---------------------------------------------------------------------
        deleteItem: function () {
            this.$Progress.start();
            let params = {};
            let url = this.ajax_url+'/'+this.item.id;
            this.$vh.ajax(url, params, this.deleteItemAfter, 'delete');
        },
        //---------------------------------------------------------------------
        deleteItemAfter: function (data, res) {
            this.$Progress.finish();
            this.$emit('eReloadList');
            this.resetItem();
        },
        //---------------------------------------------------------------------
        resetItem: function () {
            this.data.item = null;
            this.$router.push({name:'stores.list'});
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

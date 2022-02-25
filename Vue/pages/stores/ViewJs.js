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
        update: function(name, value)
        {
            let update = {
                state_name: name,
                state_value: value,
                namespace: namespace,
            };
            this.$vaah.updateState(update);
        },
        //---------------------------------------------------------------------
        updateView: function()
        {
            this.$store.dispatch(namespace+'/updateView', this.$route);
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
        actions: function (action) {

            console.log('--->action', action);

            this.$Progress.start();
            this.page.bulk_action.action = action;
            this.update('bulk_action', this.page.bulk_action);
            let params = {
                inputs: [this.item.id],
                data: null
            };

            let url = this.ajax_url+'/actions/'+action;
            this.$vaah.ajax(url, params, this.actionsAfter);

        },
        //---------------------------------------------------------------------
        actionsAfter: function (data, res) {
            let action = this.page.bulk_action.action;
            if(data)
            {
                this.resetBulkAction();
                this.$emit('eReloadList');

                if(action == 'bulk-delete')
                {
                    this.$router.push({name: 'stores.list'});
                } else
                {
                    this.getItem();
                }

            } else
            {
                this.$Progress.finish();
            }
        },
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        resetBulkAction: function()
        {
            this.page.bulk_action = {
                selected_items: [],
                data: {},
                action: "",
            };
            this.update('bulk_action', this.page.bulk_action);
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
                    self.actions('bulk-delete');
                }
            })
        },
        //---------------------------------------------------------------------
        isCopiable: function (label) {

            if(
                label == 'id' || label == 'uuid' || label == 'slug'
            )
            {
                return true;
            }

            return false;

        },
        //---------------------------------------------------------------------
        isUpperCase: function (label) {

            if(
                label == 'id' || label == 'uuid'
            )
            {
                return true;
            }

            return false;

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

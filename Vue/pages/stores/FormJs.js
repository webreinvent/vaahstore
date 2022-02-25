let namespace = 'stores';

export default {
    computed:{
        root() {return this.$store.getters['root/state']},
        base_url() {return this.$store.getters[namespace+'/state'].base_url},
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
            page: null,
            is_btn_loading: null,
            labelPosition: 'on-border',
            form_type: null,
            params: {},
            local_action: null,
        }
    },
    watch: {
        $route(to, from) {
            this.$store.dispatch(
                this.namespace+'/updateView',
                this.$route
            );
            this.onLoad();
        },
    },
    mounted() {
        //----------------------------------------------------
        this.onLoad();
        //----------------------------------------------------
        //----------------------------------------------------
    },
    methods: {
        //---------------------------------------------------------------------
        onLoad: function()
        {
            if(this.$route.name === 'stores.update')
            {
                this.form_type = 'Update';
                if(!this.data.item)
                {
                    this.getItem();
                }
            } else
            {
                this.form_type = 'Create';
                this.data.item = this.$vh.clone(this.assets.empty_item);
            }
        },
        //---------------------------------------------------------------------
        updateData: function(data)
        {
            let payload = {
                key: 'data',
                value: data
            };
            this.$store.commit(this.namespace+'/updateState', payload)
        },
        //---------------------------------------------------------------------
        setLocalAction: function (action) {
            this.local_action = action;
            if(this.local_action === 'save')
            {
                this.updateItem();
            } else
            {
                this.createItem();
            }
        },
        //---------------------------------------------------------------------
        getFaker: function () {
            this.$Progress.start();
            this.params = {
                model_namespace: this.data.model,
                except: this.assets.fillable.except,
            };
            let url = this.base_url+'/faker';
            this.$vh.ajax(url, this.params, this.getFakerAfter, 'post');
        },
        //---------------------------------------------------------------------
        getFakerAfter: function (data, res) {
            this.$Progress.finish();
            this.is_content_loading = false;
            if(data)
            {
                let self = this;
                Object.keys(data.fill).forEach(function(key) {
                    self.data.item[key] = data.fill[key];
                });
            }
        },
        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
        createItem: function (action) {
            this.is_btn_loading = true;
            this.$Progress.start();
            this.$vh.ajax(
                this.ajax_url,
                this.data.item,
                this.createItemAfter,
                'post'
            );
        },
        //---------------------------------------------------------------------
        createItemAfter: function (data, res) {
            this.is_btn_loading = false;
            this.$Progress.finish();
            if(data)
            {
                this.$emit('eReloadList');

                if(this.local_action === 'save-and-new')
                {
                    this.saveAndNew()
                }
                if(this.local_action === 'save-and-close')
                {
                    this.saveAndClose()
                }
                if(this.local_action === 'save-and-clone')
                {
                    //do nothing
                }
            }


        },
        //---------------------------------------------------------------------
        updateItem: function (action) {
            this.is_btn_loading = true;
            this.$Progress.start();

            let url = this.ajax_url+"/"+this.data.item.id;

            this.$vh.ajax(
                url,
                this.data.item,
                this.updateItemAfter,
                'put'
            );
        },
        //---------------------------------------------------------------------
        updateItemAfter: function (data, res) {
            this.is_btn_loading = false;
            this.$Progress.finish();
            if(data)
            {
                this.$emit('eReloadList');
            }
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
        saveAndClose: function () {
            this.data.item = null;
            this.$router.push({name:'stores.list'});
        },
        //---------------------------------------------------------------------
        saveAndNew: function () {
            this.data.item = this.$vh.clone(this.assets.empty_item);
        },

        //---------------------------------------------------------------------
        getNewItem: function()
        {
            let new_item = {
                name: null,
                slug: null,
                is_active: null,
                details: null,
            };
            return new_item;
        },
        //---------------------------------------------------------------------
        resetNewItem: function()
        {
            let new_item = this.getNewItem();
            this.update('new_item', new_item);
        },
        //---------------------------------------------------------------------
        fillNewItem: function () {

            let new_item = {
                name: null,
                slug: null,
                is_active: null,
                details: null,
            };

            for(let key in new_item)
            {
                new_item[key] = this.new_item[key];
            }
            this.update('new_item', new_item);
        },
        //---------------------------------------------------------------------
        hasPermission: function(slug)
        {
            return this.$vaah.hasPermission(this.permissions, slug);
        },
        //---------------------------------------------------------------------
        closeCard: function ()
        {
            if(this.form_type === 'Update')
            {
                this.$router.push({name: 'stores.read', params:{id: this.data.item.id}})
            } else{
                this.$router.push({name: 'stores.list'})
            }
        }
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
    }
}

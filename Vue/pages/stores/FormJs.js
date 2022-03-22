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
                this.data.form_type = 'Update';
                if(!this.data.item)
                {
                    this.getItem();
                }
            } else
            {
                this.data.form_type = 'Create';
                this.data.item = this.$vh.clone(this.assets.empty_item);
            }
        },
        //---------------------------------------------------------------------
        setFormAction: function (action) {
            this.data.form.action = action;
            if(this.data.form.action === 'save')
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
            let params = {
                model_namespace: this.data.model,
                except: this.assets.fillable.except,
            };
            let url = this.base_url+'/faker';
            this.$vh.ajax(url, params, this.getFakerAfter, 'post');
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
            this.data.is_creating = true;
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
            this.data.is_creating = true;
            this.$Progress.finish();
            if(data)
            {

                this.$emit('eReloadList');

                if(this.data.form.action === 'save-and-new')
                {
                    this.saveAndNew()
                }
                if(this.data.form.action === 'save-and-close')
                {
                    this.saveAndClose()
                }
                if(this.data.form.action === 'save-and-clone')
                {
                    //do nothing
                }
            }


        },
        //---------------------------------------------------------------------
        updateItem: function (action) {
            this.data.form.is_button_loading = true;
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
            this.data.form.is_button_loading = false;
            this.$Progress.finish();
            if(data)
            {
                this.$emit('eReloadList');
            }
        },
        //---------------------------------------------------------------------
        getItem: function () {
            this.$Progress.start();
            let params = {};
            let url = this.ajax_url+'/'+this.$route.params.id;
            this.$vh.ajax(url, params, this.getItemAfter);
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
        hasPermission: function(slug)
        {
            return this.$vaah.hasPermission(this.permissions, slug);
        },
        //---------------------------------------------------------------------
        closeCard: function ()
        {
            if(this.data.type === 'Update')
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

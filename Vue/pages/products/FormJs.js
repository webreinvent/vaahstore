let namespace = 'products';

import AutoCompleteAjax from "../../vaahvue/reusable/AutoCompleteAjax";

export default {
    computed:{
        root() {return this.$store.getters['root/state']},
        base_url() {return this.$store.getters[namespace+'/state'].base_url},
        ajax_url() {return this.$store.getters[namespace+'/state'].ajax_url},
        assets() {return this.$store.getters[namespace+'/state'].assets},
        data() {return this.$store.getters[namespace+'/state'].data},
    },
    components:{
        AutoCompleteAjax,
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
            if(this.$route.name === 'products.update')
            {
                this.data.form.type = 'Update';
                if(!this.data.item)
                {
                    this.getItem();
                }
            } else
            {
                this.data.form.type = 'Create';
                this.data.item = this.$vh.clone(this.assets.empty_item);
                if(this.assets.default_store)
                {
                    this.data.item.vh_st_store_id = this.assets.default_store.id
                }
            }
        },
        //---------------------------------------------------------------------
        setFormAction: function (action) {
            this.data.form.action = action;
            if(this.data.form.type === 'Update')
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
                this.$router.push({name: 'products.list'});
            }
        },
        //---------------------------------------------------------------------
        saveAndClose: function () {
            this.data.item = null;
            this.$router.push({name:'products.list'});
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
            this.resetNewItem();
            if(this.data.type === 'Update')
            {
                this.$router.push({name: 'products.read', params:{id: this.data.item.id}})
            } else{
                this.$router.push({name: 'products.list'})
            }
        },
        //---------------------------------------------------------------------
        resetNewItem: function ()
        {
            this.data.item = this.$vh.clone(this.assets.empty_item);
        },
        //---------------------------------------------------------------------

        backToView: function ()
        {
            this.$router.push({name: 'products.read', params:{id: this.data.item.id}})
        },
        //---------------------------------------------------------------------
        setBrand: function (item) {
            this.data.item.vh_st_brand_id = item.id
        },
        //---------------------------------------------------------------------
        fnAfter: function (data, res) {
            this.$Progress.finish();
            if(data){
                this.update('list', data.list);
            }

        },
        //---------------------------------------------------------------------
    }
}

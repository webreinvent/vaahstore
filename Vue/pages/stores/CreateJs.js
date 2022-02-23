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
        },
    },
    mounted() {
        //----------------------------------------------------
        this.data.item = this.$vh.clone(this.data.inputs);
        //----------------------------------------------------
        //----------------------------------------------------
    },
    methods: {
        //---------------------------------------------------------------------
        onLoad: function()
        {
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
        resetActiveItem: function()
        {
            this.update('active_item', null);
        },
        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
        updateNewItem: function()
        {
            this.update('new_item', this.new_item);
        },
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        setLocalAction: function (action) {
            this.local_action = action;
            this.createItem();
        },
        //---------------------------------------------------------------------
        saveAndClose: function () {
            this.update('active_item', null);
            this.$router.push({name:'stores.list'});
        },
        //---------------------------------------------------------------------
        saveAndNew: function () {
            this.data.item = this.data.inputs;
        },
        //---------------------------------------------------------------------
        getFaker: function () {
            this.$Progress.start();
            this.params = {
                model_namespace: this.data.model
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
                this.data.item = data.fill;
            }
        },
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
                    this.saveAndClone()
                }
            }


        },
        //---------------------------------------------------------------------
        saveAndClone: function () {
            this.fillNewItem();
            this.$router.push({name:'stores.create'});
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
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
    }
}

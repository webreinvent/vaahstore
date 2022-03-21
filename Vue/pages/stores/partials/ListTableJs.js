
let namespace = 'stores';
export default {
    computed: {
        root() {return this.$store.getters['root/state']},
        ajax_url() {return this.$store.getters[namespace+'/state'].ajax_url},
        assets() {return this.$store.getters[namespace+'/state'].assets},
        data() {return this.$store.getters[namespace+'/state'].data},
    },
    components:{
    },
    data()
    {
        let obj = {
            namespace: namespace,
            page: null,
            icon_copy: "<i class='fas fa-copy'></i>"
        };

        return obj;
    },
    watch: {

    },
    mounted(){

    },
    methods: {
        //---------------------------------------------------------------------
        updatePage: function(newPageObject)
        {
            let payload = {
                key: 'data',
                value: newPageObject
            };
            this.$store.commit(namespace+'/updateState', payload)
        },
        //---------------------------------------------------------------------
        setRowClass: function(row, index)
        {
            if(this.data.item && row.id == this.data.item.id)
            {
                return 'is-selected';
            }

            if(row.deleted_at != null)
            {
                return 'is-danger';
            }

        },
        //---------------------------------------------------------------------
        setActiveItem: function (item) {
            this.data.item = item;
            this.$router.push({name: 'stores.read', params:{id:item.id}})
        },

        //---------------------------------------------------------------------
        copiedData: function (data) {
            this.$vaah.toastSuccess(['copied']);
        },
        //---------------------------------------------------------------------
        hasPermission: function(slug)
        {
            return this.$vaah.hasPermission(this.permissions, slug);
        },
        //---------------------------------------------------------------------
        isViewLarge: function ()
        {
            if(this.data.view==='large')
            {
                return true;
            }

            return false;
        }
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
    }
}

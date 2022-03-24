
let namespace = 'vendors';
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
        };

        return obj;
    },
    watch: {

    },
    mounted(){

    },
    methods: {
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
          this.$router.push({name: 'vendors.read', params:{id:item.id}})
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
      },
      //---------------------------------------------------------------------
      changeStatus: function (item) {

        let params = this.$vh.clone(item);

        if(params.is_active == 1){
          params.is_active = null;
        }else{
          params.is_active = 1;
        }

        this.$Progress.start();

        let url = this.ajax_url+'/'+item.id;
        this.$vh.ajax(url, params, this.changeStatusAfter, 'put');
      },
      //---------------------------------------------------------------------
      changeStatusAfter: function (data,res) {
        this.$emit('eReloadList');
      },
      //---------------------------------------------------------------------
      //---------------------------------------------------------------------
    }
}

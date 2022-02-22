<template>

    <footer>
      <div class="content has-text-centered">
        <p v-if="assets">
          {{root.assets.module.name}} <b>v{{root.assets.module.version}}</b>
        </p>
          <p v-else>
        store  <b>v0.0.1</b>
        </p>
      </div>
    </footer>

</template>

<script>
export default {
    computed:{
        root() {return this.$store.getters['root/state']},
        assets() {return this.$store.getters['root/state'].assets},
    },
    components:{

    },
    data()
    {
        let obj = {
        };

        return obj;
    },
    watch: {



    },
    mounted() {
        //---------------------------------------------------------------------

    },
    methods: {
        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
        //---------------------------------------------------------------------
    }
}
</script>

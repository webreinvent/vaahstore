<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import {useSettingStore} from '../../stores/store-settings'
import { vaah } from '../../vaahvue/pinia/vaah';

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useSettingStore();

const route = useRoute();
const useVaah = vaah();

onMounted(async () => {

    await store.getAssets();


    await store.getList();

});

</script>

<template>
    <div class="grid justify-content-center">
        <div class="col">
            <router-view></router-view>
        </div>
    </div>
</template>

<style scoped>

</style>

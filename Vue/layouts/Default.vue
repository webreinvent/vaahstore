<script setup>
import {onMounted, reactive} from "vue";

import Aside from '../components/Aside.vue';

import { useRootStore } from '../stores/root'
const rootStore = useRootStore();


onMounted(async () => {
    await rootStore.getAssets();
});


</script>


<template>
    <div v-if="rootStore.assets">

        <div class="flex items-start">
            <Aside/>
            <RouterView/>
        </div>

    </div>

</template>

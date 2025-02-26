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

    <div class="flex gap-1">
        <div><Aside/></div>
        <div class="w-full"><RouterView/></div>
    </div>

</template>

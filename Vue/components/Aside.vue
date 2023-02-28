<script setup>
import {reactive, ref} from 'vue';

import Menu from 'primevue/menu';

const inputs = {
}
const data = reactive(inputs);
const height = ref(window.innerHeight)

const menu = ref();

const items = ref([
    {
        label: 'Store',
        items: [
            {
                label: 'Dashboard',
                icon: 'fa-regular fa-chart-bar',
                to: "/"
            },
            {
                label: 'Brands',
                icon: 'fa-regular fa-chart-bar',
                to: "/brands"
            },
        ]
    },
]);

</script>
<template>

    <div v-if="height">
        <Menu :model="items" />
    </div>

</template>



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
        label: 'Vendor',
        items: [
            {
                label: 'Dashboard',
                icon: 'fa-regular fa-chart-bar',
                to: "/store"
            },
            {
                label: 'Product',
                icon: 'fa-regular fa-chart-bar',
                to: "/products"
            },
            {
                label: 'Brands',
                icon: 'fa-regular fa-chart-bar',
                to: "/brands"
            },
            {
                label: 'Store',
                icon: 'fa-regular fa-chart-bar',
                to: "/store"
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



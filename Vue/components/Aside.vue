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
                label: 'Store',
                icon: 'fa-regular fa-chart-bar',
                to: "/store"
            },
            {
                label: 'Vendor',
                icon: 'fa-regular fa-chart-bar',
                to: "/vendors"
            },
            {
                label: 'Product',
                icon: 'fa-regular fa-chart-bar',
                to: "/products"
            },
            {
                label: 'Product-Variations',
                icon: 'fa-regular fa-chart-bar',
                to: "/productvariations"
            },
            {
                label: 'Product-Vendor',
                icon: 'fa-regular fa-chart-bar',
                to: "/productvendors"
            },
            {
                label: 'Product-Medias',
                icon: 'fa-regular fa-chart-bar',
                to: "/productmedias"
            },
            {
                label: 'Product-Prices',
                icon: 'fa-regular fa-chart-bar',
                to: "/productprices"
            },
            {
                label: 'Brands',
                icon: 'fa-regular fa-chart-bar',
                to: "/brands"
            }
        ]
    },
]);

</script>
<template>

    <div v-if="height">
        <Menu :model="items" />
    </div>

</template>



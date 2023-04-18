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
                label: 'Stores',
                icon: 'fa-regular fa-chart-bar',
                to: "/store"
            },
            {
                label: 'Store Payment Methods',
                icon: 'fa-regular fa-chart-bar',
                to: "/storepaymentmethods"
            },
            {
                label: 'Vendors',
                icon: 'fa-regular fa-chart-bar',
                to: "/vendors"
            },
            {
                label: 'Vendor Products',
                icon: 'fa-regular fa-chart-bar',
                to: "/productvendors"
            },
            {
                label: 'Products',
                icon: 'fa-regular fa-chart-bar',
                to: "/products"
            },
            {
                label: 'Product Variations',
                icon: 'fa-regular fa-chart-bar',
                to: "/productvariations"
            },
            {
                label: 'Product-Attribute',
                icon: 'fa-regular fa-chart-bar',
                to: "/productAttribute"
            },
            {
                label: 'Product Medias',
                icon: 'fa-regular fa-chart-bar',
                to: "/productmedias"
            },
            {
                label: 'Product Stocks',
                icon: 'fa-regular fa-chart-bar',
                to: "/productstocks"
            },
            {
                label: 'Brands',
                icon: 'fa-regular fa-chart-bar',
                to: "/brands"
            },
            {
                label: 'Warehouses',
                icon: 'fa-regular fa-chart-bar',
                to: "/warehouses"
            },
            {
                label: 'Attributes',
                icon: 'fa-regular fa-chart-bar',
                to: "/attributes"
            },
            {
                label: 'Attributes-Group',
                icon: 'fa-regular fa-chart-bar',
                to: "/attributegroups"
            },
            {
                label: 'Orders',
                icon: 'fa-regular fa-chart-bar',
                to: "/orders"
            },
            {
                label: 'Payment Methods',
                icon: 'fa-regular fa-chart-bar',
                to: "/paymentmethods"
            },
            {
                label: 'Addresses',
                icon: 'fa-regular fa-chart-bar',
                to: "/addresses"
            },
            {
                label: 'Customer Groups',
                icon: 'fa-regular fa-chart-bar',
                to: "/customergroups"
            },
            {
                label: 'Whishlists',
                icon: 'fa-regular fa-chart-bar',
                to: "/whishlists"
            },
        ]
    },
]);

</script>
<template>

    <div v-if="height">
        <Menu class="col-12" :model="items" />
    </div>

</template>



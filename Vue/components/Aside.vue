<script setup>
import {reactive, ref} from 'vue';
import { useRoute } from 'vue-router';
import Menu from 'primevue/menu';

const inputs = {
}
const data = reactive(inputs);
const height = ref(window.innerHeight)
const route = useRoute();
const menu = ref();

function isActive(routePaths) {
    return routePaths.includes(route.path);
}

const selected_page = ref({
    menuitem: ({ props }) => ({
        class: route.matched && route.matched[1] &&
        route.matched[1].path === props.item.route ? 'p-focus' : ''
    })
});

const items = ref([
    {
        label: 'Store',
        items: [
            {
                label: 'Stores',
                icon: 'fa-regular fa-building',
                route: "/stores",
            },
            {
                label: 'Carts',
                icon: 'pi pi-shopping-cart',
                route: "/carts",
            },
            {
                label: 'Store Payment Methods',
                icon: 'fa-regular fa-credit-card',
                route: "/storepaymentmethods"
            },
            {
                label: 'Vendors',
                icon: 'fa-regular fa-handshake',
                route: "/vendors",
            },
            {
                label: 'Vendor Products',
                icon: 'fa-regular fa-clone',
                route: "/vendorproducts"
            },
            {
                label: 'Products',
                icon: 'fa-regular fa-clone',
                route: "/products"
            },
            {
                label: 'Product Variations',
                icon: 'fa-regular fa-clone',
                route: "/productvariations"
            },
            {
                label: 'Product Attributes',
                icon: 'fa-regular fa-clone',
                route: "/productattributes"
            },
            {
                label: 'Product Medias',
                icon: 'fa-regular fa-image',
                route: "/productmedias"
            },
            {
                label: 'Product Stocks',
                icon: 'fa-regular fa-chart-bar',
                route: "/productstocks"
            },
            {
                label: 'Brands',
                icon: 'fa-regular fa-copyright',
                route: "/brands"
            },
            {
                label: 'Warehouses',
                icon: 'fa-regular fa-building',
                route: "/warehouses"
            },
            {
                label: 'Attributes',
                icon: 'fa-regular fa-folder',
                route: "/attributes"
            },
            {
                label: 'Attributes Group',
                icon: 'fa-regular fa-folder-closed',
                route: "/attributesgroup"
            },
            {
                label: 'Orders',
                icon: 'fa-regular fa-check-square',
                route: "/orders"
            },
            {
                label: 'Payment Methods',
                icon: 'fa-regular fa-dollar',
                route: "/paymentmethods"
            },
            {
                label: 'Addresses',
                icon: 'fa-regular fa-address-card',
                route: "/addresses"
            },
            {
                label: 'Wishlists',
                icon: 'fa-regular fa-chart-bar',
                route: "/wishlists"
            },
            {
                label: 'Customers',
                icon: 'fa-regular fa-chart-bar',
                route: "/customers"
            },
            {
                label: 'Customer Groups',
                icon: 'fa-regular fa-user',
                route: "/customergroups"
            },
        ]
    },
]);

</script>
<template>


    <div v-if="height">
        <Menu :model="items" :pt="selected_page"  class="w-full" >
            <template #item="{ item, props }">
                <router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
                    <a v-ripple :href="href" v-bind="props.action" @click="navigate">
                        <span :class="item.icon" />
                        <span class="ml-2">{{ item.label }}</span>
                    </a>
                </router-link>
                <a v-else v-ripple :href="item.url" :target="item.target" :class="props.class">
                    <span :class="item.icon" />
                    <span class="ml-2">{{ item.label }}</span>
                </a>
            </template>
        </Menu>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
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
        class: route.path === props.item.route ? 'p-focus' : ''
    }),
    list: { class: 'p-0' },
    submenuheader: { class: 'sticky top-0 bg-white z-1 border-bottom-1 border-gray-200' }
});

const items = ref([
    {
        label: 'Store',
        items: [
            {
                label: 'Dashboard',
                icon: 'material-symbols-light:dashboard-outline-rounded',
                route: "/",
            },
            {
                label: 'Stores',
                icon: 'solar:shop-linear',
                route: "/stores",
            },
            {
                label: 'Carts',
                icon: 'hugeicons:shopping-cart-01',
                route: "/carts",
            },
            {
                label: 'Orders',
                icon: 'solar:box-outline',
                route: "/orders"
            },
            {
                label: 'Payments',
                icon: 'hugeicons:paypal',
                route: "/payments"
            },
            {
                label: 'Shipments',
                icon: 'fluent:vehicle-truck-profile-24-regular',
                route: "/shipments"
            },
            {
                label: 'Store Payment Methods',
                icon: 'stash:wallet',
                route: "/storepaymentmethods"
            },
            {
                label: 'Vendors',
                icon: 'uil:chat-bubble-user',
                route: "/vendors",
            },
            {
                label: 'Vendor Products',
                icon: 'mdi:package-variant-closed',
                route: "/vendorproducts"
            },
            {
                label: 'Products',
                icon: 'mdi:shopping-outline',
                route: "/products"
            },
            {
                label: 'Product Variations',
                icon: 'mdi:shape-outline',
                route: "/productvariations"
            },
            {
                label: 'Product Attributes',
                icon: 'mdi:tag-multiple-outline',
                route: "/productattributes"
            },
            {
                label: 'Product Medias',
                icon: 'solar:gallery-linear',
                route: "/productmedias"
            },
            {
                label: 'Product Stocks',
                icon: 'mdi:chart-bar',
                route: "/productstocks"
            },
            {
                label: 'Categories',
                icon: 'hugeicons:menu-circle',
                route: "/categories"
            },
            {
                label: 'Brands',
                icon: 'mage:tag',
                route: "/brands"
            },
            {
                label: 'Warehouses',
                icon: 'hugeicons:warehouse',
                route: "/warehouses"
            },
            {
                label: 'Attributes',
                icon: 'mage:folder',
                route: "/attributes"
            },
            {
                label: 'Attributes Group',
                icon: 'mdi:folder-multiple-outline',
                route: "/attributesgroup"
            },
            {
                label: 'Payment Methods',
                icon: 'flowbite:dollar-outline',
                route: "/paymentmethods"
            },
            {
                label: 'Addresses',
                icon: 'tabler:address-book',
                route: "/addresses"
            },
            {
                label: 'Wishlists',
                icon: 'mdi:heart-outline',
                route: "/wishlists"
            },
            {
                label: 'Customers',
                icon: 'mdi:account-outline',
                route: "/customers"
            },
            {
                label: 'Customer Groups',
                icon: 'mdi:account-group-outline',
                route: "/customergroups"
            },
            {
                label: 'Settings',
                icon: 'mdi:cog-outline',
                route: "/settings/general"
            },
        ]
    },
]);

</script>

<template>
    <div v-if="height" class="overflow-hidden rounded-lg sticky bg-transparent" style="top: 54px">
        <Menu :model="items" :pt="selected_page"
            class="w-full py-0 overflow-y-auto !bg-transparent shadow-none !border-0"
            style="max-height: calc(100vh - 53px);">
            <template #item="{ item, props }">
                <router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
                    <a v-ripple :href="href" v-bind="props.action" @click="navigate" class="rounded-xl !p-1.5 text-sm font-normal mb-2 overflow-visible"
                        :class="[route.path === item.route ? 'bg-blue-500 text-white ' : 'hover:bg-blue-500/10']">
                        <!-- <span :class="item.icon" class="p-2 bg-white rounded-lg text-info-500 shadow-card" /> -->
                            <Icon :icon="item.icon" class="size-7 p-0.5 rounded-lg bg-white text-info-500 shrink-0" :class="[route.path === item.route ? 'shadow-none' : 'shadow-card']" />
                        <span class="ml-2">{{ item.label }}</span>
                    </a>
                </router-link>
                <a v-else v-ripple :href="item.url" :target="item.target" :class="[props.class, 'hover:bg-gray-50']">
                    <span :class="item.icon" />
                    <span class="ml-2">{{ item.label }}</span>
                </a>
            </template>
        </Menu>
    </div>
</template>
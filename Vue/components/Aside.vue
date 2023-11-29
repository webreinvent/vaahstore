<script setup>
import {reactive, ref} from 'vue';
import {useRoute} from 'vue-router';
import Menu from 'primevue/menu';
const route = useRoute();
const inputs = {
}
const data = reactive(inputs);
const height = ref(window.innerHeight)

const menu = ref();
const route = useRoute();
const items = ref([
    {
        label: 'Store',
        items: [
            {
                label: 'Stores',
                icon: 'fa-regular fa-building',
                to: "/stores",
                class: { 'highlight': route.path.startsWith('/stores') }
            },
            {
                label: 'Store Payment Methods',
                icon: 'fa-regular fa-credit-card',
                to: "/storepaymentmethods"
            },
            {
                label: 'Vendors',
                icon: 'fa-regular fa-handshake',
                to: "/vendors",
            },
            {
                label: 'Vendor Products',
                icon: 'fa-regular fa-clone',
                to: "/productvendors"
            },
            {
                label: 'Products',
                icon: 'fa-regular fa-clone',
                to: "/products"
            },
            {
                label: 'Product Variations',
                icon: 'fa-regular fa-clone',
                to: "/productvariations"
            },
            {
                label: 'Product Attributes',
                icon: 'fa-regular fa-clone',
                to: "/productattributes"
            },
            {
                label: 'Product Medias',
                icon: 'fa-regular fa-image',
                to: "/productmedias"
            },
            {
                label: 'Product Stocks',
                icon: 'fa-regular fa-chart-bar',
                to: "/productstocks"
            },
            {
                label: 'Brands',
                icon: 'fa-regular fa-copyright',
                to: "/brands"
            },
            {
                label: 'Warehouses',
                icon: 'fa-regular fa-building',
                to: "/warehouses"
            },
            {
                label: 'Attributes',
                icon: 'fa-regular fa-folder',
                to: "/attributes"
            },
            {
                label: 'Attributes Group',
                icon: 'fa-regular fa-folder-closed',
                to: "/attributegroups"
            },
            {
                label: 'Orders',
                icon: 'fa-regular fa-check-square',
                to: "/orders"
            },
            {
                label: 'Payment Methods',
                icon: 'fa-regular fa-dollar',
                to: "/paymentmethods"
            },
            {
                label: 'Addresses',
                icon: 'fa-regular fa-address-card',
                to: "/addresses"
            },
            {
                label: 'Customer Groups',
                icon: 'fa-regular fa-user',
                to: "/customergroups"
            },
            {
                label: 'Wishlists',
                icon: 'fa-regular fa-chart-bar',
                to: "/whishlists"
            },
        ]
    },
]);

const updateSelectedItem = () => {
    const parts = route.path.split('/');
    const value = parts[1];

    items.value.forEach((item) => {
        item.items.forEach((sub_item) => {
            const sub_item_to = sub_item.to.replace(/^\//, '');
            sub_item.style = sub_item_to === value ? { 'background': '#e5e7eb' } : {};
        });
    });
};

watch(() => route.path, updateSelectedItem);

onMounted(updateSelectedItem);
</script>
<template>

    <div v-if="height">
        <Menu class="col-12" :model="items" />
    </div>

</template>
<!--<style scoped>-->
<!--.highlight {-->
<!--    background-color: yellow; // Customize the highlight style-->
<!--}-->
<!--</style>-->


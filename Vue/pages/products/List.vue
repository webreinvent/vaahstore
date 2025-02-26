<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useProductStore} from '../../stores/store-products'
import {useRootStore} from '../../stores/root'
import {useImportStore} from "../../stores/store-import";
import CSV from "dom-csv.js";
import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Upload from './../import/components/Upload.vue';
import Map from './../import/components/Map.vue';
import Preview from './../import/components/Preview.vue';
import Result from './../import/components/Result.vue';

const store = useProductStore();
const root = useRootStore();
const route = useRoute();
const importStore = useImportStore()
import {useConfirm} from "primevue/useconfirm";


const confirm = useConfirm();

function handleScreenResize() {
    store.setScreenSize();
}

const base_url = ref('');
onMounted(async () => {
    document.title = 'Products - Store';
    base_url.value = root.ajax_url.replace('backend/store', '/');
    await importStore.getAssets();
    /**
     * call onLoad action when List view loads
     */
    await store.onLoad(route);
    await store.topSellingProducts();
    /**
     * watch routes to update view, column width
     * and get new item when routes get changed
     */
    await store.watchRoutes(route);

    /**
     * watch states like `query.filter` to
     * call specific actions if a state gets
     * changed
     */
    await store.watchStates();

    /**
     * fetch assets required for the crud
     * operation
     */
    await store.getAssets();

    /**
     * fetch list of records
     */
    await store.getList();
    store.getQuickFilterMenu();
    await store.getListCreateMenu();
    await store.watchQuantity();
    store.getExportMenu();
    store.watchSelectedItem();

    // setTimeout(() => {
    //     store.disableActiveCart();
    // }, 1000);

});

//--------form_menu
const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
//--------/form_menu
const quick_filter_menu_state = ref();
const toggleQuickFilterState = (event) => {
    quick_filter_menu_state.value.toggle(event);
};

</script>
<template>

    <Message v-show="store.show_cart_msg" icon="pi pi-shopping-cart" severity="success" :sticky="true" :life="1000"
             @close="store.disableActiveCart()">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                {{ store.active_cart_user_name }} ( {{ store.total_cart_product }} )
            </div>
            <div>
                <Button @click="store.viewCart(store.cart_id)" class="line-height-1 mr-2" label="View Cart" link/>
                <!--                <Button @click="store.disableActiveCart()" >X</Button>-->
            </div>
        </div>
    </Message>

    <div class="grid" v-if="store.assets">

        <div :class="'col-'+store.list_view_width">



            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div>
                            <b class="mr-1">Products</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total">
                            </Badge>
                        </div>

                    </div>

                </template>
                <div class="flex gap-2 mb-1" >
                    <div class="w-full bg-white p-3 border-1 border-gray-200 rounded-sm mb-2">
                        <div class=" justify-content-between " >


                            <div class="flex flex-wrap  gap-2 mt-2">
                                <Card class="border-1 border-gray-200 border-round-sm overflow-hidden">
                                    <template #title>
                                        <div class="flex align-items-center justify-content-between">
                                            <h2 class="text-lg">Top Selling Products</h2>
                                            <Chip
                                                v-if="store.filter_all"
                                                class="white-space-nowrap align-items-center"
                                                :style="{
                                                                fontSize: '11px',
                                                                marginRight: '5px',
                                                                padding: '1px 8px',
                                                                fontWeight:'600',
                                                              }"
                                                :pt="{
                                                                removeIcon: {
                                                                    style: {
                                                                        width: '12px',
                                                                        height: '12px',
                                                                        marginLeft: '6px'
                                                                    }
                                                                }
                                                              }"
                                                :label="store.filter_all?.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"
                                                removable
                                                @remove="store.loadProductChartsData()"
                                            />
                                            <Button
                                                data-testid="products_chart-quick_filter"
                                                type="button"
                                                @click="toggleQuickFilterState($event)"
                                                aria-haspopup="true"
                                                aria-controls="quick_filter_menu_state"
                                                class="ml-1 p-button-sm px-1"

                                                icon="pi pi-filter"
                                            >
                                            </Button>
                                            <Menu ref="quick_filter_menu_state"
                                                  :model="store.quick_filter_menu"
                                                  :popup="true"/>
                                        </div>
                                    </template>

                                    <template #content>
                                        <div class="max-h-14rem overflow-y-auto">
                                            <DataTable
                                                :value="store.top_selling_products"
                                                dataKey="id"

                                                class="p-datatable-sm p-datatable-hoverable-rows"
                                                :nullSortOrder="-1"
                                                v-model:selection="store.action.items"
                                                stripedRows
                                                responsiveLayout="scroll"
                                            >
                                                <Column field="variation_name" header="" class="overflow-wrap-anywhere">
                                                    <template #body="prop">
                                                        <div class="flex ">
                                                            <div class="product_img">
                                                                <div v-if="Array.isArray(prop.data?.image_urls) && prop.data?.image_urls.length > 0">
                                                                    <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">
                                                                        <Image preview
                                                                               :src="base_url + '/' + imageUrl"
                                                                               alt="Error" class="shadow-4" width="35"/>
                                                                    </div>
                                                                </div>
                                                                <div v-else>
                                                                    <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                                         alt="Error" class="shadow-4" width="35"/>
                                                                </div>
                                                            </div>
                                                            <div class="product_desc ml-3">
                                                                <h4>{{ prop.data?.name }}</h4>
                                                                <p><b> {{ prop.data?.total_sales }}</b> Sold</p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </Column>
                                                <template #empty>
                                                    <div class="text-center py-3">
                                                        No records found.
                                                    </div>
                                                </template>
                                            </DataTable>
                                        </div>
                                    </template>
                                </Card>
                                <Card class="border-1 border-gray-200 border-round-sm overflow-hidden">
                                    <template #title>
                                        <div class="flex align-items-center justify-content-between">
                                            <h2 class="text-lg">Top Brands</h2>

                                        </div>
                                    </template>

                                    <template #content>
                                        <DataTable
                                            :value="store.top_selling_brands"
                                            dataKey="id"

                                            class="p-datatable-sm p-datatable-hoverable-rows"
                                            :nullSortOrder="-1"
                                            v-model:selection="store.action.items"
                                            stripedRows
                                            responsiveLayout="scroll"
                                        >
                                            <Column field="variation_name" header="" class="overflow-wrap-anywhere">
                                                <template #body="prop">
                                                    <div class="flex ">
                                                        <div class="product_img">
                                                            <div v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">
                                                                <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">
                                                                    <Image preview
                                                                           :src="base_url + '/' + imageUrl"
                                                                           alt="Error" class="shadow-4" width="35"/>
                                                                </div>
                                                            </div>
                                                            <div v-else>
                                                                <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                                     alt="Error" class="shadow-4" width="35"/>
                                                            </div>
                                                        </div>
                                                        <div class="product_desc ml-3">
                                                            <h4>{{ prop.data.name }}</h4>
                                                            <!--                                <p><b> {{ prop.data.total_sales }}</b> Items</p>-->
                                                        </div>
                                                    </div>
                                                </template>
                                            </Column>
                                            <template #empty>
                                                <div class="text-center py-3">
                                                    No records found.
                                                </div>
                                            </template>
                                        </DataTable>
                                    </template>
                                </Card>
                                <Card class="border-1 border-gray-200 border-round-sm overflow-hidden">
                                    <template #title>
                                        <div class="flex align-items-center justify-content-between">
                                            <h2 class="text-lg">Top Categories</h2>

                                        </div>
                                    </template>

                                    <template #content>
                                        <DataTable
                                            :value="store.top_selling_categories"
                                            dataKey="id"

                                            class="p-datatable-sm p-datatable-hoverable-rows"
                                            :nullSortOrder="-1"
                                            v-model:selection="store.action.items"
                                            stripedRows
                                            responsiveLayout="scroll"
                                        >
                                            <Column field="variation_name" header="" class="overflow-wrap-anywhere">
                                                <template #body="prop">
                                                    <div class="flex ">
                                                        <div class="product_img">
                                                            <div v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">
                                                                <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">
                                                                    <Image preview
                                                                           :src="base_url + '/' + imageUrl"
                                                                           alt="Error" class="shadow-4" width="35"/>
                                                                </div>
                                                            </div>
                                                            <div v-else>
                                                                <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                                     alt="Error" class="shadow-4" width="35"/>
                                                            </div>
                                                        </div>
                                                        <div class="product_desc ml-3">
                                                            <h4>{{ prop.data.name }}</h4>
                                                            <!--                                <p><b> {{ prop.data.total_sales }}</b> Items</p>-->
                                                        </div>
                                                    </div>
                                                </template>
                                            </Column>
                                            <template #empty>
                                                <div class="text-center py-3">
                                                    No records found.
                                                </div>
                                            </template>
                                        </DataTable>
                                    </template>
                                </Card>
                            </div>

                        </div>
                    </div>
                </div>
                <template #icons>

                    <div class="p-inputgroup">
                        <Button

                            class="p-button-sm py-2 white-space-nowrap min-w-max"
                            style="font-weight:600;"
                            data-testid="products-bulk_import"
                            @click="store.toImport()">
                            Bulk Import
                        </Button>
                        <Button data-testid="products-list-create"
                                class="p-button-sm"
                                :disabled="!store.assets.permissions.includes('can-update-module')"
                                @click="store.toForm()">
                            <i class="pi pi-plus mr-1"></i>
                            Create
                        </Button>

                        <Button data-testid="products-list-reload"
                                class="p-button-sm"
                                @click="store.reloadPage()">
                            <i class="pi pi-refresh mr-1"></i>
                        </Button>

                        <!--form_menu-->

                        <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                                type="button"
                                @click="toggleCreateMenu"
                                class="p-button-sm"
                                :disabled="!store.assets.permissions.includes('can-update-module')"
                                data-testid="products-create-menu"
                                icon="pi pi-angle-down"
                                aria-haspopup="true"/>

                        <Menu ref="create_menu"
                              :model="store.list_create_menu"
                              :popup="true"/>

                        <!--/form_menu-->

                    </div>

                </template>

                <Actions/>

                <Table/>

            </Panel>
        </div>

        <RouterView/>

    </div>


</template>

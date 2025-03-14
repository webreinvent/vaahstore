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
import TileInfo from "../../components/TileInfo.vue";

const confirm = useConfirm();

function handleScreenResize() {
    store.setScreenSize();
}

const base_url = ref('');
onMounted(async () => {
    document.title = 'Products - Store';
    base_url.value = root.ajax_url.replace('backend/store', '/');
    await importStore.getAssets();

    window.addEventListener('resize', handleScreenResize);
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

    <div class="w-full" v-if="store.assets">

        <div class="lg:flex  lg:space-x-4 items-start">

            <div v-if="store.getLeftColumnClasses"
                 :class="store.getLeftColumnClasses"

                 class="mb-4  lg:mb-0">

                <Panel >
                    <template #header>

                        <div class="flex flex-row">
                            <div class="flex items-center gap-2">
                                <div>
                                <Icon icon="bx:basket" width="18" height="18"  style="color: #111113" />
                                </div>
                                <div class="flex items-center mt-1">
                                <b class="mr-1">Products</b>
                                <p class="font-bold text-xs"  v-if="store.list && store.list.total > 0"
                                >{{store.list.total}}
                                </p>
                                    </div>
                            </div>

                        </div>

                    </template>
                    <div class="flex gap-2  mb-1" v-if=" store.isListView()">
                        <div class="w-full bg-transparent border-none  rounded-sm mb-2">
                            <div class=" justify-content-between " >
                                <div class="flex border-none  gap-2 mt-2">
                                    <Card class="w-1/3" >
                                        <template #title>
                                            <div class="flex align-items-center justify-content-between">
                                                <div class="flex items-center">
                                                    <Icon class="mr-2" icon="ph:chart-line-up" width="20" height="20"  style="color: #474C5D" />
                                                <h2 class="text-sm">Top Selling Products</h2>
                                                    </div>
                                                <div>
                                                <Chip
                                                    v-if="store.filter_all"
                                                    class="white-space-nowrap  align-items-center"
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
                                                    class="p-button-sm"
                                                >
                                                    <Icon icon="iconoir:filter" width="16" height="16"  style="color: #474C5D" />
                                                </Button>
                                                </div>
                                                <Menu ref="quick_filter_menu_state"
                                                      :model="store.quick_filter_menu"
                                                      :popup="true"/>
                                            </div>
                                        </template>
                                        <template #content>
                                            <div class="max-h-14rem  overflow-y-auto">
                                                <div class="border-b border-[#EDEDF1] " v-for="product in store.top_selling_products" :key="product.id">
                                                <TileInfo :product="product" :baseUrl="base_url" :showRating="true" />
                                                </div>
<!--                                                <DataTable-->
<!--                                                    :value="store.top_selling_products"-->
<!--                                                    dataKey="id"-->

<!--                                                    class="p-datatable-sm p-datatable-hoverable-rows"-->
<!--                                                    :nullSortOrder="-1"-->
<!--                                                    v-model:selection="store.action.items"-->
<!--                                                    stripedRows-->
<!--                                                    responsiveLayout="scroll"-->
<!--                                                >-->
<!--                                                    <Column field="variation_name" header="" class="overflow-wrap-anywhere">-->
<!--                                                        <template #body="prop">-->

<!--&lt;!&ndash;                                                            <div class="flex  items-center">&ndash;&gt;-->
<!--&lt;!&ndash;                                                                <div class="product_img">&ndash;&gt;-->
<!--&lt;!&ndash;                                                                    <div v-if="Array.isArray(prop.data?.image_urls) && prop.data?.image_urls.length > 0">&ndash;&gt;-->
<!--&lt;!&ndash;                                                                        <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">&ndash;&gt;-->
<!--&lt;!&ndash;                                                                            <Image preview&ndash;&gt;-->
<!--&lt;!&ndash;                                                                                   :src="base_url + '/' + imageUrl"&ndash;&gt;-->
<!--&lt;!&ndash;                                                                                   alt="Error" class="shadow-4" width="35"/>&ndash;&gt;-->
<!--&lt;!&ndash;                                                                        </div>&ndash;&gt;-->
<!--&lt;!&ndash;                                                                    </div>&ndash;&gt;-->
<!--&lt;!&ndash;                                                                    <div v-else>&ndash;&gt;-->
<!--&lt;!&ndash;                                                                        <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"&ndash;&gt;-->
<!--&lt;!&ndash;                                                                             alt="Error" class="shadow-4" width="35"/>&ndash;&gt;-->
<!--&lt;!&ndash;                                                                    </div>&ndash;&gt;-->
<!--&lt;!&ndash;                                                                </div>&ndash;&gt;-->
<!--&lt;!&ndash;                                                                <div class=" product_desc  ml-3">&ndash;&gt;-->
<!--&lt;!&ndash;                                                                    <h4>{{ prop.data?.name }}</h4>&ndash;&gt;-->
<!--&lt;!&ndash;                                                                    <p><b class="text-gray-400 font-normal">  {{ prop.data?.total_sales }}</b> <span class="text-gray-400">Units Sold</span></p>&ndash;&gt;-->
<!--&lt;!&ndash;                                                                </div>&ndash;&gt;-->
<!--&lt;!&ndash;                                                            </div>&ndash;&gt;-->
<!--                                                        </template>-->
<!--                                                    </Column>-->
<!--                                                    <template #empty>-->
<!--                                                        <div class="text-center py-3">-->
<!--                                                            No records found.-->
<!--                                                        </div>-->
<!--                                                    </template>-->
<!--                                                </DataTable>-->
                                            </div>
                                        </template>
                                    </Card>
                                    <Card class="w-1/3">
                                        <template #title>
                                            <div class="flex align-items-center  justify-content-between">
                                                <div class="flex items-center w-full justify-between ">
                                                    <div class="flex items-center py-2">
                                                    <Icon class="mr-2" icon="mage:tag" width="20" height="20"  style="color: #474C5D" />
                                                    <h2 class="text-sm">Top Brands</h2>
                                                        </div>
                                                </div>
                                            </div>
                                        </template>

                                        <template #content>
                                            <div class="max-h-14rem overflow-y-auto">
                                            <div class="border-b border-[#EDEDF1]" v-for="product in store.top_selling_brands" :key="product.id">
                                                <TileInfo :product="product" :baseUrl="base_url" :showRating="true" />
                                            </div>
                                            </div>
<!--                                            <DataTable-->
<!--                                                :value="store.top_selling_brands"-->
<!--                                                dataKey="id"-->

<!--                                                class="p-datatable-sm p-datatable-hoverable-rows"-->
<!--                                                :nullSortOrder="-1"-->
<!--                                                v-model:selection="store.action.items"-->
<!--                                                stripedRows-->
<!--                                                responsiveLayout="scroll"-->
<!--                                            >-->
<!--                                                <Column field="variation_name" header="" class="overflow-wrap-anywhere">-->
<!--                                                    <template #body="prop">-->
<!--                                                        <div class="flex ">-->
<!--                                                            <div class="product_img">-->
<!--                                                                <div v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">-->
<!--                                                                    <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">-->
<!--                                                                        <Image preview-->
<!--                                                                               :src="imageUrl"-->
<!--                                                                               alt="Error" class="shadow-4" width="35"/>-->
<!--                                                                    </div>-->
<!--                                                                </div>-->
<!--                                                                <div v-else>-->
<!--                                                                    <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"-->
<!--                                                                         alt="Error" class="shadow-4" width="35"/>-->
<!--                                                                </div>-->
<!--                                                            </div>-->
<!--                                                            <div class="product_desc ml-3">-->
<!--                                                                <h4>{{ prop.data.name }}</h4>-->
<!--                                                                &lt;!&ndash;                                <p><b> {{ prop.data.total_sales }}</b> Items</p>&ndash;&gt;-->
<!--                                                            </div>-->
<!--                                                        </div>-->
<!--                                                    </template>-->
<!--                                                </Column>-->
<!--                                                <template #empty>-->
<!--                                                    <div class="text-center py-3">-->
<!--                                                        No records found.-->
<!--                                                    </div>-->
<!--                                                </template>-->
<!--                                            </DataTable>-->
                                        </template>
                                    </Card>
                                    <Card class="w-1/3 ">
                                        <template #title>
                                            <div class="flex items-center py-2">
                                                <Icon class="mr-2" icon="gravity-ui:circles-4-square" width="20" height="20"  style="color: #474C5D" />
                                                <h2 class="text-sm">Top Categories</h2>
                                            </div>
                                        </template>

                                        <template #content>
                                            <div class="max-h-14rem overflow-y-auto">
<!--                                                <div class="!grid grid-cols-3 gap-x-2 gap-y-8 pb-12">-->
<!--                                                    <VendorSale :vendorData="store.top_selling_categories" />-->
<!--                                                </div>-->
                                                <div v-for="product in store.top_selling_categories" :key="product.id">
                                                    <TileInfo :product="product" :baseUrl="base_url" :showRating="true" />

                                                </div>
                                            </div>
<!--                                            <div class="!grid grid-cols-3 gap-x-2 gap-y-8 pb-12">-->
<!--                                               -->
<!--                                            </div>-->
<!--                                            <DataTable-->
<!--                                                :value="store.top_selling_categories"-->
<!--                                                dataKey="id"-->

<!--                                                class="p-datatable-sm p-datatable-hoverable-rows"-->
<!--                                                :nullSortOrder="-1"-->
<!--                                                v-model:selection="store.action.items"-->
<!--                                                stripedRows-->
<!--                                                responsiveLayout="scroll"-->
<!--                                            >-->
<!--                                                <Column field="variation_name" header="" class="overflow-wrap-anywhere">-->
<!--                                                    <template #body="prop">-->
<!--                                                        <div class="flex ">-->
<!--                                                            <div class="product_img">-->
<!--                                                                <div v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">-->
<!--                                                                    <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">-->
<!--                                                                        <Image preview-->
<!--                                                                               :src="base_url + '/' + imageUrl"-->
<!--                                                                               alt="Error" class="shadow-4" width="35"/>-->
<!--                                                                    </div>-->
<!--                                                                </div>-->
<!--                                                                <div v-else>-->
<!--                                                                    <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"-->
<!--                                                                         alt="Error" class="shadow-4" width="35"/>-->
<!--                                                                </div>-->
<!--                                                            </div>-->
<!--                                                            <div class="product_desc ml-3">-->
<!--                                                                <h4>{{ prop.data.name }}</h4>-->
<!--                                                                &lt;!&ndash;                                <p><b> {{ prop.data.total_sales }}</b> Items</p>&ndash;&gt;-->
<!--                                                            </div>-->
<!--                                                        </div>-->
<!--                                                    </template>-->
<!--                                                </Column>-->
<!--                                                <template #empty>-->
<!--                                                    <div class="text-center py-3">-->
<!--                                                        No records found.-->
<!--                                                    </div>-->
<!--                                                </template>-->
<!--                                            </DataTable>-->
                                        </template>
                                    </Card>
                                </div>

                            </div>
                        </div>
                    </div>
                    <template #icons>

<!--                        <InputGroup>-->
                        <div class=" gap-2 flex">
                            <Button
                                size="small"
                                data-testid="products-bulk_import"
                                @click="store.toImport()">
                                <Icon icon="oui:import" width="16" height="16"  style="color: #7b7a7a" />
                                Bulk Import
                            </Button
        >
                            <Button data-testid="products-list-create"
                                    size="small"
                                    :disabled="!store.assets.permissions.includes('can-update-module')"
                                    @click="store.toForm()">
                                <Icon class="-mr-1" icon="ph:plus-light" width="16" height="16"  style="color: #7b7a7a" />
                                Create
                            </Button>

                            <Button data-testid="products-list-reload"
                                    size="small"
                                    @click="store.reloadPage()">
                                <Icon class="mx-1"  icon="famicons:reload-sharp" width="16" height="16"  style="color: #7b7a7a" />
                            </Button>

                            <!--form_menu-->

                            <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                                    type="button"
                                    @click="toggleCreateMenu"
                                    size="small"
                                    :disabled="!store.assets.permissions.includes('can-update-module')"
                                    data-testid="products-create-menu"
                                    icon="pi pi-angle-down"
                                    aria-haspopup="true"/>

                            <Menu ref="create_menu"
                                  :model="store.list_create_menu"
                                  :popup="true"/>
                        </div>

                            <!--/form_menu-->

<!--                        </InputGroup>-->

                    </template>


                    <Card class="">
                        <template #content>
                            <Actions/>
                    <Table/>
                        </template>
                    </Card>

                </Panel>
            </div>

            <div v-if="store.getRightColumnClasses"
                 :class="store.getRightColumnClasses">

                <RouterView/>

            </div>
            <!--/right-->



        </div>
    </div>




</template>

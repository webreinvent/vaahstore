<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useProductStockStore} from '../../stores/store-productstocks'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";

const store = useProductStockStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Product Stocks - Store';
    /**
     * call onLoad action when List view loads
     */
    await store.onLoad(route);

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

    await store.getListCreateMenu();

});

//--------form_menu
const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
//--------/form_menu


</script>
<template>

    <div class="grid" v-if="store.assets">

        <div :class="'col-'+store.list_view_width">
            <Panel class="is-small">


                <div class="flex flex-wrap justify-content-center  gap-2 mt-2">
                    <Card class="border-1 border-gray-200 border-round-sm overflow-hidden">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Highest Stock</h2>

                            <Button
                                data-testid="inventories-quick_filter"
                                type="button"
                                @click="store.QuickFilter()"
                                aria-haspopup="true"
                                aria-controls="quick_filter_menu_state"
                                class="ml-1 p-button-sm px-1"
                                label="All"
                                icon="pi pi-filter"
                            >
                            </Button>

                        </div>
                    </template>

                    <template #content>
                        <div class="max-h-14rem overflow-y-auto">


                            <DataTable :value="store.highest_stock"
                                       dataKey="id"

                                       class="p-datatable-sm p-datatable-hoverable-rows"
                                       :nullSortOrder="-1"
                                       v-model:selection="store.action.items"
                                       stripedRows
                                       responsiveLayout="scroll">
                                <Column field="" header="" class="overflow-wrap-anywhere"
                                        style="width:120px;">
                                    <template #body="prop">
                                        <div class="flex mt-4">
                                            <div class="product_img">
                                                <div v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">
                                                    <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">
                                                        <Image preview
                                                               :src="'http://localhost/shivam-g001/store-dev/public/' + imageUrl"
                                                               alt="Error" class="shadow-4" width="64"/>
                                                    </div>
                                                </div>
                                                <div v-else>
                                                    <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                         alt="Error" class="shadow-4" width="64"/>
                                                </div>
                                            </div>
                                            <div class=" ml-3">
                                                <h4>
                                                    {{
                                                    prop.data.product && prop.data.productVariation
                                                    ? prop.data.product.name + '-' + prop.data.productVariation.name
                                                    : prop.data.name
                                                    }}
                                                </h4>
                                                <p  v-if="prop.data.stock"><b>Stocks:</b>

                                                        {{ prop.data.stock }}

                                                </p>
<!--                                                <p v-if="prop.data.pivot.price !== null && prop.data.pivot.price !== undefined">-->
<!--                                                    ₹{{ prop.data.pivot.price }}</p>-->
                                                <ProgressBar
                                                    style="width: 15rem; height:12px"
                                                    :value=prop.data.stock_percentage

                                                >

                                                </ProgressBar>
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
                                                        <h2 class="text-lg">Lowest Stock</h2>

                                                        <Button
                                                            data-testid="inventories-quick_filter"
                                                            type="button"
                                                            @click="store.QuickLowFilter()"
                                                            aria-haspopup="true"
                                                            aria-controls="quick_filter_menu_state"
                                                            class="ml-1 p-button-sm px-1"

                                                            icon="pi pi-filter"
                                                        >
                                                        </Button>

                                                    </div>
                                                </template>

                                                <template #content>
                                                    <div class="max-h-14rem overflow-y-auto">

                                                        <DataTable :value="store.lowest_stock"
                                                                   dataKey="id"

                                                                   class="p-datatable-sm p-datatable-hoverable-rows"
                                                                   :nullSortOrder="-1"
                                                                   v-model:selection="store.action.items"
                                                                   stripedRows
                                                                   responsiveLayout="scroll">
                                                            <Column field="" header="" class="overflow-wrap-anywhere"
                                                                    style="width:120px;">
                                                                <template #body="prop">
                                                                    <div class="flex mt-4">
                                                                        <div class="product_img">
                                                                            <div v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">
                                                                                <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">
                                                                                    <Image preview
                                                                                           :src="'http://localhost/shivam-g001/store-dev/public/' + imageUrl"
                                                                                           alt="Error" class="shadow-4" width="64"/>
                                                                                </div>
                                                                            </div>
                                                                            <div v-else>
                                                                                <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                                                     alt="Error" class="shadow-4" width="64"/>
                                                                            </div>
                                                                        </div>
                                                                        <div class=" ml-3">
                                                                            <h4>
                                                                                {{
                                                                                prop.data.product && prop.data.productVariation
                                                                                ? prop.data.product.name + '-' + prop.data.productVariation.name
                                                                                : prop.data.name
                                                                                }}
                                                                            </h4>
                                                                            <p v-if="prop.data.stock"><b>Stocks:</b>

                                                                                {{ prop.data.stock }}

                                                                            </p>
                                                                            <!--                                                <p v-if="prop.data.pivot.price !== null && prop.data.pivot.price !== undefined">-->
                                                                            <!--                                                    ₹{{ prop.data.pivot.price }}</p>-->
                                                                            <ProgressBar
                                                                                style="width: 15rem; height:10px"
                                                                                :value=prop.data.stock_percentage
                                                                            >

                                                                            </ProgressBar>
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
                </div>
                                            <template class="p-1" #header>

                                                <div class="flex flex-row">
                                                    <div >
                                                        <b class="mr-1">Product Stocks</b>
                                                        <Badge v-if="store.list && store.list.total > 0"
                                                               :value="store.list.total">
                                                        </Badge>
                                                    </div>

                                                </div>

                                            </template>

                                            <template #icons>

                                                <div class="p-inputgroup">

                                                <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                                                        data-testid="productstocks-list-create"
                                                        class="p-button-sm"
                                                        @click="store.toForm()">
                                                    <i class="pi pi-plus mr-1"></i>
                                                    Create
                                                </Button>

                                                <Button data-testid="productstocks-list-reload"
                                                        class="p-button-sm"
                                                        @click="store.toReload()">
                                                    <i class="pi pi-refresh mr-1"></i>
                                                </Button>

                                                <!--form_menu-->

                    <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                        v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                        type="button"
                        @click="toggleCreateMenu"
                        class="p-button-sm"
                        data-testid="productstocks-create-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="create_menu"
                          :disabled="!store.assets.permissions.includes('can-update-module')"
                          :model="store.list_create_menu"
                          :popup="true" />

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

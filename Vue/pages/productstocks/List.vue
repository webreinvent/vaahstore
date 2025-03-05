<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRoute } from 'vue-router';

import { useProductStockStore } from '../../stores/store-productstocks'
import { useRootStore } from '@/stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";

const store = useProductStockStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
import TileInfo from "../../components/TileInfo.vue";
const confirm = useConfirm();
function handleScreenResize() {
    store.setScreenSize();
}
const base_url = ref('');
onMounted(async () => {
    document.title = 'Product Stocks - Store';
    window.addEventListener('resize', handleScreenResize);
    base_url.value = root.ajax_url.replace('backend/store', '/');
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

    <div class="w-full" v-if="store.assets">

        <div class="lg:flex lg:space-x-4 items-start">
            <!--left-->
            <div v-if="store.getLeftColumnClasses" :class="store.getLeftColumnClasses" class="mb-4 lg:mb-0">
                <Panel :pt="root.panel_pt">
                    <template #header>
                        <div class="flex flex-row">
                            <div>
                                <b class="mr-1">Product Stocks</b>
                                <Badge v-if="store.list && store.list.total > 0" :value="store.list.total">
                                </Badge>
                            </div>

                        </div>

                    </template>
                    <div v-if="store.isListView()" class="flex flex-wrap gap-5 mt-1 mb-4 *:w-1/3">
                        <Card>
                            <template #title>

                                <div class="flex align-items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <Icon icon="bi:graph-up-arrow" width="16" height="16" />
                                        <h2 class="text-lg">Highest Stock</h2>
                                    </div>
                                    <Button class="gap-0" data-testid="productstocks_chart-quick_filter"   type="button"
                                        @click="store.QuickHighFilter()" aria-haspopup="true"
                                        aria-controls="quick_filter_menu_state" size="small" label="All"
                                        icon="pi pi-filter">
                                    </Button>

                                </div>
                            </template>

                            <template #content>
                                <div class="max-h-20rem overflow-y-auto mb-2">
                                    <div v-if="store.highest_stock && store.highest_stock.length">
                                        <div v-for="product in store.highest_stock" :key="product.id">
                                            <TileInfo :product="product" :baseUrl="base_url + '/'" :showVendor="true" />
                                        </div>
                                    </div>
                                    <div v-else class="text-center text-lg pb-5">
                                        No records found.
                                    </div>
                                </div>
                            </template>
                        </Card>
                        <Card>

                            <template #title>
                                <div class="flex align-items-center justify-content-between">
                                    <div class="flex items-center gap-2">
                                        <Icon icon="bi:graph-down-arrow" width="16" height="16" />
                                        <h2 class="text-lg">Lowest Stock</h2>
                                    </div>


                                    <Button  data-testid="productstocks_chart-quick_filter" type="button"
                                        @click="store.QuickLowFilter()" aria-haspopup="true"
                                        aria-controls="quick_filter_menu_state" class="gap-0 ml-1 p-button-sm px-1"
                                        label="All" icon="pi pi-filter">
                                    </Button>

                                </div>
                            </template>

                            <template #content>
                                <div class="max-h-20rem overflow-y-auto">
                                    <div v-if="store.lowest_stock && store.lowest_stock.length">
                                        <div v-for="product in store.lowest_stock" :key="product.id">
                                            <TileInfo :product="product" :baseUrl="base_url + '/'" :showVendor="true" />
                                        </div>
                                    </div>
                                    <div v-else class="text-center text-lg">
                                        No records found.
                                    </div>
                                </div>

                            </template>
                        </Card>
                    </div>
                    <template #icons>

                        <InputGroup>

                            <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                                data-testid="productstocks-list-create" size="small" @click="store.toForm()">
                                <i class="pi pi-plus mr-1"></i>
                                Create
                            </Button>

                            <Button data-testid="productstocks-list-reload" size="small" @click="store.toReload()">
                                <i class="pi pi-refresh mr-1"></i>
                            </Button>

                            <!--form_menu-->

                            <Button :disabled="!store.assets.permissions.includes('can-update-module')" v-if="root.assets && root.assets.module
                                && root.assets.module.is_dev" type="button" @click="toggleCreateMenu" size="small"
                                data-testid="productstocks-create-menu" icon="pi pi-angle-down" aria-haspopup="true" />

                            <Menu ref="create_menu" :disabled="!store.assets.permissions.includes('can-update-module')"
                                :model="store.list_create_menu" :popup="true" />

                            <!--/form_menu-->

                        </InputGroup>

                    </template>

                    <Actions />

                    <Table />

                </Panel>
            </div>

            <div v-if="store.getRightColumnClasses" :class="store.getRightColumnClasses">

                <RouterView />

            </div>

        </div>
    </div>


</template>

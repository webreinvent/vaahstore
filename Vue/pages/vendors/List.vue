<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useVendorStore} from '../../stores/store-vendors'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Charts from "../../components/Charts.vue";

const store = useVendorStore();
const root = useRootStore();
const route = useRoute();

import {useConfirm} from "primevue/useconfirm";

const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Vendors - Store';
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
    store.getQuickFilterMenu();
    await store.getListCreateMenu();
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


function toggleDatasetVisibility(index) {
    store.vendor_sales_area_chart_series[index].hidden = !store.vendor_sales_area_chart_series[index].hidden;

    store.vendor_sales_area_chart_series = [...store.vendor_sales_area_chart_series];
}


</script>
<template>

    <div class="w-full m-1" v-if="store.assets">

        <div class="lg:flex lg:space-x-4 items-start">
            <div v-if="store.getLeftColumnClasses"
                 :class="store.getLeftColumnClasses">

                <Panel class="is-small">

                    <template class="p-1" #header>

                        <div class="flex flex-row">
                            <div>
                                <b class="mr-1">Vendors</b>
                                <Badge v-if="store.list && store.list.total > 0"
                                       :value="store.list.total">
                                </Badge>
                            </div>

                        </div>

                    </template>
                        <div class="flex gap-2"  v-if=" store.isListView()">
                        <Card class="min-w-max border-round-xl shadow-md">
                            <template #title>
                                <div class="flex align-items-center justify-content-between">
                                    <h2 class="text-lg">Top Vendors By Sales</h2>
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
                                        @remove="store.loadSalesData()"
                                    />
                                    <Button
                                        data-testid="vendors_chart-quick_filter"
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
                                        :value="store.top_selling_vendors"
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

                                                    <div class="product_desc ml-3">
                                                        <h4>{{ prop.data.name }}</h4>
                                                    </div>
                                                </div>
                                            </template>
                                        </Column>
                                        <Column field="total_sales" header="" class="overflow-wrap-anywhere">
                                            <template #body="prop">
                                                <div class="flex ">

                                                    <div class="product_desc ml-3">
                                                        <p><b> {{ prop.data.total_sales }}</b> Items</p>
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
                        <div class="w-full bg-white border-round-2xl shadow-2">

                            <div class="flex " v-if=" store.isListView()">

                                <Charts
                                    type="line"

                                    :chartOptions="store.vendor_sales_area_chart_options"
                                    :chartSeries="store.vendor_sales_area_chart_series"
                                    height=250 width=700
                                    titleAlign="center"
                                    title="Sales By Vendor"

                                />
                                <div class="flex  mt-4 " v-if="store.isListView()">


                                    <div class="scrollable-legend flex-1 bg-gray-100 border-round-xl px-3 py-2 max-h-10rem overflow-y-auto h-full">
                                        <ul class="legend-items">

                                            <li
                                                v-for="(dataset, index) in store?.vendor_sales_area_chart_series"
                                                :key="index"
                                                class="legend-item flex align-items-center mb-2 gap-2"
                                                :style="{ textDecoration: dataset.hidden ? 'line-through' : 'none' }"
                                                style="cursor: pointer;"
                                                @click="toggleDatasetVisibility(index)"
                                            >

                                                <span class="legend-label text-blue-500 hover:text-gray-700">{{ dataset.name }}</span>
                                            </li>
                                        </ul>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                    <template #icons>

                        <div class="p-inputgroup">

                            <Button data-testid="vendors-list-create"
                                    class="p-button-sm"
                                    :disabled="!store.assets.permissions.includes('can-update-module')"
                                    @click="store.toForm()">
                                <i class="pi pi-plus mr-1"></i>
                                Create
                            </Button>

                            <Button data-testid="vendors-list-reload"
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
                                    data-testid="vendors-create-menu"
                                    icon="pi pi-angle-down"
                                    :disabled="!store.assets.permissions.includes('can-update-module')"
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

            <div v-if="store.getRightColumnClasses"
                 :class="store.getRightColumnClasses">

                <RouterView/>

            </div>
        </div>

    </div>


</template>

<style scoped>

.legend-items {
    list-style: none;
    padding: 0;
    margin: 0;
}

</style>

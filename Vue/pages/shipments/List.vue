<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useShipmentStore} from '../../stores/store-shipments'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue'
import Charts from "../../components/Charts.vue";
const store = useShipmentStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Shipments - Store';
    store.item = null;
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
    store.chartSeries[index].hidden = !store.chartSeries[index].hidden;

    store.chartSeries = [...store.chartSeries];
}

const handleDateChangeRound = (newDate, date_type) => {
    if (newDate && date_type) {
        store[date_type] = new Date(newDate.getTime() - newDate.getTimezoneOffset() * 60000);
    }
}
const today = ref(new Date());

</script>
<template>

    <div class="grid" v-if="store.assets">

        <div :class="'col-'+(store.show_filters?9:store.list_view_width)">



            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div >
                            <b class="mr-1">Shipments</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total">
                            </Badge>
                        </div>

                    </div>

                </template>
                <div class="flex gap-2 mb-1">
                    <div class="w-full bg-white   border-gray-200 rounded-sm mb-2">
                        <div class="flex justify-content-end" v-if=" store.isViewLarge()">


                            <div v-if="store.is_custom_range_open" class="flex gap-2">
                                <Calendar
                                    placeholder="Select Start Date"
                                    date-format="yy-mm-dd"
                                    @date-select="handleDateChangeRound($event,'filter_start_date')"
                                    :maxDate="today"
                                    v-model="store.filter_start_date"
                                    showIcon/>
                                <Calendar
                                    placeholder="Select End Date"
                                    date-format="yy-mm-dd"
                                    :maxDate="today"
                                    @date-select="handleDateChangeRound($event,'filter_end_date')"
                                    :minDate="store.filter_start_date"
                                    v-model="store.filter_end_date"
                                    showIcon/>
                                <Button
                                    @click="store.getChartData()"
                                    label="Go"
                                />
                            </div>
                            <Chip
                                v-if="store.quick_chart_filter"
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
                                :label="store.quick_chart_filter.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"
                                removable
                                @remove="store.removeChartFilter();"
                            />
                            <Button
                                data-testid="inventories-quick_filter"
                                type="button"
                                @click="toggleQuickFilterState($event)"
                                aria-haspopup="true"
                                aria-controls="quick_filter_menu_state"
                                class="ml-1 p-button-sm px-1"

                                icon="pi pi-filter"
                            >
                            </Button>
                            <Menu
                                ref="quick_filter_menu_state"
                                :model="store.quick_filter_menu"
                                :popup="true"
                            >

                            </Menu>

                        </div>
                        <div class="flex flex-wrap justify-content-between gap-3 align-items-start mt-3" v-if=" store.isViewLarge()">

                            <Charts
                                class="border-1 border-gray-200 border-round-sm overflow-hidden"
                                type="area"
                                :chartOptions="store.chartOptions"
                                :chartSeries="store.chartSeries"
                                height=200 width=350
                                titleAlign="center"
                            />
                            <Charts
                                class="border-1 border-gray-200 border-round-sm overflow-hidden"
                                type="area"
                                :chartOptions="store.shipmentItemsChartOptions"
                                :chartSeries="store.shipmentItemsSeries"
                                height=200 width=350
                                titleAlign="center"
                            />
                            <Charts
                                class="border-1 border-gray-200 border-round-sm overflow-hidden"
                                type="bar"
                                :chartOptions="store.shipment_items_by_status_chart_options"
                                :chartSeries="store.shipment_items_by_status_chart_series"
                                height=200 width=350
                                titleAlign="center"
                            />

                        </div>
                    </div>
                </div>
                <template #icons>

                    <div class="p-inputgroup">

                    <Button data-testid="shipments-list-create"
                            class="p-button-sm"
                            @click="store.toForm()">
                        <i class="pi pi-plus mr-1"></i>
                        Create
                    </Button>

                    <Button data-testid="shipments-list-reload"
                            class="p-button-sm"
                            @click="store.getList()">
                        <i class="pi pi-refresh mr-1"></i>
                    </Button>

                    <!--form_menu-->

                    <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                        type="button"
                        @click="toggleCreateMenu"
                        class="p-button-sm"
                        data-testid="shipments-create-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="create_menu"
                          :model="store.list_create_menu"
                          :popup="true" />

                    <!--/form_menu-->

                    </div>

                </template>

                <Actions/>

                <Table/>

            </Panel>
        </div>

         <Filters/>

        <RouterView/>

    </div>


</template>

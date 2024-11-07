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

    await store.getListCreateMenu();

});

//--------form_menu
const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
//--------/form_menu

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

            <div class="flex gap-2">
                <div class="w-full bg-white border-round-2xl shadow-2">
                    <div class="flex justify-content-between mt-1 ml-2" v-if=" store.isViewLarge()">
                        <div class="flex gap-2">
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
                                label="Get Report"
                            />
                        </div>

                    </div>
                    <div class="flex justify-content-between align-items-start" v-if=" store.isViewLarge()">

                        <Charts
                            type="area"
                            :chartOptions="store.chartOptions"
                            :chartSeries="store.chartSeries"
                            height=200 width=380
                            titleAlign="center"
                        />
                        <Charts
                            type="area"
                            :chartOptions="store.shipmentItemsChartOptions"
                            :chartSeries="store.shipmentItemsSeries"
                            height=200 width=380
                            titleAlign="center"
                        />

<!--                        <div class="flex  mt-4 " v-if="store.isViewLarge()">


                            <div class="scrollable-legend flex-1 bg-gray-100 border-round-xl px-3 py-2 max-h-10rem overflow-y-auto h-full">
                                <ul class="legend-items">

                                    <li
                                        v-for="(dataset, index) in store?.chartSeries"
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


                        </div>-->
                    </div>
                </div>
            </div>

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

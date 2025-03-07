<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useShipmentStore} from '../../stores/store-shipments'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './Filters.vue'
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


</script>
<template>

    <div class="w-full m-1" v-if="store.assets">

        <div class="lg:flex lg:space-x-4 items-start">
            <div v-if="store.getLeftColumnClasses"
                 :class="store.getLeftColumnClasses">


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

                            <div class="flex flex-wrap justify-content-between gap-3 align-items-start mt-3" v-if=" store.isListView()">

                                <Charts
                                    class="border-1 border-gray-200 border-round-sm overflow-hidden"

                                    :chartOptions="store.shipment_by_order_chart_options"
                                    :chartSeries="store.shipment_by_order_chart_series"
                                    height=300 width=350
                                    titleAlign="center"
                                />
                                <Charts
                                    class="border-1 border-gray-200 border-round-sm overflow-hidden"

                                    :chartOptions="store.shipment_by_items_chart_options"
                                    :chartSeries="store.shipment_by_items_chart_series"
                                    height=300 width=350
                                    titleAlign="center"
                                />
                                <Charts
                                    class="border-1 border-gray-200 border-round-sm overflow-hidden"
                                    type="bar"
                                    :chartOptions="{
                                        ...store.shipment_items_by_status_chart_options,
                                        legend: { show: false }
                                    }"
                                    :chartSeries="store.shipment_items_by_status_chart_series"
                                    height=300 width=300
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

            <div v-if="store.getRightColumnClasses"
                 :class="store.getRightColumnClasses">

                <RouterView/>

            </div>
        </div>

    </div>


</template>

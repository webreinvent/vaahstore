<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useWarehouseStore} from '../../stores/store-warehouses'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Charts from "../../components/Charts.vue";
const store = useWarehouseStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();

function handleScreenResize() {
    store.setScreenSize();
}
onMounted(async () => {
    document.title = 'Warehouses - Store';

    window.addEventListener('resize', handleScreenResize);
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

            <div v-if="store.getLeftColumnClasses"
                 :class="store.getLeftColumnClasses"

                 class="mb-4 lg:mb-0">



                <Panel :pt="root.panel_pt">
                    <template #header>

                        <div class="flex flex-row">
                            <div>
                                <b class="mr-1">Warehouses</b>
                                <Badge v-if="store.list && store.list.total > 0"
                                       :value="store.list.total">
                                </Badge>
                            </div>

                        </div>

                    </template>

                    <div class="flex gap-2 mb-1" v-if=" store.isListView()">
                        <div class="w-full bg-white p-3 border-1 border-gray-200 rounded-sm mb-2">

                            <div class="flex flex-wrap justify-content-center gap-3 align-items-start mt-3" v-if=" store.isListView()">


                                <Charts
                                    class="border-1 border-gray-200 border-round-sm overflow-hidden"
                                    type="bar"
                                    :chartOptions="store.warehouse_stock_bar_chart_options"
                                    :chartSeries="store.warehouse_stock_bar_chart_series"
                                    height=300 width=600
                                    titleAlign="center"
                                />
                            </div>
                        </div>
                    </div>

                <template #icons>

                    <div class="p-inputgroup">

                    <Button data-testid="warehouses-list-create"
                            class="p-button-sm"
                            @click="store.toForm()"
                            :disabled="store.assets.is_guest_impersonating">
                        <i class="pi pi-plus mr-1"></i>
                        Create
                    </Button>

                    <Button data-testid="warehouses-list-reload"
                            class="p-button-sm"
                            @click="store.reload()">
                        <i class="pi pi-refresh mr-1"></i>
                    </Button>

                    <!--form_menu-->

                    <Button v-if="root.assets
                                  && root.assets.module
                                  && root.assets.module.is_dev"
                        type="button"
                        @click="toggleCreateMenu"
                        class="p-button-sm"
                        data-testid="warehouses-create-menu"
                        icon="pi pi-angle-down"
                        :disabled="store.assets.is_guest_impersonating"
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
        <!--/right-->
    </div>
    </div>



</template>

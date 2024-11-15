<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useUserStore} from '../../stores/store-users'
import {useRootStore} from "../../stores/root";
import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Charts from "../../components/Charts.vue";
const store = useUserStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();


onMounted(async () => {
    /**
     * call onLoad action when List view loads
     */
     document.title = 'Customers - Store';
    await store.onLoad(route);
    store.fetchCustomerCountChartData();
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



const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};

const quick_filter_menu_state = ref();
const toggleQuickFilterState = (event) => {
    alert('as')
    quick_filter_menu_state.value.toggle(event);
};
</script>
<template>
    <div class="grid">
        <div :class="'col-'+store.list_view_width">
            <Panel class="is-small">
                <template class="p-1" #header>
                    <div class="flex flex-row">
                        <div >
                            <b class="mr-1">Customers</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total"
                            />
                        </div>
                    </div>
                </template>
                <div class="flex gap-2 mb-1">
                    <div class="w-full bg-white   border-gray-200 rounded-sm mb-2">

                        <div class="flex flex-wrap justify-content-center gap-3 align-items-start mt-3 " v-if=" store.isViewLarge()">


                                <div class="chart-container position-relative">

                                <Chip
                                    v-if="store.query.filter.time?.length"
                                    class="position-absolute top-0 right-0 mt-2 mr-10 white-space-nowrap align-items-center"
                                    :style="{
                                                        fontSize: '11px',
                                                        marginRight: '5px',
                                                        padding: '1px 8px',
                                                        fontWeight:'600',
                                                        zIndex: 20
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
                                    :label="store.query.filter.time?.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"
                                    removable
                                    @remove="store.query.filter.time=null;"
                                />
                                <Button
                                    data-testid="inventories-quick_filter"
                                    type="button"
                                    @click="toggleQuickFilterState($event)"
                                    aria-haspopup="true"
                                    aria-controls="quick_filter_menu_state"
                                    class="position-absolute top-0 right-0 mt-2 mr-2 p-button-sm px-1"
                                    icon="pi pi-filter"
                                    :style="{ zIndex: 10 }"
                                >
                                </Button>
                                <Menu ref="quick_filter_menu_state"
                                      :model="store.quick_filter_menu"
                                      :popup="true"/>
                                <Charts
                                    class="border-1 border-gray-200 border-round-sm overflow-hidden"
                                    type="line"
                                    :chartOptions="store.chartOptions"
                                    :chartSeries="store.chartSeries"
                                    height="250"
                                    width="520"
                                    titleAlign="center"
                                />
                            </div>

                        </div>
                    </div>
                </div>
                <template #icons>
                    <div class="p-inputgroup">
                        <Button class="p-button-sm"
                                label="Create"
                                @click="store.toForm()"
                                :disabled="!store.assets || !store.assets.permissions.includes('can-update-module')"
                                data-testid="users-create"
                                >
                            <i class="pi pi-plus mr-1"></i>
                            Create
                        </Button>

                        <Button class="p-button-sm"
                                :loading="store.is_btn_loading"
                                data-testid="users-list_refresh"
                                @click="store.sync()"
                        >
                            <i class="pi pi-refresh mr-1"></i>
                        </Button>

                        <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                                type="button"
                                @click="toggleCreateMenu"
                                class="p-button-sm"  data-testid="customers-create-menu"
                                icon="pi pi-angle-down"
                                aria-haspopup="true"/>

                        <Menu ref="create_menu"
                              :model="store.list_create_menu"
                              :popup="true" />
                    </div>
                </template>

                <Actions/>


                <Table/>
            </Panel>
        </div>

        <RouterView/>
    </div>
</template>
<style scoped>
.chart-container {
    position: relative;
    width: 520px; /* Match chart width */
}

.position-absolute {
    position: absolute;
}
.top-0 {
    top: 0;
}
.right-0 {
    right: 0;
}
.left-0 {
    left: 0;
}
.mt-2 {
    margin-top: 8px;
}
.mr-2 {
    margin-right: 8px;
}
.ml-2 {
    margin-left: 8px;
}
</style>

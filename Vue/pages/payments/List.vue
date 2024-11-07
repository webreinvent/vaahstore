<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {usePaymentStore} from '../../stores/store-payments'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue'
import Charts from "../../components/Charts.vue";
const store = usePaymentStore();
const order_store = useOrderStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
import {useOrderStore} from "../../stores/store-orders";
const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Payments - Store';
    store.item = null;
    /**
     * call onLoad action when List view loads
     */
    await store.onLoad(route);
    await order_store.fetchOrderPaymentsData();

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

            <div class="flex gap-2 mb-1">
                <div class="w-full bg-white p-3 border-1 border-gray-200 rounded-sm mb-2">
                    <div class="flex justify-content-between " v-if=" store.isViewLarge()">
                        <p><b>Payments Dashboard</b></p>


                    </div>
                    <div class="flex justify-content-center gap-8 align-items-start mt-3" v-if=" store.isViewLarge()">

                        <Charts
                            class="border-1 border-gray-200 border-round-sm overflow-hidden shadow-2 "
                            type="pie"
                            :chartOptions="store.chartOptions"
                            :chartSeries="store.chartSeries"
                            height=220 width=400
                            titleAlign="center"
                        />
                        <div class="flex " v-if="store.isViewLarge()">
                            <div class="w-26rem h-12rem">
                                <Card
                                    v-if="store.isViewLarge()"
                                    class="border-1 border-gray-200 border-round-sm overflow-hidden min-h-full" style="margin-right:20px;"
                                    :pt="{
                                        content: {
                                            class: 'py-0'
                                        }
                                      }">
                                    <template #content>
                                        <div>
                                            <div class="flex gap-3 align-items-center">
                                                <div class="min-w-max relative">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="52"
                                                         viewBox="0 0 48 52" fill="none">
                                                        <path
                                                            d="M19.1094 2.12943C22.2034 0.343099 26.0154 0.343099 29.1094 2.12943L42.4921 9.85592C45.5861 11.6423 47.4921 14.9435 47.4921 18.5162V33.9692C47.4921 37.5418 45.5861 40.8431 42.4921 42.6294L29.1094 50.3559C26.0154 52.1423 22.2034 52.1423 19.1094 50.3559L5.72669 42.6294C2.63268 40.8431 0.726688 37.5418 0.726688 33.9692V18.5162C0.726688 14.9435 2.63268 11.6423 5.72669 9.85592L19.1094 2.12943Z"
                                                            fill="#22C55E"></path>
                                                    </svg>
                                                    <i class="pi pi-indian-rupee absolute top-50 left-50 text-white font-semibold text-xl" style="transform: translate(-50%, -50%)">₹</i>


                                                </div>


                                                <div class="min-w-max">
                                        <span
                                            :style="{color: 'gray', flexWrap: 'nowrap', fontSize: order_store.show_filters ? '10px' : '13px' }">
                                                           Payment Received

                                                        </span>
                                                    <p :style="{fontSize: store.show_filters ? '14px' : '18px' }">
                                                        <b>₹{{
                                                            order_store.overall_paid > 0 ?
                                                            order_store.overall_income?.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') :
                                                            '0'
                                                            }}</b>
                                                    </p>
                                                </div>
                                                <div class="min-w-max"
                                                     v-if="order_store.list && order_store.list.data && order_store.list.data?.length">
                                                    <template
                                                        v-if="order_store.order_payments_chart_series?.income_growth_rate !== 0">
                                                        <i :style="{ color: order_store.order_payments_chart_series?.income_growth_rate <= 0 ? 'red' : '#5acc81',fontSize: order_store.show_filters ? '8px' : '10px',marginRight:'3px', }"
                                                           :class="order_store.order_payments_chart_series?.income_growth_rate <= 0 ? 'pi pi-arrow-down' : 'pi pi-arrow-up'"/>
                                                        <i v-if="order_store.order_payments_chart_series?.income_growth_rate > 0"
                                                           class="pi pi-plus"
                                                           style="font-size:6px;color:#5acc81;margin-right:2px;"/>
                                                        <span
                                                            :style="{ fontWeight: '400',color: order_store.order_payments_chart_series?.income_growth_rate <= 0 ? 'red' : '#5acc81', fontSize: order_store.show_filters ? '10px' : '12px' }">
                                                                                                    {{ order_store.order_payments_chart_series?.income_growth_rate.toLocaleString('en-US') }}%
                                                                                                </span>
                                                    </template>
                                                    <template v-else>
                                                        {{ 0 }}
                                                    </template>
                                                </div>

                                            </div>
                                            <div class="align-self-end">
                                                <Charts
                                                    class="w-full"
                                                    type="area"
                                                    :chartOptions="order_store.orderPaymentsIncomeChartOptions"
                                                    :chartSeries="order_store.orderPaymentsIncomeChartSeries"
                                                    height=100 width=300
                                                    titleAlign="center"


                                                />
                                            </div>
                                        </div>


                                    </template>
                                </Card>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div >
                            <b class="mr-1">Payments</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total">
                            </Badge>
                        </div>

                    </div>

                </template>

                <template #icons>

                    <div class="p-inputgroup">

                    <Button data-testid="payments-list-create"
                            class="p-button-sm"
                            @click="store.toForm()">
                        <i class="pi pi-plus mr-1"></i>
                        Create
                    </Button>

                    <Button data-testid="payments-list-reload"
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
                        data-testid="payments-create-menu"
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

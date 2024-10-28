<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useOrderStore} from '../../stores/store-orders'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Charts from "../../components/Charts.vue";

const store = useOrderStore();
const root = useRootStore();
const route = useRoute();

import {useConfirm} from "primevue/useconfirm";

const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Orders - Store';
    /**
     * call onLoad action when List view loads
     */
    await store.onLoad(route);

    await store.fetchOrdersChartData();
    await store.fetchSalesChartData();

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


            <div class="flex w-full mt-2 mb-4">
                <div class="w-18rem h-12rem">
                    <Card
                        v-if="store.isViewLarge()"
                        class="flex-grow-1 shadow-1 h-full border-round-xl" style="margin-right:20px;"
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
                                        <i class="pi pi-shopping-bag absolute top-50 left-50 text-white font-semibold text-xl" style="transform: translate(-50%, -50%)"/>


                                    </div>


                                    <div class="min-w-max">
                                        <span
                                            :style="{color: 'gray', flexWrap: 'nowrap', fontSize: store.show_filters ? '10px' : '13px' }">
                                                            Overall Sales

                                                        </span>
                                        <p :style="{fontSize: store.show_filters ? '14px' : '18px' }">
                                            <b>{{
                                                store.overall_sales > 0 ?
                                                store.overall_sales?.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') :
                                                '0'
                                                }}</b>
                                        </p>
                                    </div>
                                    <div class="min-w-max"
                                        v-if="store.list && store.list.data && store.list.data?.length">
                                        <template v-if="store.chart_series?.growth_rate !== 0">
                                            <i :style="{ color: store.chart_series?.growth_rate <= 0 ? 'red' : '#5acc81',fontSize: store.show_filters ? '8px' : '10px',marginRight:'3px', }"
                                               :class="store.chart_series.growth_rate <= 0 ? 'pi pi-arrow-down' : 'pi pi-arrow-up'"/>
                                            <i v-if="store.chart_series.growth_rate > 0" class="pi pi-plus"
                                               style="font-size:6px;color:#5acc81;margin-right:2px;"/>
                                            <span
                                                :style="{ fontWeight: '400',color: store.chart_series.growth_rate <= 0 ? 'red' : '#5acc81', fontSize: store.show_filters ? '10px' : '12px' }">
                                                                                                    {{ store.chart_series.growth_rate.toLocaleString('en-US') }}%
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
                                        :chartOptions="store.salesChartOptions"
                                        :chartSeries="store.salesChartSeries"
                                        height=100 width=200
                                        titleAlign="center"


                                    />
                                </div>
                            </div>


                        </template>
                    </Card>
                </div>
                <div class="w-18rem h-12rem">
                    <Card
                        v-if="store.isViewLarge()"
                        class="flex-grow-1 shadow-1 h-full border-round-xl" style="margin-right:20px;"
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
                                        <i class="pi pi-shopping-bag absolute top-50 left-50 text-white font-semibold text-xl" style="transform: translate(-50%, -50%)"/>


                                    </div>


                                    <div class="min-w-max">
                                        <span
                                            :style="{color: 'gray', flexWrap: 'nowrap', fontSize: store.show_filters ? '10px' : '13px' }">
                                                           Orders Paid

                                                        </span>
                                        <p :style="{fontSize: store.show_filters ? '14px' : '18px' }">
                                            <b>{{
                                                store.overall_paid > 0 ?
                                                store.overall_paid?.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') :
                                                '0'
                                                }}</b>
                                        </p>
                                    </div>
                                    <div class="min-w-max"
                                         v-if="store.list && store.list.data && store.list.data?.length">
                                        <template
                                            v-if="store.order_payments_chart_series?.order_payments_growth_rate !== 0">
                                            <i :style="{ color: store.order_payments_chart_series?.order_payments_growth_rate <= 0 ? 'red' : '#5acc81',fontSize: store.show_filters ? '8px' : '10px',marginRight:'3px', }"
                                               :class="store.order_payments_chart_series?.order_payments_growth_rate <= 0 ? 'pi pi-arrow-down' : 'pi pi-arrow-up'"/>
                                            <i v-if="store.order_payments_chart_series?.order_payments_growth_rate > 0"
                                               class="pi pi-plus"
                                               style="font-size:6px;color:#5acc81;margin-right:2px;"/>
                                            <span
                                                :style="{ fontWeight: '400',color: store.order_payments_chart_series?.order_payments_growth_rate <= 0 ? 'red' : '#5acc81', fontSize: store.show_filters ? '10px' : '12px' }">
                                                                                                    {{ store.order_payments_chart_series?.order_payments_growth_rate.toLocaleString('en-US') }}%
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
                                        :chartOptions="store.orderPaymentsChartOptions"
                                        :chartSeries="store.orderPaymentsChartSeries"
                                        height=100 width=200
                                        titleAlign="center"


                                    />
                                </div>
                            </div>


                        </template>
                    </Card>
                </div>
                <div class="w-18rem h-12rem">

                </div>
            </div>


            <div class="flex justify-content-center ">


                <Charts
                    type="donut"
                    :chartOptions="store.pieChartOptions"
                    :chartSeries="store.pieChartSeries"
                    height=250 width=400
                    titleAlign="center"

                />
                <!--                <Charts
                                    type="pie"
                                    :chartOptions="store.pieChartOptions"
                                    :chartSeries="store.pieChartSeries"
                                    height=350 width=400
                                    titleAlign="center"

                                />-->
                <Charts
                    type="area"
                    :chartOptions="store.chartOptions"
                    :chartSeries="store.chartSeries"
                    height=250 width=400
                    titleAlign="center"
                    title="Orders Count Over Months"

                />
                <Charts
                    type="area"
                    :chartOptions="store.salesChartOptions"
                    :chartSeries="store.salesChartSeries"
                    height=250 width=400
                    titleAlign="center"


                />


            </div>
            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div>
                            <b class="mr-1">Orders</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total">
                            </Badge>
                        </div>

                    </div>

                </template>

                <template #icons>

                    <div class="p-inputgroup">


                        <Button data-testid="orders-list-reload"
                                class="p-button-sm"
                                @click="store.getList()">
                            <i class="pi pi-refresh mr-1"></i>
                        </Button>


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

        <RouterView/>

    </div>


</template>

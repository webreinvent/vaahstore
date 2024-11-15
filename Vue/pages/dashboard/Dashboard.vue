<script setup>
import {useProductStore} from "../../stores/store-products";
import {useRootStore} from "../../stores/root";
import {useRoute} from "vue-router";
import {onMounted, ref} from "vue";
import {useOrderStore} from "../../stores/store-orders";

const customers_store = useUserStore();
import Charts from "../../components/Charts.vue";
import {useUserStore} from "../../stores/store-users";
import {useVendorStore} from "../../stores/store-vendors";
import {useShipmentStore} from "../../stores/store-shipments";
import {useWarehouseStore} from "../../stores/store-warehouses";
import {usePaymentStore} from "../../stores/store-payments";
import {useProductStockStore} from "../../stores/store-productstocks";

const orders_store = useOrderStore();
const product_store = useProductStore();
const vendor_store = useVendorStore();
const shipment_store = useShipmentStore();
const warehouse_store = useWarehouseStore();
const payment_store = usePaymentStore();
const product_stock_store = useProductStockStore();
const root = useRootStore();
const route = useRoute();
onMounted(async () => {
    document.title = 'VaahStore-Dashboard';
    await orders_store.watchStates();
    await product_store.watchStates();

    await product_store.topSellingProducts();
    await product_store.topSellingBrands();
    await product_store.topSellingCategories();
    await vendor_store.topSellingVendorsData();
    await orders_store.fetchOrdersChartData();
    await orders_store.fetchSalesChartData();
    await product_stock_store.getStocksChartData();
    customers_store.fetchCustomerCountChartData();

    product_store.getQuickFilterMenu();

    await orders_store.fetchOrdersCountChartData();
    await orders_store.fetchSalesChartData();
    await orders_store.fetchOrderPaymentsData();
    await orders_store.fetchOrdersChartData();

    await vendor_store.vendorSalesByRange();

    await shipment_store.ordersShipmentItemsByDateRange();
    await shipment_store.shipmentItemsByStatusBarChart();
    await warehouse_store.warehouseStockInBarChart();
    await payment_store.paymentMethodsPieChartData();
});

const quick_filter_menu_state = ref();
const toggleQuickFilterState = (event) => {
    quick_filter_menu_state.value.toggle(event);
};
</script>
<template>

    <div>
        <div class="grid">
            <div class="col-4">
                <Card

                    class="flex-grow-1 shadow-1 h-full border-round-xl"
                    :pt="{
                                        content: {
                                            class: 'py-0'
                                        },
                                        body: 'p-3 pb-0'
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
                                    <i class="pi pi-shopping-bag absolute top-50 left-50 text-white font-semibold text-xl"
                                       style="transform: translate(-50%, -65%)"/>


                                </div>


                                <div class="min-w-max text-base">
                                    <b>Orders Created vs Completed</b>

                                </div>


                            </div>
                            <div>
                                <Charts
                                    type="area"
                                    :chartOptions="orders_store.chartOptions"
                                    :chartSeries="orders_store.chartSeries"
                                    height=100
                                    titleAlign="center"
                                    title=""

                                />
                            </div>
                        </div>


                    </template>
                </Card>
            </div>

            <div class="col-4">
                <Card

                    class=" flex-grow-1 shadow-1 h-full border-round-xl"
                    :pt="{
                                        content: {
                                            class: 'py-0',
                                        },
                                        body: 'p-3 pb-0'
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
                                    <i class="pi pi-shopping-bag absolute top-50 left-50 text-white font-semibold text-xl"
                                       style="transform: translate(-50%, -50%)"/>


                                </div>


                                <div class="">
                                    <b>Overall Sale</b>
                                    <p :style="{fontSize: orders_store.show_filters ? '14px' : '18px' }">
                                        <b>₹{{
                                            orders_store.overall_sales > 0 ?
                                            orders_store.overall_sales?.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                                            :
                                            '0'
                                            }}</b>
                                    </p>
                                </div>
                                <div class="min-w-max"
                                     v-if="orders_store.chart_series?.growth_rate">
                                    <template v-if="orders_store.chart_series?.growth_rate !== 0">
                                        <i :style="{ color: orders_store.chart_series?.growth_rate <= 0 ? 'red' : '#5acc81',fontSize: orders_store.show_filters ? '8px' : '10px',marginRight:'3px', }"
                                           :class="orders_store.chart_series.growth_rate <= 0 ? 'pi pi-arrow-down' : 'pi pi-arrow-up'"/>
                                        <i v-if="orders_store.chart_series.growth_rate > 0" class="pi pi-plus"
                                           style="font-size:6px;color:#5acc81;margin-right:2px;"/>
                                        <span
                                            :style="{ fontWeight: '400',color: orders_store.chart_series.growth_rate <= 0 ? 'red' : '#5acc81', fontSize: orders_store.show_filters ? '10px' : '12px' }">
                                                                                                  {{ orders_store.chart_series.growth_rate.toLocaleString('en-US') }}%
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
                                    :chartOptions="orders_store.salesChartOptions"
                                    :chartSeries="orders_store.salesChartSeries"
                                    height=100
                                    titleAlign="center"


                                />
                            </div>
                        </div>


                    </template>
                </Card>

            </div>

            <div class="col-4">
                <Card
                    v-if="orders_store.isViewLarge()"
                    class="flex-grow-1 shadow-1 h-full border-round-xl"
                    :pt="{
                                        content: {
                                            class: 'py-0',
                                        },
                                        body: 'p-3 pb-0'
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
                                    <i class="pi pi-indian-rupee absolute top-50 left-50 text-white font-semibold text-xl"
                                       style="transform: translate(-50%, -50%)">₹</i>


                                </div>


                                <div class="">
                                    <b>Payment Received </b>
                                    <p :style="{fontSize: orders_store.show_filters ? '14px' : '18px' }">
                                        <b>₹{{
                                            orders_store.overall_income && !isNaN(orders_store.overall_income) ?
                                            orders_store.overall_income.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                                            :
                                            '0'
                                            }}</b>
                                    </p>
                                </div>
                                <div class="min-w-max"
                                >

                                    <template
                                        v-if="orders_store.order_payments_chart_series?.income_growth_rate !== 0">
                                        <i :style="{ color: orders_store.order_payments_chart_series?.income_growth_rate <= 0 ? 'red' : '#5acc81',fontSize: orders_store.show_filters ? '8px' : '10px',marginRight:'3px', }"
                                           :class="orders_store.order_payments_chart_series?.income_growth_rate <= 0 ? 'pi pi-arrow-down' : 'pi pi-arrow-up'"/>
                                        <i v-if="orders_store.order_payments_chart_series?.income_growth_rate > 0"
                                           class="pi pi-plus"
                                           style="font-size:6px;color:#5acc81;margin-right:2px;"/>
                                        <span
                                            :style="{ fontWeight: '400',color: orders_store.order_payments_chart_series?.income_growth_rate <= 0 ? 'red' : '#5acc81', fontSize: orders_store.show_filters ? '10px' : '12px' }">
                                                                                                    {{ orders_store.order_payments_chart_series?.income_growth_rate.toLocaleString('en-US') }}%
                                                                                                </span>
                                    </template>
                                    <template v-else>
                                        {{ }}
                                    </template>
                                </div>

                            </div>
                            <div class="align-self-end">
                                <Charts
                                    class="w-full"
                                    type="area"
                                    :chartOptions="orders_store.orderPaymentsIncomeChartOptions"
                                    :chartSeries="orders_store.orderPaymentsIncomeChartSeries"
                                    height=100
                                    titleAlign="center"


                                />
                            </div>
                        </div>


                    </template>
                </Card>
            </div>

        </div>
        <div class="grid mt-2">
            <!--          Top Products-->
            <div class="col-4">
                <Card class="border-round-xl shadow-md h-full">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Top Selling Products</h2>
                            <Chip
                                v-if="product_store.query.filter.time?.length"
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
                                :label="product_store.query.filter.time?.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"
                                removable
                                @remove="product_store.query.filter.time=null;"
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
                            <Menu ref="quick_filter_menu_state"
                                  :model="product_store.quick_filter_menu"
                                  :popup="true"/>
                        </div>
                    </template>

                    <template #content>
                        <DataTable
                            :value="product_store.top_selling_variations"
                            dataKey="id"

                            class="p-datatable-sm p-datatable-hoverable-rows"
                            :nullSortOrder="-1"
                            v-model:selection="product_store.action.items"
                            stripedRows
                            responsiveLayout="scroll"
                        >
                            <Column field="variation_name" header="" class="overflow-wrap-anywhere">
                                <template #body="prop">
                                    <div class="flex ">
                                        <div class="product_img">
                                            <div
                                                v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">
                                                <div v-for="(imageUrl, imgIndex) in prop.data.image_urls"
                                                     :key="imgIndex">
                                                    <Image preview
                                                           :src="'http://localhost/shivam-g001/store-dev/public/' + imageUrl"
                                                           alt="Error" class="shadow-4" width="35"/>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <img
                                                    src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                    alt="Error" class="shadow-4" width="35"/>
                                            </div>
                                        </div>
                                        <div class="product_desc ml-3">
                                            <h4>{{ prop.data.name }}</h4>
                                            <p><b> {{ prop.data.total_sales }}</b> Sold</p>
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
                    </template>
                </Card>
            </div>
            <!--          Vendors By Sale-->
            <div class="col-4">
                <Card class="min-w-max border-round-xl shadow-md h-full">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Top Vendors By Sale</h2>
                            <Chip
                                v-if="vendor_store.query.filter.time?.length"
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
                                :label="vendor_store.query.filter.time?.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"
                                removable
                                @remove="vendor_store.query.filter.time=null;"
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
                            <Menu ref="quick_filter_menu_state"
                                  :model="vendor_store.quick_filter_menu"
                                  :popup="true"/>
                        </div>
                    </template>

                    <template #content>
                        <DataTable
                            :value="vendor_store.top_selling_vendors"
                            dataKey="id"

                            class="p-datatable-sm p-datatable-hoverable-rows"
                            :nullSortOrder="-1"
                            v-model:selection="vendor_store.action.items"
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
                                            <p><b> {{ prop.data.total_sales }}</b> Sold</p>
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
                    </template>
                </Card>
            </div>
            <!--          Top Brands-->
            <div class="col-4">
                <Card class="min-w-max border-round-xl shadow-md h-full">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Top Brands</h2>

                        </div>
                    </template>

                    <template #content>
                        <DataTable
                            :value="product_store.top_selling_brands"
                            dataKey="id"

                            class="p-datatable-sm p-datatable-hoverable-rows"
                            :nullSortOrder="-1"
                            v-model:selection="product_store.action.items"
                            stripedRows
                            responsiveLayout="scroll"
                        >
                            <Column field="variation_name" header="" class="overflow-wrap-anywhere">
                                <template #body="prop">
                                    <div class="flex ">
                                        <div class="product_img">
                                            <div
                                                v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">
                                                <div v-for="(imageUrl, imgIndex) in prop.data.image_urls"
                                                     :key="imgIndex">
                                                    <Image preview
                                                           :src="'http://localhost/shivam-g001/store-dev/public/' + imageUrl"
                                                           alt="Error" class="shadow-4" width="35"/>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <img
                                                    src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                    alt="Error" class="shadow-4" width="35"/>
                                            </div>
                                        </div>
                                        <div class="product_desc ml-3">
                                            <h4>{{ prop.data.name }}</h4>
                                            <!--                                <p><b> {{ prop.data.total_sales }}</b> Items</p>-->
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
                    </template>
                </Card>
            </div>
            <div class="col-3">
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden mb-2 " :pt="{content: {
                                            class: 'py-0',
                                        },
                                        body: 'py-2'
                                        }">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Highest Stock</h2>

                            <Button
                                data-testid="inventories-quick_filter"
                                type="button"
                                @click="product_stock_store.QuickHighFilter()"
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


                            <DataTable :value="product_stock_store.highest_stock"
                                       dataKey="id"

                                       class="p-datatable-sm p-datatable-hoverable-rows"
                                       :nullSortOrder="-1"
                                       v-model:selection="product_stock_store.action.items"
                                       stripedRows
                                       responsiveLayout="scroll">
                                <Column field="" header="" class="overflow-wrap-anywhere"
                                        style="width:120px;">
                                    <template #body="prop">
                                        <div class="flex mt-4">
                                            <div class="product_img">
                                                <div
                                                    v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">
                                                    <div v-for="(imageUrl, imgIndex) in prop.data.image_urls"
                                                         :key="imgIndex">
                                                        <Image preview
                                                               :src="'http://localhost/shivam-g001/store-dev/public/' + imageUrl"
                                                               alt="Error" class="shadow-4" width="64"/>
                                                    </div>
                                                </div>
                                                <div v-else>
                                                    <img
                                                        src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                        alt="Error" class="shadow-4" width="64"/>
                                                </div>
                                            </div>
                                            <div class=" ml-3">
                                                <h3>
                                                    {{
                                                    prop.data.product && prop.data.productVariation
                                                    ? prop.data.product.name + '-' + prop.data.productVariation.name
                                                    : prop.data.name
                                                    }}
                                                </h3>
                                                <p class="mb-1" v-if="prop.data.stock"><b>Stock Qty:</b>

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

                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden" :pt="{content: {
                                            class: 'py-0',
                                        },
                                        body: 'py-2'
                                        }">

                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Lowest Stock</h2>

                            <Button
                                data-testid="inventories-quick_filter"
                                type="button"
                                @click="product_stock_store.QuickLowFilter()"
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
                        <div class="max-h-10rem overflow-y-auto">

                            <DataTable :value="product_stock_store.lowest_stock"
                                       dataKey="id"

                                       class="p-datatable-sm p-datatable-hoverable-rows"
                                       :nullSortOrder="-1"
                                       v-model:selection="product_stock_store.action.items"
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
                                                <h3>
                                                    {{
                                                    prop.data.product && prop.data.productVariation
                                                    ? prop.data.product.name + '-' + prop.data.productVariation.name
                                                    : prop.data.name
                                                    }}
                                                </h3>
                                                <p class="mb-1" v-if="prop.data.stock"><b>Stock Qty:</b>

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
            <div class="col-3">
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Top Categories</h2>

                        </div>
                    </template>

                    <template #content>
                        <DataTable
                            :value="product_store.top_selling_categories"
                            dataKey="id"

                            class="p-datatable-sm p-datatable-hoverable-rows"
                            :nullSortOrder="-1"
                            v-model:selection="product_store.action.items"
                            stripedRows
                            responsiveLayout="scroll"
                        >
                            <Column field="variation_name" header="" class="overflow-wrap-anywhere">
                                <template #body="prop">
                                    <div class="flex ">
                                        <div class="product_img">
                                            <div
                                                v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">
                                                <div v-for="(imageUrl, imgIndex) in prop.data.image_urls"
                                                     :key="imgIndex">
                                                    <Image preview
                                                           :src="'http://localhost/shivam-g001/store-dev/public/' + imageUrl"
                                                           alt="Error" class="shadow-4" width="35"/>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <img
                                                    src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                    alt="Error" class="shadow-4" width="35"/>
                                            </div>
                                        </div>
                                        <div class="product_desc ml-3">
                                            <h4>{{ prop.data.name }}</h4>
                                            <!--                                <p><b> {{ prop.data.total_sales }}</b> Items</p>-->
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
                    </template>
                </Card>
            </div>
            <div class="col-6">
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full w-full "
                      :pt="{content: {
                                            class: 'py-0',
                                        },
                                        body: 'p-3 pb-0'
                                        }">
                    <template #content>
                        <Charts
                            type="line"
                            :chartOptions="vendor_store.chartOptions"
                            :chartSeries="vendor_store.chartSeries"
                            titleAlign=""
                            title="Sales By Vendor"

                        />
                    </template>
                </Card>
            </div>
            <div class="col-6">
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden">
                    <template #content>
                        <Charts
                            type="donut"
                            :chartOptions="orders_store.pieChartOptions"
                            :chartSeries="orders_store.pieChartSeries"
                            height=250
                            titleAlign="center"

                        />
                    </template>

                </Card>
            </div>

            <div class="col-6">
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #content>
                        <Charts
                            type="pie"
                            :chartOptions="payment_store.chartOptions"
                            :chartSeries="payment_store.chartSeries"
                            height=250
                            titleAlign="center"
                        />
                    </template>
                </Card>
            </div>

            <div class="col-6">
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #content>
                        <Charts
                            type="bar"
                            :chartOptions="shipment_store.shipment_items_by_status_chart_options"
                            :chartSeries="shipment_store.shipment_items_by_status_chart_series"

                            titleAlign="center"
                        />
                    </template>

                </Card>
            </div>

            <div class="col-6">
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #content>
                        <Charts
                            type="bar"
                            :chartOptions="{
                ...warehouse_store.warehouse_stock_bar_chart_options,
                 chart: {
                                background: '#ffffff',
                            },
                }"
                            :chartSeries="warehouse_store.warehouse_stock_bar_chart_series"

                        />
                    </template>

                </Card>
            </div>



            <div class="col-7">
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #content>
                        <Charts
                            type="line"
                            :chartOptions="customers_store.chartOptions"
                            :chartSeries="customers_store.chartSeries"
                            height="300"
                            titleAlign="center"

                        />
                    </template>

                </Card>
            </div>

            <div class="col-5">
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #content>
                        <Charts
                            type="area"
                            :chartOptions="shipment_store.shipmentItemsChartOptions"
                            :chartSeries="shipment_store.shipmentItemsSeries"

                            titleAlign="center"
                        />
                    </template>

                </Card>

            </div>

            <!--Customer Charts-->


            <!--Quantity Shipped Over Date range-->



            <!--Shipped Quantity Status-->



            <!--Stocks Availabe in Warehouse-->




            <!--Highest & Lowest Stocks Cards-->

            <!--          Top Categories-->


            <!--          Vendors Sales Line Chart-->


            <!--Order Status Pie/Donut Chart-->


            <!--Payment Method Used-->


            <!--            <Charts-->
            <!--                class="bg-white h-full border-round-xl p-card p-3"-->
            <!--                type="area"-->
            <!--                :chartOptions="{-->
            <!--    ...orders_store.chartOptions,-->
            <!--    title: {-->
            <!--      text: 'Orders Created Vs Completed',-->
            <!--      align: 'center',-->
            <!--      offsetY: 10,-->
            <!--      style: {-->
            <!--        fontSize: '16px',-->
            <!--        fontWeight: 'bold',-->
            <!--        color: '#263238'-->
            <!--      }-->
            <!--    },-->
            <!--    legend: {-->
            <!--      position: 'top',-->
            <!--      horizontalAlign: 'center',-->
            <!--      fontSize: '14px'-->
            <!--    },-->
            <!--     grid: {-->
            <!--                    show: true,-->
            <!--                },-->
            <!--    xaxis: {-->
            <!--                    type: 'datetime',-->
            <!--                    // Set x-axis to datetime-->
            <!--                    labels: {-->
            <!--                        show: true, // Hide x-axis labels-->
            <!--                    },-->
            <!--                    axisBorder: {-->
            <!--                        show: true, // Hide x-axis border if desired-->
            <!--                    },-->
            <!--                },-->
            <!--                yaxis: {-->
            <!--                    labels: {-->
            <!--                        show: true, // Hide y-axis labels-->
            <!--                    },-->
            <!--                    axisBorder: {-->
            <!--                        show: true, // Hide y-axis border if desired-->
            <!--                    },-->
            <!--                },-->
            <!--                legend: {-->
            <!--                    position: 'top',-->
            <!--                    horizontalAlign: 'center',-->
            <!--                    floating: false,-->
            <!--                    fontSize: '14px',-->
            <!--                    formatter: function (val, opts) {-->
            <!--                        const seriesIndex = opts.seriesIndex; // Get the series index-->
            <!--                        const seriesData = opts.w.globals.series[seriesIndex]; // Get the series data-->
            <!--                        const sum = seriesData.reduce((acc, value) => acc + value, 0); // Calculate the sum of the series data-->
            <!--                        return `${val} - ${sum}`; // Return the legend text with the sum-->
            <!--                    },-->
            <!--                },-->
            <!--    toolbar: {-->
            <!--      show: false // Example of an additional option-->
            <!--    }-->
            <!--  }"-->
            <!--                :chartSeries="orders_store.chartSeries"-->
            <!--                height="320"-->
            <!--                width="390"-->
            <!--            />-->




        </div>


    </div>

</template>



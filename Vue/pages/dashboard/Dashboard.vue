<script setup>
import {useProductStore} from "../../stores/store-products";
import {useRootStore} from "../../stores/root";
import {useRoute} from "vue-router";
import {computed, onMounted, ref} from "vue";
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
const base_url = ref('');
const formattedDateRange = computed(() => {
    const startDate = root.filter_start_date;
    const endDate = root.filter_end_date;

    if (!startDate || !endDate) return "Date range not selected";

    const formatOptions = { year: "numeric", month: "short", day: "2-digit" };
    const formatter = new Intl.DateTimeFormat("en-US", formatOptions);

    return `${formatter.format(new Date(startDate))} - ${formatter.format(new Date(endDate))}`;
});
onMounted(async () => {
    document.title = 'VaahStore-Dashboard';
    base_url.value = root.ajax_url.replace('backend/store', '/');
    await orders_store.watchStates();


    await orders_store.fetchOrdersChartData();
    await orders_store.fetchSalesChartData();
    await product_stock_store.getStocksChartData();
    await product_store.topSellingProducts();
    await product_store.topSellingBrands();
    await product_store.topSellingCategories();
    await customers_store.fetchCustomerCountChartData();

    const formatCurrency = (value) => {
        if (value == null) return 'Loading...';

        if (value >= 1000) {
            return `&#8377;${(value / 1000).toFixed(2)}k`;
        }

        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    };

    const formatLargeNumber = (value) => {
        if (value == null) return 'Loading...';
        return value >= 1000 ? `${(value / 1000).toFixed(2)}k` : value;
    };

    metrics.value = [
        {
            label: `<i class="pi pi-users text-2xl mr-2"></i><b>Active Customers</b>`,
            value: customers_store.total_customers
                ? formatLargeNumber(customers_store.total_customers)
                : 'Loading...'
        },
        {
            label: `<i class="pi pi-briefcase text-2xl mr-2"></i><b>Average Order Value</b>`,
            value: customers_store.average_order_value
                ? formatCurrency(customers_store.average_order_value)
                : 'Loading...'
        },
        {
            label: `<i class="pi pi-shopping-bag text-2xl mr-2"></i><b>Total Orders</b>`,
            value: customers_store.total_orders
                ? formatLargeNumber(customers_store.total_orders)
                : 'Loading...'
        },
        {
            label: `<b>Avg Orders per Customer</b>`,
            value: customers_store.avg_orders_per_customer
                ? formatLargeNumber(customers_store.avg_orders_per_customer)
                : 'Loading...'
        }
    ];

    await vendor_store.topSellingVendorsData();


    await orders_store.fetchOrdersCountChartData();
    await orders_store.fetchSalesChartData();
    await orders_store.fetchOrderPaymentsData();
    await orders_store.fetchOrdersChartData();

    await vendor_store.vendorSalesByRange();

    await shipment_store.ordersShipmentByDateRange();
    await shipment_store.shipmentItemsByStatusBarChart();
    await warehouse_store.warehouseStockInBarChart();
    await payment_store.paymentMethodsPieChartData();
});

const quick_filter_menu_state = ref();
const toggleQuickFilterState = (event) => {
    quick_filter_menu_state.value.toggle(event);
};
const metrics = ref([]);

</script>
<template>
    <div  class="flex-grow-1  border-round-xl has-background-white mb-2 p-3 surface-ground ">
    <h4 class="text-lg">
        Selected Date Range:{{ formattedDateRange }}
    </h4>
        <h6 class="text-sm">Note : For Change the Date Range Navigate to Store>Settings</h6>
    </div>
    <div class="container mb-3 mt-1">

        <div class="!grid grid-cols-4 gap-4">
            <div  v-for="metric in metrics" :key="metric.label">
                <Card class="surface-ground h-full">
                    <template #content>
                        <div class="text-500 mb-2 text-sm"><p v-html="metric.label"></p></div>
                        <div class="text-900 text-xl font-semibold"><p v-html="metric.value"></p></div>
                    </template>
                </Card>
            </div>
        </div>
    </div>
    <div class="!mt-3">
        <div class="!grid grid-cols-3 !gap-4">
            <div>
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

                                    :chartOptions="orders_store.count_chart_options"
                                    :chartSeries="orders_store.count_chart_series"
                                    height=300
                                    titleAlign="center"
                                    title=""

                                />
                            </div>
                        </div>


                    </template>
                </Card>
            </div>

            <div>
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
                                    :chartOptions="orders_store.sales_chart_options"
                                    :chartSeries="orders_store.sales_chart_series"
                                    height=300
                                    titleAlign="center"


                                />
                            </div>
                        </div>


                    </template>
                </Card>

            </div>

            <div>
                <Card
                    v-if="orders_store.isListView()"
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
                                    :chartOptions="orders_store.order_payments_income_chart_options"
                                    :chartSeries="orders_store.order_payments_income_chart_series"
                                    height=300
                                    titleAlign="center"


                                />
                            </div>
                        </div>


                    </template>
                </Card>
            </div>

        </div>
        <div class="!grid grid-cols-3 mt-2 !gap-4">
            <!--          Top Products-->


<!--asas-->

            <div>
                <Card class="border-round-xl shadow-md h-full">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Top Selling Products</h2>

                        </div>
                    </template>

                    <template #content>
                        <DataTable
                            :value="product_store.top_selling_products"
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
                                                           :src="base_url + '/' + imageUrl"
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
            <div>
                <Card class="min-w-max border-round-xl shadow-md h-full">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Top Vendors By Sale</h2>

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
            <div>
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
                                                           :src="base_url + '/' + imageUrl"
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




            <div>
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden mb-2 " :pt="{content: {
                                            class: 'py-0',
                                        },
                                        body: 'py-2'
                                        }">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Highest Stock</h2>



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
                                                               :src="base_url + '/' + imageUrl"
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
                                                    ?  prop.data.productVariation.name
                                                    : prop.data.name
                                                    }}
                                                </h3>
                                                <p class="mb-1" v-if="prop.data.stock"><b>Stock Qty:</b>

                                                    {{ prop.data.stock }}

                                                </p>
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
                                                               :src="base_url + '/' + imageUrl"
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
                                                    ?  prop.data.productVariation.name
                                                    : prop.data.name
                                                    }}
                                                </h3>
                                                <p class="mb-1" v-if="prop.data.stock"><b>Stock Qty:</b>

                                                    {{ prop.data.stock }}

                                                </p>
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




            <div>
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
                                                           :src="base_url + '/' + imageUrl"
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
            <div>
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full w-full "
                      :pt="{content: {
                                            class: 'py-0',
                                        },
                                        body: 'p-3 pb-0'
                                        }">
                    <template #content>
                        <Charts
                            type="line"
                            :chartOptions="vendor_store.vendor_sales_area_chart_options"
                            :chartSeries="vendor_store.vendor_sales_area_chart_series"
                            titleAlign=""
                            title="Sales By Vendor"

                        />
                    </template>
                </Card>
            </div>
            <div>
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden">
                    <template #content>
                        <Charts
                            type="donut"
                            :chartOptions="orders_store.pie_chart_options"
                            :chartSeries="orders_store.pie_chart_series"
                            height=250
                            titleAlign="center"

                        />
                    </template>

                </Card>
            </div>

            <div>
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #content>
                        <Charts
                            type="donut"
                            :chartOptions="payment_store.payment_methods_chart_options"
                            :chartSeries="payment_store.payment_methods_chart_series"
                            height=250
                            titleAlign="center"
                        />
                    </template>
                </Card>
            </div>

            <div>
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #content>
                        <Charts
                            type="bar"
                            :chartOptions="shipment_store.shipment_items_by_status_chart_options"
                            :chartSeries="shipment_store.shipment_items_by_status_chart_series"


                        />
                    </template>

                </Card>
            </div>

            <div>
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



            <div>
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #content>
                        <Charts

                            :chartOptions="customers_store.customer_count_chart_options"
                            :chartSeries="customers_store.customer_count_chart_series"
                            height="300"
                            titleAlign="center"

                        />
                    </template>

                </Card>
            </div>

            <div>
                <Card class="border-1 border-gray-200 border-round-xl overflow-hidden h-full">
                    <template #content>
                        <Charts

                            :chartOptions="shipment_store.shipment_by_order_chart_options"
                            :chartSeries="shipment_store.shipment_by_order_chart_series"
                            title=""
                            height="300"

                        />

                    </template>

                </Card>

            </div>



        </div>


    </div>

</template>

<style scoped>
.container {


    margin: 0 auto;
}

.p-card {
    background-color: var(--surface-ground);
    box-shadow: none !important;
}

.p-card .p-card-content {
    padding: 1.5rem;
}
</style>

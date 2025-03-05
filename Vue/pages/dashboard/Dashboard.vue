<script setup>
import { useProductStore } from "../../stores/store-products";
import { useRootStore } from "../../stores/root";
import { useRoute } from "vue-router";
import { computed, onMounted, ref } from "vue";
import { useOrderStore } from "../../stores/store-orders";

const customers_store = useUserStore();
import TileInfo from "../../components/TileInfo.vue";
import Charts from "../../components/Charts.vue";
import {useUserStore} from "../../stores/store-users";
import {useVendorStore} from "../../stores/store-vendors";
import {useShipmentStore} from "../../stores/store-shipments";
import {useWarehouseStore} from "../../stores/store-warehouses";
import {usePaymentStore} from "../../stores/store-payments";
import {useProductStockStore} from "../../stores/store-productstocks";
import {useSettingStore} from "../../stores/store-settings";
import VendorSale from "../../components/VendorSale.vue";
const orders_store = useOrderStore();
const settings_store = useSettingStore();
const product_store = useProductStore();
const vendor_store = useVendorStore();
const shipment_store = useShipmentStore();
const warehouse_store = useWarehouseStore();
const payment_store = usePaymentStore();
const product_stock_store = useProductStockStore();
const root = useRootStore();
const route = useRoute();
const base_url = ref('');

onMounted(async () => {
    document.title = 'VaahStore-Dashboard';
    base_url.value = root.ajax_url.replace('backend/store', '/');
    await orders_store.watchStates();
    await settings_store.getAssets();
    await settings_store.getList();


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




const handleDateChangeRound = (newDate, date_type) => {
    if (newDate && date_type) {
        store[date_type] = new Date(newDate.getTime() - newDate.getTimezoneOffset() * 60000);
    }
}
const today = ref(new Date());
</script>
<template>
    <div  class="flex-grow-1  border-round-xl has-background-white mb-2 p-3 surface-ground ">
    <div class="flex justify-content-between">
        <div>
            <h4 class="text-sm mb-2">
                Filter By:
            </h4>
            <InputGroup>
                <!--            {{store.chart_date_filter}}-->
                <SelectButton v-model="settings_store.chart_date_filter"
                              optionLabel="name"
                              optionValue="value"
                              :options="settings_store.chart_by_date_filter"
                              size="small"
                              @update:modelValue="settings_store.storeChartFilterSettings"
                              data-testid="general-charts_filters"
                              aria-labelledby="single"
                />



            </InputGroup>
            <div v-if="settings_store.chart_date_filter === 'custom'" class="flex gap-2 mt-3 mb-2">
                <DatePicker
                    class="rounded-lg"
                    size="small"
                    placeholder="Select Start Date"
                    date-format="yy-mm-dd"
                    @date-select="handleDateChangeRound($event,'filter_start_date')"
                    :maxDate="today"
                    :disabled="settings_store.chart_date_filter !== 'custom'"
                    v-model="settings_store.filter_start_date"
                    showIcon/>
                <DatePicker
                    class="rounded-lg"
                    placeholder="Select End Date"
                    date-format="yy-mm-dd"
                    :maxDate="today"
                    size="small"
                    @date-select="handleDateChangeRound($event,'filter_end_date')"

                    :disabled="settings_store.chart_date_filter !== 'custom'"
                    v-model="settings_store.filter_end_date"
                    showIcon/>
                <Button label="Submit"
                        class=""
                        size="small"
                        @click="settings_store.storeChartFilterSettings()"
                        :disabled="settings_store.is_button_disabled"/>

            </div>
        </div>

        <div class="mt-2">
            <FloatLabel :variant="product_store.float_label_variants"
            >
                <AutoComplete
                    name="products-filter-store"
                    data-testid="products-filter-store"
                    v-model="product_store.selected_store_at_list"
                    @change="product_store.onStoreSelect($event)"
                    option-label = "name"
                    dropdown
                    style="height:30px"
                    :complete-on-focus = "true"
                    :suggestions="product_store.filteredStores"
                    @complete="product_store.searchStoreForListQuery"



                />
                <label for="articles-name">Select Store</label>
            </FloatLabel>
        </div>
    </div>

    </div>
    <div class=" mb-3 mt-1">

        <div v-if="metrics.length > 0" class="!grid grid-cols-4 gap-4">
            <div v-for="metric in metrics" :key="metric.label">
                <Card class="surface-ground h-full p-4">
                    <template #content>
                        <div class="text-500 mb-2 text-sm">
                            <p v-html="metric.label"></p>
                        </div>
                        <div class="text-900 text-xl font-semibold">
                            <p v-html="metric.value"></p>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
        <div v-else class="!grid grid-cols-4 gap-4">
        <Skeleton class="mb-2" height="8rem" borderRadius="14px"></Skeleton>
        <Skeleton class="mb-2" height="8rem" borderRadius="14px"></Skeleton>
        <Skeleton class="mb-2" height="8rem" borderRadius="14px"></Skeleton>
        <Skeleton class="mb-2" height="8rem" borderRadius="14px"></Skeleton>
        </div>
    </div>
    <div class="!mt-3">
        <div class="!grid grid-cols-3 !gap-5 mb-3">
            <Card>
                <template #title>
                    <div class="flex gap-1 align-items-center">
                        <Icon icon="solar:box-linear" width="20" height="20"></Icon>
                        <span class="text-sm"> Orders Created vs Completed</span>
                    </div>
                </template>
                <template #content>
                    <Charts :chartOptions="orders_store.count_chart_options"
                        :chartSeries="orders_store.count_chart_series" height=300 titleAlign="center" title="" />
                </template>
            </Card>

            <Card>
                <template #title>
                    <div class="flex gap-1 items-center">
                        <Icon icon="solar:box-linear" width="20" height="20"></Icon>
                        <span class="text-sm"> Overall Sales</span>
                        <span class="rounded-full bg-gray-200 size-1 mx-1"></span>
                        <span class="text-xs">
                            ₹{{
                                orders_store.overall_sales > 0 ?
                                    orders_store.overall_sales?.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                                    :
                                    '0'
                            }}
                        </span>

                        <template v-if="orders_store.chart_series?.growth_rate">
                            <template v-if="orders_store.chart_series?.growth_rate !== 0">
                                <Icon v-if="orders_store.chart_series?.growth_rate"
                                    :icon="orders_store.chart_series?.growth_rate <= 0 ? 'mdi-light:arrow-down' : 'mdi-light:arrow-up'"
                                    :class="orders_store.chart_series?.growth_rate <= 0 ? 'text-danger-500' : 'text-success-500'"
                                    width="20" height="20"></Icon>

                                <span
                                    :style="{ fontWeight: '400', color: orders_store.chart_series.growth_rate <= 0 ? 'red' : '#5acc81', fontSize: orders_store.show_filters ? '10px' : '12px' }">
                                    {{ orders_store.chart_series.growth_rate.toLocaleString('en-US') }}%
                                </span>
                            </template>
                            <template v-else>
                                {{ }}
                            </template>
                        </template>



                    </div>
                </template>
                <template #content>
                    <Charts class="w-full" type="area" :chartOptions="orders_store.sales_chart_options"
                        :chartSeries="orders_store.sales_chart_series" height=300 titleAlign="center" />
                </template>


            </Card>

            <Card>
                <template #title>
                    <div class="flex gap-1 items-center">
                        <Icon icon="solar:box-linear" width="20" height="20"></Icon>
                        <span class="text-sm"> Payment Recieved</span>
                        <span class="rounded-full bg-gray-200 size-1 mx-1"></span>
                        <span class="text-xs">
                            ₹{{
                                orders_store.overall_income && !isNaN(orders_store.overall_income) ?
                                    orders_store.overall_income.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                                    :
                                    '0'
                            }}
                        </span>

                        <template v-if="orders_store.order_payments_chart_series?.income_growth_rate !== 0">
                            <Icon v-if="orders_store.order_payments_chart_series?.income_growth_rate"
                                :icon="orders_store.order_payments_chart_series?.income_growth_rate <= 0 ? 'mdi-light:arrow-down' : 'mdi-light:arrow-up'"
                                :class="orders_store.order_payments_chart_series?.income_growth_rate <= 0 ? 'text-danger-500' : 'text-success-500'"
                                width="20" height="20"></Icon>

                            <span
                                :style="{ fontWeight: '400', color: orders_store.order_payments_chart_series?.income_growth_rate <= 0 ? 'red' : '#5acc81', fontSize: orders_store.show_filters ? '10px' : '12px' }">
                                {{
                                    orders_store.order_payments_chart_series?.income_growth_rate.toLocaleString('en-US')
                                }}%
                            </span>
                        </template>
                        <template v-else>
                            {{ }}
                        </template>

                    </div>
                </template>
                <template #content>
                    <Charts class="w-full" type="area" :chartOptions="orders_store.order_payments_income_chart_options"
                        :chartSeries="orders_store.order_payments_income_chart_series" height=300 titleAlign="center" />
                </template>
            </Card>

        </div>
        <div class="!grid grid-cols-3 mt-2 !gap-3 text-sm">
            <!--          Top Products-->

            <Card>
                <template #title>
                    <div class="flex align-items-center gap-1">
                        <Icon icon="solar:bag-2-outline" width="20" height="20"></Icon>

                        <h2 class="text-sm">Top Selling Products</h2 class="text-sm">

                    </div>
                </template>

                <template #content>
                    <div v-for="product in product_store.top_selling_products" :key="product.id">
                        <TileInfo :product="product" :baseUrl="base_url" :showRating="true" />
                    </div>

                    <!-- Show empty message if no products -->
                    <div v-if="!product_store.top_selling_products || product_store.top_selling_products.length === 0"
                        class="text-center py-3">
                        No records found.
                    </div>
                </template>
            </Card>
            <div class="flex flex-col gap-3">
                <!--          Vendors By Sale-->
                <Card class="min-w-max border-round-xl  h-full">
                    <template #title>
                        <div class="flex align-items-center gap-1">
                            <Icon icon="uil:chat-bubble-user" width="20" height="20"></Icon>
                            <h2 class="text-sm">Top Vendors By Sale</h2 class="text-sm">

                        </div>
                    </template>

                    <template #content>
                        <div class="!grid grid-cols-3 gap-x-2 gap-y-8 pb-12">
                            <VendorSale :vendorData="vendor_store.top_selling_vendors" />
                        </div>

                        <!-- <DataTable :value="vendor_store.top_selling_vendors" dataKey="id"
                            class="p-datatable-sm p-datatable-hoverable-rows" :nullSortOrder="-1"
                            v-model:selection="vendor_store.action.items" stripedRows responsiveLayout="scroll">
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
            </DataTable> -->
                    </template>
                </Card>

                <!--          Top Brands-->
                <Card class="min-w-max border-round-xl h-full">
                    <template #title>
                        <div class="flex align-items-center gap-1">
                            <Icon icon="mage:tag" width="20" height="20"></Icon>
                            <h2 class="text-sm">Top Brands</h2 class="text-sm">

                        </div>
                    </template>

                    <template #content>

                        <div v-for="product in product_store.top_selling_brands" :key="product.id">

                            <TileInfo :product="product" :baseUrl="product.image_urls" :showRating="true" />
                        </div>

                    </template>
                </Card>
            </div>

            <div class="flex flex-col gap-3">
                <Card class="h-full">
                    <template #title>
                        <div class="flex align-items-center gap-1">
                            <Icon icon="pajamas:sort-lowest" width="20" height="20"></Icon>
                            <h2 class="text-sm">Highest Stock</h2 class="text-sm">
                        </div>
                    </template>

                    <template #content>
                        <div class="max-h-14rem overflow-y-auto">
                            <div
                                v-if="product_stock_store.highest_stock && product_stock_store.highest_stock.length > 0">
                                <div v-for="product in product_stock_store.highest_stock" :key="product.id">
                                    <TileInfo :product="product" :baseUrl="base_url + '/'" :showVendor="true" />
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-else class="text-center py-3">
                                No records found.
                            </div>


                        </div>
                    </template>
                </Card>

                <Card class="h-full">
                    <template #title>
                        <div class="flex align-items-center gap-1">
                            <Icon icon="lineicons:sort-high-to-low" width="20" height="20"></Icon>
                            <h2 class="text-sm">Lowest Stock</h2 class="text-sm">
                        </div>
                    </template>

                    <template #content>
                        <div class="max-h-10rem overflow-y-auto">
                            <div v-if="product_stock_store.lowest_stock && product_stock_store.lowest_stock.length > 0">
                                <div v-for="product in product_stock_store.lowest_stock" :key="product.id">
                                    <TileInfo :product="product" :baseUrl="base_url + '/'" :showVendor="true" />
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>







            <Card>
                <template #title>
                    <div class="flex align-items-center gap-1">
                        <Icon icon="hugeicons:menu-circle" height="20" width="20"></Icon>
                        <h2 class="text-sm">Top Categories</h2 class="text-sm">
                    </div>
                </template>

                <template #content>
                    <div class="!grid grid-cols-3 gap-x-2 gap-y-8 pb-10">
                        <VendorSale :vendorData="product_store.top_selling_categories" />
                    </div>

                </template>
            </Card>

            <!-- Vendor Sales over selected date range -->
            <Card :pt="{ body: { class: 'pt-0' } }" class="col-span-2">
                <template #content>
                    <Charts type="line" :chartOptions="vendor_store.vendor_sales_area_chart_options"
                        :chartSeries="vendor_store.vendor_sales_area_chart_series" titleAlign="" height="400"
                        title="Sales By Vendor" />
                </template>
            </Card>



        </div>
        <div class="!grid grid-cols-2 mt-4 gap-3">


            <Card :pt="{ body: { class: 'pt-0' } }">
                <template #content>
                    <Charts type="donut" :chartOptions="orders_store.pie_chart_options"
                        :chartSeries="orders_store.pie_chart_series" height="350" titleAlign="center" />
                </template>

            </Card>

            <Card :pt="{ body: { class: 'pt-0' } }">
                <template #content>
                    <Charts type="donut" :chartOptions="payment_store.payment_methods_chart_options"
                        :chartSeries="payment_store.payment_methods_chart_series" height="350" titleAlign="center" />
                </template>
            </Card>

            <Card :pt="{ body: { class: 'pt-0' } }">
                <template #content>
                    <Charts type="bar" :chartOptions="shipment_store.shipment_items_by_status_chart_options"
                        :chartSeries="shipment_store.shipment_items_by_status_chart_series" height="350" />
                </template>

            </Card>

            <Card :pt="{ body: { class: 'pt-0' } }">
                <template #content>
                    <Charts type="bar" :chartOptions="{
                        ...warehouse_store.warehouse_stock_bar_chart_options,


                    }" :chartSeries="warehouse_store.warehouse_stock_bar_chart_series" height="350" />
                </template>

            </Card>



            <Card :pt="{ body: { class: 'pt-0' } }">
                <template #content>
                    <Charts :chartOptions="customers_store.customer_count_chart_options"
                        :chartSeries="customers_store.customer_count_chart_series" height="300" titleAlign="center" />
                </template>

            </Card>

            <Card :pt="{ body: { class: 'pt-0' } }">
                <template #content>
                    <Charts :chartOptions="shipment_store.shipment_by_order_chart_options"
                        :chartSeries="shipment_store.shipment_by_order_chart_series" title="" height="300" />

                </template>

            </Card>



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

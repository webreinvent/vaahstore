<script setup>
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";

import { usePaymentStore } from "../../stores/store-payments";
import { useOrderStore } from "../../stores/store-orders";
import { useRootStore } from "../../stores/root";

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Charts from "../../components/Charts.vue";

const store = useOrderStore();
const payment_store = usePaymentStore();
const root = useRootStore();
const route = useRoute();

onMounted(async () => {
  document.title = "Orders - Store";
  /**
   * call onLoad action when List view loads
   */
  await store.onLoad(route);

  await store.fetchOrdersChartData();
  await store.fetchSalesChartData();
  await payment_store.paymentMethodsPieChartData();

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
  <div class="w-full bg-gray-50 m-1" v-if="store.assets">
    <div class="lg:flex lg:space-x-4 items-start">
      <div
        v-if="store.getLeftColumnClasses"
        :class="store.getLeftColumnClasses"
      >
        <Panel>
          <template class="!bg-gray-50" #header>
            <div class="flex items-center gap-2">
                <Icon
                        icon="solar:box-linear"
                        width="24"
                        height="24"
                        class="text-gray-950"
                      ></Icon>
                <p class="text-gray-950 font-normal text-base leading-5">Orders</p>
            </div>

          </template>

          <!-- <div class="h-[1px] bg-gray-100 w-full mb-2"></div> -->

          <div class="flex bg-gray-50 gap-2 mb-1">
            <div class="w-full rounded-sm mb-2">
              <div
                class="!grid grid-cols-3 !gap-5 mb-3"
                v-if="store.isListView()"
              >
                <Card>
                  <template #title>
                    <div class="flex gap-1 align-items-center">
                      <Icon
                        icon="solar:box-linear"
                        width="20"
                        height="20"
                      ></Icon>
                      <span class="text-sm"> Orders Created vs Completed</span>
                    </div>
                  </template>
                  <template #content>
                    <Charts
                      :chartOptions="store.count_chart_options"
                      :chartSeries="store.count_chart_series"
                      height="300"
                      titleAlign="center"
                      title=""
                    />
                  </template>
                </Card>

                <Card>
                  <template #title>
                    <div class="flex gap-1 items-center">
                      <Icon
                        icon="solar:box-linear"
                        width="20"
                        height="20"
                      ></Icon>
                      <span class="text-sm"> Overall Sales</span>
                      <span class="rounded-full bg-gray-200 size-1 mx-1"></span>
                      <span class="text-xs">
                        ₹{{
                          store.overall_sales > 0
                            ? store.overall_sales
                                ?.toString()
                                .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                            : "0"
                        }}
                      </span>

                      <template v-if="store.chart_series?.growth_rate">
                        <template v-if="store.chart_series?.growth_rate !== 0">
                          <Icon
                            v-if="store.chart_series?.growth_rate"
                            :icon="
                              store.chart_series?.growth_rate <= 0
                                ? 'mdi-light:arrow-down'
                                : 'mdi-light:arrow-up'
                            "
                            :class="
                              store.chart_series?.growth_rate <= 0
                                ? 'text-danger-500'
                                : 'text-success-500'
                            "
                            width="20"
                            height="20"
                          ></Icon>

                          <span
                            :style="{
                              fontWeight: '400',
                              color:
                                store.chart_series.growth_rate <= 0
                                  ? 'red'
                                  : '#5acc81',
                              fontSize: store.show_filters ? '10px' : '12px',
                            }"
                          >
                            {{
                              store.chart_series.growth_rate.toLocaleString(
                                "en-US"
                              )
                            }}%
                          </span>
                        </template>
                        <template v-else>
                          {{}}
                        </template>
                      </template>
                    </div>
                  </template>
                  <template #content>
                    <Charts
                      class="w-full"
                      type="area"
                      :chartOptions="store.sales_chart_options"
                      :chartSeries="store.sales_chart_series"
                      height="300"
                      titleAlign="center"
                    />
                  </template>
                </Card>

                <Card>
                  <template #title>
                    <div class="flex gap-1 items-center">
                      <Icon
                        icon="solar:box-linear"
                        width="20"
                        height="20"
                      ></Icon>
                      <span class="text-sm"> Payment Recieved</span>
                      <span class="rounded-full bg-gray-200 size-1 mx-1"></span>
                      <span class="text-xs">
                        ₹{{
                          store.overall_income && !isNaN(store.overall_income)
                            ? store.overall_income
                                .toString()
                                .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                            : "0"
                        }}
                      </span>

                      <template
                        v-if="
                          store.order_payments_chart_series
                            ?.income_growth_rate !== 0
                        "
                      >
                        <Icon
                          v-if="
                            store.order_payments_chart_series
                              ?.income_growth_rate
                          "
                          :icon="
                            store.order_payments_chart_series
                              ?.income_growth_rate <= 0
                              ? 'mdi-light:arrow-down'
                              : 'mdi-light:arrow-up'
                          "
                          :class="
                            store.order_payments_chart_series
                              ?.income_growth_rate <= 0
                              ? 'text-danger-500'
                              : 'text-success-500'
                          "
                          width="20"
                          height="20"
                        ></Icon>

                        <span
                          :style="{
                            fontWeight: '400',
                            color:
                              store.order_payments_chart_series
                                ?.income_growth_rate <= 0
                                ? 'red'
                                : '#5acc81',
                            fontSize: store.show_filters ? '10px' : '12px',
                          }"
                        >
                          {{
                            store.order_payments_chart_series?.income_growth_rate.toLocaleString(
                              "en-US"
                            )
                          }}%
                        </span>
                      </template>
                      <template v-else>
                        {{}}
                      </template>
                    </div>
                  </template>
                  <template #content>
                    <Charts
                      class="w-full"
                      type="area"
                      :chartOptions="store.order_payments_income_chart_options"
                      :chartSeries="store.order_payments_income_chart_series"
                      height="300"
                      titleAlign="center"
                    />
                  </template>
                </Card>
              </div>

              <div
                class="!grid grid-cols-2 mt-4 gap-3"
                v-if="store.isListView()"
              >
                <Card :pt="{ body: { class: 'pt-0' } }">
                  <template #content>
                    <Charts
                      type="donut"
                      :chartOptions="store.pie_chart_options"
                      :chartSeries="store.pie_chart_series"
                      height="350"
                      titleAlign="center"
                    />
                  </template>
                </Card>

                <Card :pt="{ body: { class: 'pt-0' } }">
                  <template #content>
                    <Charts
                      type="donut"
                      :chartOptions="
                        payment_store.payment_methods_chart_options
                      "
                      :chartSeries="payment_store.payment_methods_chart_series"
                      height="350"
                      titleAlign="center"
                    />
                  </template>
                </Card>
              </div>
            </div>
          </div>
          <template #icons>
            <div class="p-inputgroup flex gap-1 items-center">
              <Button
                data-testid="orders-list-reload"
                class="p-button-sm !rounded-lg"
                @click="store.getList()"
              >
              <Icon icon="material-symbols:refresh-rounded" width="20" height="20" class="text-gray-500" />
                          </Button>

              <Button
                v-if="
                  root.assets && root.assets.module && root.assets.module.is_dev
                "
                type="button"
                @click="toggleCreateMenu"
                class="p-button-sm !rounded-lg"
                data-testid="productstocks-create-menu"
                aria-haspopup="true"
              >
              <Icon icon="uil:angle-down" width="20" height="20" class="text-gray-500" />
            </Button>

              <Menu
                ref="create_menu"
                :model="store.list_create_menu"
                :popup="true"
              />

              <!--/form_menu-->
            </div>
          </template>

          <Actions />

          <Table />
        </Panel>
      </div>

      <div
        v-if="store.getRightColumnClasses"
        :class="store.getRightColumnClasses"
      >
        <RouterView />
      </div>
    </div>
  </div>
</template>

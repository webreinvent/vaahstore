<script  setup>

import { useShipmentStore } from '../../../stores/store-shipments'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'

const store = useShipmentStore();

</script>

<template>
    <div class="col-3" v-if="store.show_filters">

            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div >
                            <b class="mr-1">Filters</b>
                        </div>

                    </div>

                </template>

                <template #icons>

                    <div class="p-inputgroup">

                        <Button data-testid="shipments-hide-filter"
                                class="p-button-sm"
                                @click="store.show_filters = false">
                            <i class="pi pi-times"></i>
                        </Button>

                    </div>

                </template>

                <VhFieldVertical >
                    <template #label>
                        <b>Orders By:</b>
                    </template>
                    <VhField label="Prder">
                        <AutoComplete name="shipments-filters-orders"
                                      data-testid="shipments-filters-orders"
                                      v-model="store.selected_orders"
                                      @change = "store.addOrdersFilter()"
                                      option-label = "user.user_name"
                                      multiple
                                      :complete-on-focus = "true"
                                      :suggestions="store.filter_order_suggestion"
                                      @complete="store.getorders($event)"
                                      placeholder="Search orders"
                                      class="w-full "
                                      append-to="self"
                                      :pt="{
                                      token: {
                                        class: 'max-w-full'
                                      },
                                      removeTokenIcon: {
                                          class: 'min-w-max'
                                      },
                                      item: { style: {
                                                    textWrap: 'wrap'
                                                }  },
                                       panel: { class: 'w-16rem ' }
                                  }"/>
                    </VhField>


                </VhFieldVertical>
                <VhFieldVertical >
                    <template #label>
                        <b>Created Between:</b>
                    </template>

                    <Calendar v-model="store.selected_dates"
                              selectionMode="range"
                              @date-select="store.setDateRange"
                              :manualInput="false"
                              class="w-full"
                              append-to="self"
                              data-testid="shipments-filters-create_date_range"
                              placeholder="Select date range"
                    />


                </VhFieldVertical >

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="shipments-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="shipments-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="shipments-filters-sort-descending"
                                 value="updated_at:desc"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-descending" class="cursor-pointer">Updated (Descending)</label>
                </div>

            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Is Active:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="active-all"
                                 inputId="active-all"
                                 value="null"
                                 data-testid="shipments-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="shipments-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="shipments-filters-active-false"
                                 value="false"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-false" class="cursor-pointer">Only Inactive</label>
                </div>

            </VhFieldVertical>

             <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Trashed:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="trashed-exclude"
                                 inputId="trashed-exclude"
                                 data-testid="shipments-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="shipments-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="shipments-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Panel>

    </div>
</template>

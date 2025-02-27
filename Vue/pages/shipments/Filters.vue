<script  setup>

import { useShipmentStore } from '../../stores/store-shipments'
import VhFieldVertical from '../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import VhField from '../../vaahvue/vue-three/primeflex/VhField.vue'

const store = useShipmentStore();

</script>

<template>
    <div>

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

                        <Button data-testid="payments-hide-filter"
                                @click="store.toList()"
                                icon="pi pi-times"
                                rounded
                                variant="text"
                                severity="contrast"
                                size="small">
                        </Button>

                    </div>

                </template>

                <VhFieldVertical >
                    <template #label>
                        <b>Orders By:</b>
                    </template>
                    <VhField label="Orders">
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
                                      :dropdown="true"
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

                    <DatePicker v-model="store.selected_dates"
                              selectionMode="range"
                              @date-select="store.setDateRange"
                              :manualInput="false"
                              showIcon iconDisplay="input"
                              class="w-full"
                              data-testid="shipments-filters-create_date_range"
                              placeholder="Select date range"
                    />


                </VhFieldVertical >

                <VhFieldVertical >
                    <template #label>
                        <b>Status By:</b>
                    </template>
                    <VhField label="Status">
                        <MultiSelect
                            v-model="store.query.filter.status"
                            :options="store.status_option"
                            filter
                            optionValue="name"
                            optionLabel="name"
                            data-testid="shipments-filter-status"
                            placeholder="Select status"
                            display="chip"
                            append-to="self"
                            class="w-full relative" />
                    </VhField>


                </VhFieldVertical>

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

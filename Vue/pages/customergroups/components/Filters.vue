<script  setup>

import { useCustomerGroupStore } from '../../../stores/store-customergroups'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'

const store = useCustomerGroupStore();

</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right">

            <VhFieldVertical>

                <template #label>
                    <b>Customers By:</b>
                </template>

                <AutoComplete
                    name="customergroups-customer-filter"
                    data-testid="customergroups-customer-filter"
                    v-model="store.filter_selected_customers"
                    @change="store.setFilterSelectedCustomers()"
                    option-label = "display_name"
                    multiple
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
                                  }"
                    :complete-on-focus = "true"
                    :suggestions="store.customer_suggestions_list"
                    @complete="store.getCustomers($event)"
                    placeholder="Select Customers"
                    append-to="self"
                    class="w-full">
                </AutoComplete>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Select Created Date:</b>
                </template>

                <Calendar v-model="store.selected_dates"
                          selectionMode="range"
                          data-testid="customergroups-created_range-filter"
                          @date-select="store.setDateRange"
                          :manualInput="false"
                          class="w-full"
                          append-to="self"
                          data-testid="customergroups-filters-created_date"
                          placeholder="Choose Date Range"

                />


            </VhFieldVertical >

            <VhFieldVertical >
                <template #label>
                    <b>Status By:</b>
                </template>
                <VhField label="Status">
                    <MultiSelect
                        v-model="store.query.filter.status"
                        :options="store.status"
                        filter
                        optionValue="slug"
                        optionLabel="name"
                        data-testid="customergroups-filters-status"
                        placeholder="Select Status"
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
                                 data-testid="customergroups-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="customergroups-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="customergroups-filters-sort-descending"
                                 value="updated_at:desc"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-descending" class="cursor-pointer">Updated (Descending)</label>
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
                                 data-testid="customergroups-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="customergroups-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="customergroups-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Sidebar>

    </div>
</template>

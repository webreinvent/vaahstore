<script  setup>

import { useUserStore } from '../../../stores/store-users'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'

const store = useUserStore();

</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right"
                 style="z-index: 1101"
        >


            <VhFieldVertical>
                <template #label>
                    <b>Customer Groups By:</b>
                </template>

                <VhField label="Customer Group">

                    <AutoComplete name="users-customergroup-filter"
                                  data-testid="users-customergroup-filter"
                                  v-model="store.selected_customer_group"
                                  @change = "store.addCustomerGroup()"
                                  option-label = "name"
                                  option-value = "name"
                                  multiple
                                  :complete-on-focus = "true"
                                  append-to="self"
                                  :pt="{
                                              token: {class: 'max-w-full'},
                                              removeTokenIcon: {class: 'min-w-max'},
                                              item: { style: {
                                              textWrap: 'wrap'
                                               }  },
                                              panel: { class: 'w-16rem ' }
                                  }"
                                  :suggestions="store.customer_group_suggestion"
                                  @complete="store.searchCustomerGroup($event)"
                                  placeholder="Select Customer Groups"
                                  class="w-full " />
                </VhField>


            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Select Created Date:</b>
                </template>

                <Calendar v-model="store.selected_dates"
                          selectionMode="range"
                          @date-select="store.setDateRange"
                          :manualInput="false"
                          class="w-full"
                          append-to="self"
                          data-testid="users-filter-created_at"
                          placeholder="Choose date range"

                />


            </VhFieldVertical >
            <Divider/>



            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 value=""
                                 data-testid="users-filter_sort_none"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 value="updated_at"
                                 data-testid="users-filter_sort_asc"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 value="updated_at:desc"
                                 data-testid="users-filter_sort_desc"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-descending">Updated (Descending)</label>
                </div>

            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Is Active:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="active-all"
                                 value="null"
                                 data-testid="users-filter_active_all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 value="true"
                                 data-testid="users-filter_active_only"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 value="false"
                                 data-testid="users-filter_inactive_only"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-false">Only Inactive</label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Trashed:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="trashed-exclude"
                                 value=""
                                 data-testid="users-filter_trash_exclude"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 value="include"
                                 data-testid="users-filter_trash_include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 value="only"
                                 data-testid="users-filter_trash_only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Sidebar>

    </div>
</template>

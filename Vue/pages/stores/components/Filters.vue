<script  setup>

import { useStoreStore } from '../../../stores/store-stores'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
const store = useStoreStore();

</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right">

            <VhFieldVertical >

                <div class="field-checkbox">
                    <Checkbox name="multi-language-yes"
                              data-testid="multi-language-yes"
                              v-model = "store.query.filter.is_multi_language"
                              :value="true" />
                    <label for="multi-language-yes"> Multi Language </label>
                </div>

                <div class="field-checkbox">
                    <Checkbox name="multi-currency-yes"
                              data-testid="multi-currency-yes"
                              v-model = "store.query.filter.is_multi_currency"
                              :value="true" />
                    <label for="multi-currency-yes"> Multi Currency </label>
                </div>

                <div class="field-checkbox">
                    <Checkbox name="default-store-yes"
                              data-testid="default-store-yes"
                              v-model = "store.query.filter.default"
                              :value="true" />
                    <label for="default-store-yes"> Default Store </label>
                </div>

            </VhFieldVertical>
            
            <VhFieldVertical >
                <template #label>
                    <b>Multi Vendor:</b>
                </template>
                <div class="field-radiobutton">
                    <RadioButton name="multi-vendor-yes"
                                 value="true"
                                 data-testid="stores-filters-multi-vendor-yes"
                                 v-model="store.query.filter.is_multi_vendor" />
                    <label for="multi-vendor-yes">Yes</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="multi-vendor-no"
                                 value="false"
                                 data-testid="stores-filters-multi-vendor-no"
                                 v-model="store.query.filter.is_multi_vendor" />
                    <label for="multi-vendor-no">No</label>
                </div>
            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Status:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="status-all"
                                 value="all"
                                 data-testid="stores-filters-status-all"
                                 v-model="store.query.filter.status" />
                    <label for="status-all">All</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="status-pending"
                                 value="pending"
                                 data-testid="stores-filters-status-pending"
                                 v-model="store.query.filter.status" />
                    <label for="status-pending">Pending</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="status-approved"
                                 data-testid="stores-filters-status-approved"
                                 value="approved"
                                 v-model="store.query.filter.status" />
                    <label for="status-approved">Approved</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="status-rejected"
                                 data-testid="stores-filters-status-rejected"
                                 value="rejected"
                                 v-model="store.query.filter.status" />
                    <label for="status-rejected">Rejected</label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Date Range Filter:</b>
                </template>

                <Calendar v-model="store.selected_dates"
                          selectionMode="range"
                          @date-select="store.setDateRange"
                          :manualInput="false"/>

                </VhFieldVertical >

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="stores-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="stores-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="stores-filters-sort-descending"
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
                                 data-testid="stores-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="stores-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="stores-filters-active-false"
                                 value="false"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-false" class="cursor-pointer">Only Inactive</label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Trashed:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="trashed-exclude"
                                 inputId="trashed-exclude"
                                 data-testid="stores-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="stores-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="stores-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Sidebar>

    </div>
</template>

<script  setup>

import { useAddressStore } from '../../../stores/store-addresses'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import VhField from './../../../vaahvue/vue-three/primeflex/VhField.vue'

const store = useAddressStore();

</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right">

            <VhFieldVertical>
                <template #label>
                    <b>Address Type:</b>
                </template>
                <div class="field-autocomplete">
                    <AutoComplete
                        v-model="store.query.filter.address_type"
                        @change="store.setAddressTypeFilter($event)"
                        name="addresses-type"
                        :suggestions="store.type_suggestion"
                        @complete="store.searchType($event)"
                        placeholder="Select Type"
                        :dropdown="true" optionLabel="name"
                        data-testid="addresses-type"
                        forceSelection>
                    </AutoComplete>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Default Store:</b>
                </template>
                <div class="field-radiobutton">
                    <RadioButton name="default-address-yes"
                                 value="true"
                                 data-testid="stores-filters-default-address-yes"
                                 v-model="store.query.filter.is_default" />
                    <label for="default-address-yes">Yes</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="default-address-no"
                                 value="false"
                                 data-testid="stores-filters-default-address-no"
                                 v-model="store.query.filter.is_default" />
                    <label for="default-address-no">No</label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical>

                <template #label>
                    <b>User:</b>
                </template>

                <AutoComplete
                    v-model="store.query.filter.user"
                    @change="store.setUserSlug($event)"
                    name="addresses-user"
                    :suggestions="store.user_suggestion"
                    @complete="store.searchUser($event)"
                    placeholder="Select User"
                    :dropdown="true" optionLabel="first_name"
                    data-testid="addresses-user"
                    forceSelection>
                </AutoComplete>

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
                    <b>Status:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="status-pending"
                                 value="pending"
                                 data-testid="addresses-filters-status-pending"
                                 v-model="store.query.filter.status" />
                    <label for="status-pending">Pending</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="status-rejected"
                                 data-testid="addresses-filters-status-rejected"
                                 value="rejected"
                                 v-model="store.query.filter.status" />
                    <label for="status-rejected">Rejected</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="status-approved"
                                 data-testid="addresses-filters-status-approved"
                                 value="approved"
                                 v-model="store.query.filter.status" />
                    <label for="status-approved">Approved</label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="addresses-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="addresses-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="addresses-filters-sort-descending"
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
                                 data-testid="addresses-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="addresses-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="addresses-filters-active-false"
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
                                 data-testid="addresses-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="addresses-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="addresses-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Sidebar>

    </div>
</template>

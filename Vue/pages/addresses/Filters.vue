<script  setup>

import { useAddressStore } from '../../stores/store-addresses'
import VhFieldVertical from './../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import { useRootStore } from '@/stores/root'

const store = useAddressStore();
const root = useRootStore();

</script>

<template>
    <Panel :pt="root.panel_pt">
        <template class="p-1" #header>
            <b class="mr-1">Filters</b>
        </template>

        <template #icons>
            <Button data-testid="projects-hide-filter"
                    as="router-link"
                    :to="`/addresses`"
                    icon="pi pi-times"
                    rounded
                    variant="text"
                    severity="contrast"
                    size="small">
            </Button>
        </template>

            <VhFieldVertical>
                <template #label>
                    <b>Address Type:</b>
                </template>
                <div class="field-autocomplete">
                    <AutoComplete
                        v-model="store.filter_selected_address_type"
                        @change="store.setAddressTypeFilter($event)"
                        name="addresses-type"
                        :suggestions="store.type_suggestion"
                        @complete="store.searchType($event)"
                        placeholder="Select Type"
                        :dropdown="true" optionLabel="name"
                        data-testid="addresses-type"
                        forceSelection
                        append-to="self"
                        class="w-full">
                    </AutoComplete>
                </div>

            </VhFieldVertical>



            <VhFieldVertical>

                <template #label>
                    <b>User:</b>
                </template>

                <AutoComplete
                    name="addresses-user-filter"
                    data-testid="addresses-user-filter"
                    v-model="store.filter_selected_users"
                    @change="store.setFilterUser($event)"
                    option-label = "first_name"
                    multiple
                    :dropdown="true"
                    :complete-on-focus = "true"
                    :suggestions="store.user_suggestion"
                    @complete="store.searchUser($event)"
                    placeholder="Select User"
                    append-to="self"
                    class="w-full">
                </AutoComplete>

            </VhFieldVertical>


            <VhFieldVertical >
                <template #label>
                    <b>Select Created Date:</b>
                </template>

                <DatePicker v-model="store.selected_dates"
                          selectionMode="range"
                          @date-select="store.setDateRange"
                          data-testid="addresses-filters-date_range"
                          :manualInput="false"
                          class="w-full"
                            showIcon
                          placeholder="Choose Date Range"/>

            </VhFieldVertical >

            <VhFieldVertical >
                <template #label>
                    <b>Status By:</b>
                </template>
                    <MultiSelect
                        v-model="store.query.filter.status"
                        :options="store.status"
                        filter
                        optionValue="slug"
                        optionLabel="name"
                        data-testid="addresses-filter-status"
                        placeholder="Select Status"
                        display="chip"
                        append-to="self"
                        class="w-full relative" />


            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Default Address:</b>
                </template>
                <div class="field-radiobutton">
                    <RadioButton name="default-address-yes"
                                 value="true"
                                 data-testid="addresses-filters-default-address-yes"
                                 v-model="store.query.filter.is_default" />
                    <label for="default-address-yes">Yes</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="default-address-no"
                                 value="false"
                                 data-testid="addresses-filters-default-address-no"
                                 v-model="store.query.filter.is_default" />
                    <label for="default-address-no">No</label>
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


    </Panel>
</template>

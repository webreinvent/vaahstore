<script  setup>

import { useBrandStore } from '../../stores/store-brands'
import VhFieldVertical from '../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import VhField from '../../vaahvue/vue-three/primeflex/VhField.vue'

const store = useBrandStore();
</script>

<template>
    <div>

        <Panel >

            <template class="p-1" #header>

                <b class="mr-1">Filters</b>

            </template>

            <template #icons>

                <Button data-testid="vendors-hide-filter"
                        @click="store.toList()"
                        icon="pi pi-times"
                        rounded
                        variant="text"
                        severity="contrast"
                        size="small">
                </Button>

            </template>

<!--        <Sidebar v-model:visible="store.show_filters"-->
<!--                 position="right">-->

            <VhFieldVertical >
                <template #label>
                    <b>Status By:</b>
                </template>

                <MultiSelect
                    v-model="store.query.filter.brand_status"
                    :options="store.assets.taxonomy.status"
                    filter
                    option-value="slug"
                    optionLabel="name"
                    placeholder="Select Status"
                    display="chip"
                    appendTo="self"
                    class="w-full relative" />

            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Select Created Date:</b>
                </template>

                <DatePicker v-model="store.selected_dates"
                          selectionMode="range"
                          @date-select="store.setDateRange"
                          data-testid="brands-filters-created_date"
                          placeholder="Choose Date Range"
                          append-to="self"
                          class="w-full"
                            showIcon
                          :manualInput="false"/>

            </VhFieldVertical >

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="brands-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="brands-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="brands-filters-sort-descending"
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
                                 data-testid="brands-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="brands-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="brands-filters-active-false"
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
                                 data-testid="brands-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="brands-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="brands-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


<!--        </Sidebar>-->
        </Panel>

    </div>
</template>

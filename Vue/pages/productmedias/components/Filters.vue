<script  setup>

import { useProductMediaStore } from '../../../stores/store-productmedias'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'

const store = useProductMediaStore();

</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right">
            <VhFieldVertical >
                <template #label>
                    <b>Status By:</b>
                </template>
                <VhField label="Status">


                    <AutoComplete name="productmedias-filter"
                                  data-testid="productmedias-filter"
                                  v-model="store.selected_status"
                                  @change = "store.addStatus()"
                                  option-label = "slug"
                                  multiple
                                  :complete-on-focus = "true"
                                  :suggestions="store.status_suggestion"
                                  @complete="store.searchStatus($event)"
                                  placeholder="Select Status"
                                  class="w-full " />

                </VhField>


            </VhFieldVertical>


            <VhFieldVertical >
                <template #label>
                    <b>Product Variation By:</b>
                </template>
                <VhField label="Product Variation">
                    <MultiSelect
                        v-model="store.query.filter.product_variation"
                        :options="store.assets.active_product_variations"
                        filter
                        optionValue="name"
                        optionLabel="name"
                        placeholder="Select Product Variation"
                        display="chip"
                        class="w-full" />
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
                          placeholder="Choose date range"

                />


            </VhFieldVertical >

            <VhFieldVertical >
                <template #label>
                    <b>Media Type:</b>
                </template>
                <VhField label="Type">
                    <MultiSelect
                        v-model="store.query.filter.type"
                        :options="store.media_type"
                        filter
                        optionValue="name"
                        optionLabel="name"
                        placeholder="Select Media Type"
                        display="chip"
                        class="w-full" />
                </VhField>


            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="productmedias-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="productmedias-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="productmedias-filters-sort-descending"
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
                                 data-testid="productmedias-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="productmedias-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="productmedias-filters-active-false"
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
                                 data-testid="productmedias-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="productmedias-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="productmedias-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Sidebar>

    </div>
</template>

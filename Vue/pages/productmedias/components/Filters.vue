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
                    <b>Product Variations By:</b>
                </template>
                <VhField label="Product Variation">

                    <AutoComplete name="productmedias-variations-filter"
                                  data-testid="productmedias-filters-variations"
                                  v-model="store.selected_variation"
                                  @change = "store.setVariationFilter()"
                                  option-label = "name"
                                  option-value = "name"
                                  multiple
                                  :complete-on-focus = "true"
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
                                  :suggestions="store.variation_suggestion"
                                  @complete="store.searchVariation($event)"
                                  placeholder="Select Variations"
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
                          data-testid="productmedias-filters-created_date"
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
                    <AutoComplete name="productmedias-filter"
                                  data-testid="productmedias-filters-media_type"
                                  v-model="store.selected_media"
                                  @change = "store.addMedia()"
                                  option-label = "type"
                                  multiple
                                  :complete-on-focus = "true"
                                  :suggestions="store.media_suggestion"
                                  @complete="store.searchMediaType($event)"
                                  placeholder="Select Media Type"
                                  class="w-full " />
                </VhField>


            </VhFieldVertical>


            <VhFieldVertical >
                <template #label>
                    <b>Status By:</b>
                </template>
                <VhField label="Status">
                    <MultiSelect
                        v-model="store.query.filter.media_status"
                        :options="store.assets.status"
                        filter
                        optionValue="name"
                        optionLabel="name"
                        data-testid="productmedias-filters-status"
                        placeholder="Select Status"
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

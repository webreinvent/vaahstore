<script  setup>

import { useProductAttributeStore } from '../../../stores/store-productattributes'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'

const store = useProductAttributeStore();

</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right">

            <VhFieldVertical >
                <div class="field-checkbox">
                    <Checkbox name="default-product-variation-yes"
                              data-testid="product-variation-yes"
                              v-model = "store.filter_default_product_variation"
                              @change="store.updateDefaultProductVariation"
                              :value="'true'" />
                    <label for="default-product-variation-yes">Default Product Variation </label>
                </div>
            </VhFieldVertical>


            <VhFieldVertical >
                <template #label>
                    <b>Attribute:</b>
                </template>

                <AutoComplete
                              name="productattributes-attributes-filter"
                              data-testid="productattributes-attributes-filter"
                              v-model="store.filter_selected_attribute"
                              @change="store.setAttributeFilter($event)"
                              option-label="name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.filtered_attributes"
                              @complete="store.searchAttribute"
                              class="w-full"
                              placeholder="Select Attributes">
                </AutoComplete>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="productattributes-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="productattributes-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="productattributes-filters-sort-descending"
                                 value="updated_at:desc"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-descending" class="cursor-pointer">Updated (Descending)</label>
                </div>

            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Date:</b>
                </template>

                <div class="field-radiobutton">

                    <Calendar v-model="store.selected_dates"
                              name="range-date"
                              inputId="range-date"
                              data-testid="productattributes-filters-range-date"
                              selectionMode="range"
                              @date-select="store.setDateRange"
                              :manualInput="false" />

                    <label for="range-date" class="cursor-pointer"></label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Trashed:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="trashed-exclude"
                                 inputId="trashed-exclude"
                                 data-testid="productattributes-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="productattributes-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="productattributes-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Sidebar>

    </div>
</template>

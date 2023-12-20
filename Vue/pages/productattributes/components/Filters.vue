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
                <template #label>
                    <b>Product Variation:</b>
                </template>

                <AutoComplete v-model="store.query.filter.product_variation"
                              class="w-full"
                              data-testid="productattributes-product-variation-filter"
                              :suggestions="store.filtered_product_variations"
                              @complete="store.searchProductVariation"
                              @change="store.setProductVariationFilter($event)"
                              :dropdown="true"
                              optionLabel="name"
                              placeholder="Select Product variation"
                              forceSelection>
                </AutoComplete>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Attribute:</b>
                </template>

                <AutoComplete v-model="store.query.filter.attributes"
                              class="w-full"
                              placeholder="Select Attributes"
                              data-testid="productattributes-attributes-filter"
                              :suggestions="store.filtered_attributes"
                              @complete="store.searchAttribute"
                              @change="store.setAttributeFilter($event)"
                              :dropdown="true"
                              optionLabel="name"
                              forceSelection>
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

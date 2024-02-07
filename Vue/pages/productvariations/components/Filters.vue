<script  setup>

import { useProductVariationStore } from '../../../stores/store-productvariations'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import {onMounted} from "vue";
import {useRoute} from "vue-router";
const route = useRoute();

const store = useProductVariationStore();

onMounted(async () => {

    await store.setQuantityRange();

    await store.setProductInFilter();


});

import { ref } from 'vue';

const value = ref([20, 80]);
</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right">

            <VhFieldVertical >
                <template #label>
                    <b>Product:</b>
                </template>


                <AutoComplete
                    value="id"
                    v-model="store.selected_product"
                    @change="store.setProductFilter($event)"
                    class="w-full"
                    :suggestions="store.filtered_products"
                    @complete="store.searchProduct($event)"
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
                    placeholder="Select Product"
                    data-testid="productvariations-product"
                    append-to="self"
                    name="productvariations-product"
                    :dropdown="true" optionLabel="name" forceSelection>
                </AutoComplete>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>In Stock:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="in-stock-yes"
                                 value="true"
                                 data-testid="stores-filters-in-stock-yes"
                                 v-model="store.query.filter.in_stock" />
                    <label for="in-stock-yes">Yes</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="in-stock-no"
                                 value="false"
                                 data-testid="stores-filters-in-stock-no"
                                 v-model="store.query.filter.in_stock" />
                    <label for="in-stock-no">No</label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Default Product Variation:</b>
                </template>
                <div class="field-radiobutton">
                    <RadioButton name="default-product-variation-yes"
                                 value="true"
                                 data-testid="stores-filters-default-product-variation-yes"
                                 v-model="store.query.filter.default" />
                    <label for="default-product-variation-yes">Yes</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="default-product-variation-no"
                                 value="false"
                                 data-testid="stores-filters-default-product-variation-no"
                                 v-model="store.query.filter.default" />
                    <label for="default-product-variation-no">No</label>
                </div>
            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Status By:</b>
                </template>
                <VhField label="Status">
                    <MultiSelect
                        v-model="store.query.filter.product_variation_status"
                        :options="store.assets.taxonomy.status"
                        filter
                        option-value="slug"
                        optionLabel="name"
                        placeholder="Select Status"
                        display="chip"
                        class="w-full relative"
                        appendTo="self"
                    />
                </VhField>


            </VhFieldVertical>


            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="productvariations-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="productvariations-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="productvariations-filters-sort-descending"
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
                                 data-testid="productvariations-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="productvariations-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="productvariations-filters-active-false"
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
                                 data-testid="productvariations-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="productvariations-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="productvariations-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Select Created Date :</b>
                </template>

                <div class="field-radiobutton">

                    <Calendar v-model="store.selected_dates"
                              name="range-date"
                              inputId="range-date"
                              placeholder="Choose date range"
                              data-testid="productvariation-filters-range-date"
                              selectionMode="range"
                              @date-select="store.setDateRange"
                              :manualInput="false"/>

                    <label for="range-date" class="cursor-pointer"></label>
                </div>

            </VhFieldVertical>


            <Divider/>


            <VhFieldVertical >
                <template #label>
                    <b>Quantity Count Range:</b>
                </template>

                <div class="card flex justify-content-center">
                    <div class="w-14rem">
                        <div class="flex justify-content-between">
                            <badge>{{ store.min_quantity | bold }}</badge>
                            <badge>{{ store.max_quantity | bold }}</badge>

                        </div>

                        <Slider v-model="store.quantity"
                                range
                                :min="store.assets.min_quantity"
                                :max="store.assets.max_quantity"
                                @change="store.quantityFilter()"
                                class="w-14rem mt-2"
                        />
                    </div>
                </div>

            </VhFieldVertical>



        </Sidebar>

    </div>
</template>


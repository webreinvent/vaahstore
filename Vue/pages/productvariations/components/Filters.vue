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
</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right">

            <VhFieldVertical >
                <template #label>
                    <b>Product:</b>
                </template>

                <AutoComplete name="product-variations-product-filter"
                              data-testid="product-variations-product-filter"
                              v-model="store.selected_products"
                              @change = "store.addSelectedProduct()"
                              option-label = "name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.products_suggestion"
                              @complete="store.searchProduct($event)"
                              placeholder = "select product"
                              class="w-full "
                              append-to="self"
                              :pt="{
                          token: {
                                    class: 'max-w-full'
                                  },
                          removeTokenIcon: {
                                    class: 'min-w-max'
                          },
                          item: { style:
                                {
                                textWrap: 'wrap'
                                }  },
                          panel: { class: 'w-16rem ' }
                            }"/>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Stock Status:</b>
                </template>

                <VhField label="Stock Status">
                    <MultiSelect
                        v-model="store.query.filter.stock_status"
                        :options="store.stock_options"
                        filter
                        option-value="value"
                        optionLabel="label"
                        placeholder="Select Stock Status"
                        display="chip"
                        class="w-full relative"
                        data-testid="product-variations-stock-status-filters"
                        appendTo="self"
                    />
                </VhField>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Default Product Variation:</b>
                </template>
                <div class="field-radiobutton">
                    <RadioButton name="default-product-variation-yes"
                                 value="true"
                                 data-testid="product-variations-filters-default-product-variation-yes"
                                 v-model="store.query.filter.default" />
                    <label for="default-product-variation-yes">Yes</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="default-product-variation-no"
                                 value="false"
                                 data-testid="product-variations-filters-default-product-variation-no"
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
                        data-testid="product-variations-status-filters"
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
                                 data-testid="product-variations-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="product-variations-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="product-variations-filters-sort-descending"
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
                                 data-testid="product-variations-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="product-variations-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="product-variations-filters-active-false"
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
                                 data-testid="product-variations-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="product-variations-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="product-variations-filters-trashed-only"
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
                              data-testid="product-variation-filters-range-date"
                              selectionMode="range"
                              @date-select="store.setDateRange"
                              class="w-full"
                              append-to="self"
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
                                :min="store.assets.min_max_quantity.min_quantity"
                                :max="store.assets.min_max_quantity.max_quantity"
                                data-testid="product-variation-sliders"
                                @change="store.quantityFilter()"
                                class="w-14rem mt-2"
                        />
                    </div>
                </div>

            </VhFieldVertical>



        </Sidebar>

    </div>
</template>


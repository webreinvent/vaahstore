<script  setup>

import { useProductVariationStore } from '../../stores/store-productvariations'
import VhFieldVertical from './../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import { useRootStore } from '@/stores/root'
import {onMounted} from "vue";
import {useRoute} from "vue-router";
const route = useRoute();

const store = useProductVariationStore();
const root = useRootStore();

onMounted(async () => {

    /*await store.setQuantityRange();*/
    await store.setProductInFilter();


});
</script>

<template>
    <Panel :pt="root.panel_pt">
        <template class="p-1" #header>
            <b class="mr-1">Filters</b>
        </template>

        <template #icons>
            <Button data-testid="projects-hide-filter"
                    as="router-link"
                    :to="`/productvariations`"
                    icon="pi pi-times"
                    rounded
                    variant="text"
                    severity="contrast"
                    size="small">
            </Button>
        </template>

                <AutoComplete name="product-variations-product-filter"
                              data-testid="product-variations-product-filter"
                              v-model="store.selected_products"
                              @change = "store.addSelectedProduct()"
                              option-label = "name"
                              multiple
                              :dropdown="true"
                              :complete-on-focus = "true"
                              :suggestions="store.products_suggestion"
                              @complete="store.searchProduct($event)"
                              placeholder = "Select Product"
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

       <Divider/>

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

                    <DatePicker v-model="store.selected_dates"
                              name="range-date"
                              inputId="range-date"
                              placeholder="Choose Date Range"
                              data-testid="product-variation-filters-range-date"
                              selectionMode="range"
                              @date-select="store.setDateRange"
                              class="w-full"
                                showIcon
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
                    <div class="w-full">

                        <InputNumber
                            v-model="store.query.filter.min_quantity"
                            data-testid="product-variation-filter-min_quantity"
                            placeholder="Enter minimum quantity"
                            @input="store.quantityFilterMin($event)"
                            class="w-full mt-2"

                        />

                        <InputNumber
                            v-model="store.query.filter.max_quantity"
                            data-testid="product-variation-filter-max_quantity"
                            placeholder="Enter maximum quantity"
                            @input="store.quantityFilterMax($event)"
                            class="w-full mt-2"

                        />
                    </div>
                </div>

            </VhFieldVertical>



    </Panel>
</template>


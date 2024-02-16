<script  setup>

import { useProductStockStore } from '../../../stores/store-productstocks'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'

const store = useProductStockStore();

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
                    <MultiSelect
                        v-model="store.query.filter.status"
                        :options="store.assets.taxonomy.status"
                        filter
                        optionValue="slug"
                        optionLabel="slug"
                        placeholder="Select Status"
                        display="chip"
                        class="w-full" />
                </VhField>


            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Vendor:</b>
                </template>

                <AutoComplete name="product-stocks-vendor-filter"
                              data-testid="product-stocks-vendor-filter"
                              v-model="store.selected_vendors"
                              @change = "store.addSelectedVendor()"
                              option-label = "name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.vendors_suggestion"
                              @complete="store.searchVendors($event)"
                              placeholder = "select vendor"
                              class="w-full "
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
                    <b>Product:</b>
                </template>

                <AutoComplete name="product-stocks-product-filter"
                              data-testid="product-stocks-product-filter"
                              v-model="store.selected_products"
                              @change = "store.addSelectedProduct()"
                              option-label = "name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.products_suggestion"
                              @complete="store.searchProduct($event)"
                              placeholder = "select product"
                              class="w-full "
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
                    <b>Product Variation:</b>
                </template>

                <AutoComplete name="product-stocks-variation-filter"
                              data-testid="product-stocks-variation-filter"
                              v-model="store.selected_variations"
                              @change = "store.addSelectedVariation()"
                              option-label = "name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.product_variations_suggestion"
                              @complete="store.searchVariations($event)"
                              placeholder = "select product variation"
                              class="w-full "
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
                            }"
                />

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Warehouse:</b>
                </template>

                <AutoComplete name="product-stocks-warehouse-filter"
                              data-testid="product-stocks-warehouse-filter"
                              v-model="store.selected_warehouses"
                              @change = "store.addSelectedWarehouse()"
                              option-label = "name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.warehouses_suggestion"
                              @complete="store.searchWarehouses($event)"
                              placeholder="select warehouse"
                              class="w-full "
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
                    <b>Select Created Date:</b>
                </template>

                <Calendar v-model="store.selected_dates"
                          selectionMode="range"
                          @date-select="store.setDateRange"
                          :manualInput="false"
                          class="w-full"
                          placeholder="Choose Date Range"/>

            </VhFieldVertical >


            <VhFieldVertical >
                <template #label>
                    <b>Quantity Count Range:</b>
                </template>

                <div class="card flex justify-content-center">
                    <div class="w-14rem">
                        <div class="flex justify-content-between">
                            <div><b>{{ store.min_quantity | bold }}</b></div>
                            <div><b>{{ store.max_quantity | bold }}</b></div>
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


            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="productstocks-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="productstocks-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="productstocks-filters-sort-descending"
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
                                 data-testid="productstocks-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="productstocks-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="productstocks-filters-active-false"
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
                                 data-testid="productstocks-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="productstocks-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="productstocks-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Sidebar>

    </div>
</template>

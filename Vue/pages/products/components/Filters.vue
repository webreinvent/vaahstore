<script  setup>

import { useProductStore } from '../../../stores/store-products'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'

const store = useProductStore();

</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right">

            <VhFieldVertical >
                <template #label>
                    <b>Product Variation:</b>
                </template>

                <AutoComplete name="products-variation-filter"
                              data-testid="products-variation-filter"
                              v-model="store.selected_product_variations"
                              @change = "store.addProductVariation()"
                              option-label = "name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.filtered_product_variations"
                              @complete="store.searchProductVariation"
                              placeholder="Select Product Variation"
                              class="w-full "
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
                />

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Vendor:</b>
                </template>

                <AutoComplete name="products-vendor-filter"
                              data-testid="products-vendor-filter"
                              v-model="store.selected_vendors"
                              @change = "store.addProductVendor()"
                              option-label = "name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.filtered_vendors"
                              @complete="store.searchProductVendor"
                              placeholder="Select Vendor"
                              class="w-full "
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
                                                }"/>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Brand:</b>
                </template>

                <AutoComplete name="products-brand-filter"
                              data-testid="products-brand-filter"
                              v-model="store.filter_selected_brands"
                              @change = "store.addFilterBrand()"
                              option-label = "name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.filtered_brands"
                              @complete="store.searchBrand"
                              placeholder="Select Brand"
                              class="w-full "
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
                                                }"/>

            </VhFieldVertical>


            <VhFieldVertical >
                <template #label>
                    <b>Product Type:</b>
                </template>

                <AutoComplete name="products-type-filter"
                              data-testid="products-type-filter"
                              v-model="store.filter_selected_product_type"
                              @change = "store.addFilterProductType()"
                              option-label = "name"
                              multiple
                              :complete-on-focus = "true"
                              :suggestions="store.type_suggestion"
                              @complete="store.searchTaxonomyProduct($event)"
                              placeholder="Select Product Type"
                              class="w-full " />

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Store:</b>
                </template>

                <VhField label="Store*">

                    <AutoComplete
                        name="products-filter-store"
                        data-testid="products-filter-store"
                        v-model="store.filter_selected_store"
                        @change="store.setFilterStore($event)"
                        option-label = "name"
                        multiple
                        :complete-on-focus = "true"
                        class="w-full"
                        :suggestions="store.filtered_stores"
                        @complete="store.searchStore"
                        placeholder="Select Store"
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
                    />


                </VhField>

            </VhFieldVertical>
            <br/>
            <VhFieldVertical >
                <template #label>
                    <b>Quantity Range Filter:</b>
                </template>

                <div class="card flex justify-content-center">
                    <div class="w-14rem">
                        <div class="flex">
                            <label for="min-quantity" class="mr-2 mt-2" style="width:50px;">From:</label>
                            <InputNumber
                                       id="min-quantity"
                                       name="productvariations-name"
                                       data-testid="productvariations-name"
                                       placeholder="Minimum Quantity"
                                       @input="updateMinQuantity()"
                                       v-model="store.quantity.from"
                                       style="width: 200px;"/>
                        </div>

                        <div class="flex mt-2">
                            <label for="max-quantity" class="mr-2 mt-2" style="width:50px;">To:</label>
                            <InputNumber class="ml-3"
                                       id="max-quantity"
                                       name="productvariations-name"
                                       data-testid="productvariations-name"
                                       placeholder="Maximum Quantity"
                                         @input="updateMinQuantity()"
                                       v-model="store.quantity.to"
                                       style="width: 200px;"/>
                        </div>

                    </div>
                </div>

            </VhFieldVertical>

            <br/>

            <VhFieldVertical >
                <template #label>
                    <b>Status:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="status-pending"
                                 value="pending"
                                 data-testid="stores-filters-status-pending"
                                 v-model="store.query.filter.status" />
                    <label for="status-pending">Pending</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="status-approved"
                                 data-testid="stores-filters-status-approved"
                                 value="approved"
                                 v-model="store.query.filter.status" />
                    <label for="status-approved">Approved</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="status-rejected"
                                 data-testid="stores-filters-status-rejected"
                                 value="rejected"
                                 v-model="store.query.filter.status" />
                    <label for="status-rejected">Rejected</label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="products-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="products-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="products-filters-sort-descending"
                                 value="updated_at:desc"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-descending" class="cursor-pointer">Updated (Descending)</label>
                </div>

            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Date Range Filter:</b>
                </template>

                <Calendar v-model="store.selected_dates"
                          selectionMode="range"
                          @date-select="store.setDateRange"
                          :manualInput="false"
                          class="w-full "

                />

            </VhFieldVertical >

            <VhFieldVertical >
                <template #label>
                    <b>Is Active:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="active-all"
                                 inputId="active-all"
                                 value="null"
                                 data-testid="products-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="products-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="products-filters-active-false"
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
                                 data-testid="products-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="products-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="products-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Sidebar>

    </div>
</template>

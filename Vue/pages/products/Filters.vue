<script  setup>

import { useProductStore } from '../../stores/store-products'
import VhFieldVertical from './../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import {computed, onMounted} from "vue";
import { useRootStore } from '@/stores/root'


const store = useProductStore();
const root = useRootStore();

onMounted(async () => {

    await store.getCategories();

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
                    :to="`/products`"
                    icon="pi pi-times"
                    rounded
                    variant="text"
                    severity="contrast"
                    size="small">
            </Button>
        </template>

        <div>
            <b>Price Range:</b>
            <div class="flex gap-2">
                <InputNumber v-model="store.query.filter.min_price" placeholder="Minimum Price" class="w-full" />
                <InputNumber v-model="store.query.filter.max_price" placeholder="Maximum Price" class="w-full" />
            </div>
        </div>

        <Divider />

        <div>
            <b>Categories By:</b>
            <TreeSelect v-model="store.product_category_filter"
                        :options="store.categories_dropdown_data"
                        selectionMode="multiple"
                        display="chip"
                        placeholder="Select Category"
                        :show-count="true"
                        class="w-full" />
        </div>

        <Divider />

        <div>
            <b>Status:</b>
            <MultiSelect v-model="store.query.filter.status"
                         :options="store.assets.taxonomy.product_status"
                         filter
                         optionValue="slug"
                         optionLabel="name"
                         placeholder="Select Status"
                         display="chip"
                         class="w-full" />
        </div>

        <Divider/>

        <div>
            <b>Product Variation:</b>
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
                          append-to="self"
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
        </div>

        <Divider />

        <div>
                <b>Vendor:</b>


            <AutoComplete name="products-vendor-filter"
                          data-testid="products-vendor-filter"
                          v-model="store.selected_vendors"
                          @change = "store.addProductVendor()"
                          option-label = "name"
                          multiple
                          :complete-on-focus = "true"
                          :suggestions="store.filtered_vendors"
                          @complete="store.searchVendor($event)"
                          placeholder="Select Vendor"
                          class="w-full "
                          append-to="self"
                          :pt="{ token:
                               {class: 'max-w-full'},
                               removeTokenIcon: {class: 'min-w-max'},
                               item: { style: {
                                      textWrap: 'wrap'
                                      }  },
                               panel: { class: 'w-16rem ' }
                                                }"/>

        </div>

        <Divider />

        <div>

                <b>Brand:</b>

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
                          append-to="self"
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

        </div>

        <Divider/>


        <div>

                <b>Product Type:</b>


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
                          append-to="self"
                          class="w-full " />

        </div>

        <Divider/>

        <div >

                <b>Store:</b>




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
                    append-to="self"
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



        </div>
        <Divider/>

        <div >

                <b>Quantity Count Range:</b>

            <div class="card flex justify-content-center">
                <div class="w-14rem">

                    <InputNumber
                        v-model="store.query.filter.min_quantity"
                        data-testid="product-filter-min_quantity"
                        placeholder="Enter minimum quantity"
                        @input="store.minQuantity($event)"
                        class="w-14rem mt-2"

                    />
                    <InputNumber
                        v-model="store.query.filter.max_quantity"
                        data-testid="product-filter-max_quantity"
                        placeholder="Enter maximum quantity"
                        @input="store.maxQuantity($event)"
                        class="w-14rem mt-2"

                    />
                </div>
            </div>

        </div>

        <Divider />

        <div>
            <b>Sort By:</b>
            <div class="flex items-center gap-2">
                <RadioButton name="sort-none" inputId="sort-none" value="" v-model="store.query.filter.sort" />
                <label for="sort-none" class="cursor-pointer">None</label>
            </div>
            <div class="flex items-center gap-2">
                <RadioButton name="sort-ascending" inputId="sort-ascending" value="updated_at" v-model="store.query.filter.sort" />
                <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
            </div>
            <div class="flex items-center gap-2">
                <RadioButton name="sort-descending" inputId="sort-descending" value="updated_at:desc" v-model="store.query.filter.sort" />
                <label for="sort-descending" class="cursor-pointer">Updated (Descending)</label>
            </div>
        </div>

        <Divider />

        <div>
            <b>Select Created Date:</b>
            <DatePicker v-model="store.selected_dates"
                      selectionMode="range"
                        size="small"
                      placeholder="Choose Date Range"
                      class="w-full" />
        </div>

        <Divider />

        <div>
            <b>Is Active:</b>
            <div class="flex items-center gap-2">
                <RadioButton name="active-all" inputId="active-all" value="null" v-model="store.query.filter.is_active" />
                <label for="active-all" class="cursor-pointer">All</label>
            </div>
            <div class="flex items-center gap-2">
                <RadioButton name="active-true" inputId="active-true" value="true" v-model="store.query.filter.is_active" />
                <label for="active-true" class="cursor-pointer">Only Active</label>
            </div>
            <div class="flex items-center gap-2">
                <RadioButton name="active-false" inputId="active-false" value="false" v-model="store.query.filter.is_active" />
                <label for="active-false" class="cursor-pointer">Only Inactive</label>
            </div>
        </div>

        <Divider />

        <div>
            <b>Trashed:</b>
            <div class="flex items-center gap-2">
                <RadioButton name="trashed-exclude" inputId="trashed-exclude" value="" v-model="store.query.filter.trashed" />
                <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
            </div>
            <div class="flex items-center gap-2">
                <RadioButton name="trashed-include" inputId="trashed-include" value="include" v-model="store.query.filter.trashed" />
                <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
            </div>
            <div class="flex items-center gap-2">
                <RadioButton name="trashed-only" inputId="trashed-only" value="only" v-model="store.query.filter.trashed" />
                <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
            </div>
        </div>
    </Panel>
</template>


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
                    <b>Store:</b>
                </template>

                <AutoComplete name="products-store-filter"
                              data-testid="products-store-filter"
                              v-model="store.query.filter.store"
                              @change="store.setFilterStore($event)"
                              option-label = "name"
                              :complete-on-focus = "true"
                              :suggestions="store.filtered_stores"
                              @complete="store.searchStore"
                              :dropdown="true"
                              optionLabel="name"
                              />

            </VhFieldVertical>

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
                    <b>Quantity:</b>
                </template>

                <VhField label="Quantity">
                    <InputNumber
                        placeholder="Enter a Quantity"
                        inputId="minmax-buttons"
                        name="products-quantity"
                        v-model="store.query.filter.quantity"
                        @input = "store.updateQuantityFilter($event)"
                        showButtons
                        :min="0"
                        data-testid="products-quantity"/>
                </VhField>

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

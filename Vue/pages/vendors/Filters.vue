<script  setup>

import { useVendorStore } from '../../stores/store-vendors'
import VhFieldVertical from '../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import VhField from '../../vaahvue/vue-three/primeflex/VhField.vue'
import {onMounted} from "vue";

const store = useVendorStore();

onMounted(async () => {

    await store.setProductInFilter();


});

</script>

<template>
    <div>
        <Panel >

            <template class="p-1" #header>

                <b class="mr-1">Filters</b>

            </template>

            <template #icons>

                <Button data-testid="campaigns-hide-filter"
                        @click="store.toList()"
                        icon="pi pi-times"
                        rounded
                        variant="text"
                        severity="contrast"
                        size="small">
                </Button>

            </template>


            <VhFieldVertical >
                <template #label>
                    <b>Product:</b>
                </template>

                <AutoComplete name="vendor-product-filter"
                              data-testid="vendor-product-filter"
                              v-model="store.sel_product"
                              @change = "store.addSelectedProduct()"
                              option-label = "name"
                              multiple
                              :dropdown="true"
                              :complete-on-focus = "true"
                              :suggestions="store.search_products"
                              @complete="store.searchProduct($event)"
                              placeholder = "Select Product"
                              appendTo="self"
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
                    <b>Store By:</b>
                </template>
                <MultiSelect
                    v-model="store.query.filter.store"
                    :options="store.assets.active_stores"
                    filter
                    optionValue="name"
                    optionLabel="name"
                    placeholder="Select Store"
                    display="chip"
                    class="w-full relative"
                    appendTo="self"
                />


            </VhFieldVertical>


            <VhFieldVertical >
                <template #label>
                    <b>Status By:</b>
                </template>
                <MultiSelect
                    v-model="store.query.filter.vendor_status"
                    :options="store.assets.taxonomy.status"
                    filter
                    optionValue="name"
                    optionLabel="name"
                    placeholder="Select Status"
                    display="chip"
                    class="w-full relative"
                    appendTo="self"
                />


            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Select Created Date:</b>
                </template>

                <DatePicker v-model="store.selected_dates"
                          selectionMode="range"
                          @date-select="store.setDateRange"
                          placeholder="Choose date range"
                          :manualInput="false"
                            class="w-full"
                            showIcon/>

            </VhFieldVertical >

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="vendors-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="vendors-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="vendors-filters-sort-descending"
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
                                 data-testid="vendors-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="vendors-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="vendors-filters-active-false"
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
                                 data-testid="vendors-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="vendors-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="vendors-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>



        </Panel>

    </div>
</template>

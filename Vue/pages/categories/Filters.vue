<script  setup>

import { useCategoryStore } from '@/stores/store-categories'
import VhFieldVertical from '@/vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import { useRootStore } from '@/stores/root'
import {ref} from "vue";
import {useRoute, useRouter} from "vue-router";

const store = useCategoryStore();
const route = useRoute();
const router = useRouter();
const root = useRootStore();
</script>

<template>
    <Panel :pt="root.panel_pt" >
        <template class="p-1" #header>

            <b class="mr-1">Filters</b>

        </template>
        <template #icons>

            <Button data-testid="projects-hide-filter"
                    as="router-link"
                    :to="`/categories`"
                    icon="pi pi-times"
                    rounded
                    variant="text"
                    severity="contrast"
                    size="small">
            </Button>

        </template>

                <VhFieldVertical >
                    <template #label>
                        <b>Category By:</b>
                    </template>
                    <TreeSelect
                        v-model="store.category_filter"
                        :options="store.categories_dropdown_data"
                        selectionMode="multiple"
                        display="chip"
                        placeholder="Select Category"
                        :show-count="true"
                        @node-select="store.selectCategoryForFilter($event)"
                        @node-unselect="store.removeCategoryForFilter($event)"
                        data-testid="categories-filters-category"

                        class=" w-full" />
                </VhFieldVertical>
                <VhFieldVertical >
                    <template #label>
                        <b>Select Created Date:</b>
                    </template>

                    <DatePicker v-model="store.selected_dates"
                              selectionMode="range"
                              @date-select="store.setDateRange"
                              data-testid="categories-filters-created_at"
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
                                 data-testid="categories-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="categories-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="categories-filters-sort-descending"
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
                                 data-testid="categories-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="categories-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="categories-filters-active-false"
                                 value="false"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-false" class="cursor-pointer">Only Inactive</label>
                </div>

            </VhFieldVertical>

             <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Trashed:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="trashed-exclude"
                                 inputId="trashed-exclude"
                                 data-testid="categories-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="categories-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="categories-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


        </Panel>

</template>

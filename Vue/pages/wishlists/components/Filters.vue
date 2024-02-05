<script  setup>
import { useWishlistStore } from '../../../stores/store-wishlists'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'

const store = useWishlistStore();

</script>

<template>
    <div>

        <Sidebar v-model:visible="store.show_filters"
                 position="right">

            <VhFieldVertical >
                <template #label>
                    <b>Status By:</b>
                </template>
                <VhField label="Wish List Status">
                    <MultiSelect
                        v-model="store.query.filter.wishlist_status"
                        :options="store.assets.taxonomy.status"
                        filter
                        optionValue="slug"
                        optionLabel="name"
                        placeholder="Select Status"
                        display="chip"
                        class="w-full" />
                </VhField>


            </VhFieldVertical>

            <VhFieldVertical>

                <template #label>
                    <b>User:</b>
                </template>

                <AutoComplete
                    name="wishlists-user-filter"
                    data-testid="wishlists-user-filter"
                    v-model="store.selected_users"
                    @change="store.setFilterSelectedUsers()"
                    option-label = "first_name"
                    multiple
                    :complete-on-focus = "true"
                    :suggestions="store.user_suggestion"
                    @complete="store.searchUsers($event)"
                    placeholder="Select User"
                    class="w-full">
                </AutoComplete>

            </VhFieldVertical>

            <VhFieldVertical>

                <template #label>
                    <b>Product:</b>
                </template>

                <AutoComplete
                    name="wishlists-user-filter"
                    data-testid="wishlists-user-filter"
                    v-model="store.filter_selected_products"
                    @change="store.setFilterSelectedProducts()"
                    option-label = "name"
                    multiple
                    :complete-on-focus = "true"
                    :suggestions="store.product_suggestion"
                    @complete="store.searchProduct($event)"
                    placeholder="Select Product"
                    class="w-full"
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
                            }">
                </AutoComplete>

            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Select Created Date:</b>
                </template>

                <div class="field-radiobutton">

                    <Calendar v-model="store.selected_dates"
                              name="range-date"
                              inputId="range-date"
                              data-testid="wishlist-filters-range-date"
                              selectionMode="range"
                              @date-select="store.setDateRange"
                              placeholder="Choose Date Range"
                              class="w-full"
                              :manualInput="false"/>

                    <label for="range-date" class="cursor-pointer"></label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="wishlists-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="wishlists-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="wishlists-filters-sort-descending"
                                 value="updated_at:desc"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-descending" class="cursor-pointer">Updated (Descending)</label>
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
                                 data-testid="wishlists-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="wishlists-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="wishlists-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>

        </Sidebar>

    </div>
</template>

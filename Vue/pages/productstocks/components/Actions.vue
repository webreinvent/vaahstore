<script  setup>
import {ref, reactive, watch, onMounted} from 'vue';
import { useProductStockStore } from '../../../stores/store-productstocks'

import Filters from '../Filters.vue'

const store = useProductStockStore();

onMounted(async () => {
    store.getListSelectedMenu();
    store.getListBulkMenu();
});

//--------selected_menu_state
const selected_menu_state = ref();
const toggleSelectedMenuState = (event) => {
    selected_menu_state.value.toggle(event);
};
//--------/selected_menu_state

//--------bulk_menu_state
const bulk_menu_state = ref();
const toggleBulkMenuState = (event) => {
    bulk_menu_state.value.toggle(event);
};
//--------/bulk_menu_state
</script>

<template>
    <div>

        <!--actions-->
        <div :class="{'flex justify-content-between': store.isListView()}" class="mt-2 mb-2">

            <!--left-->
            <div v-if="store.view === 'list'">

                <!--selected_menu-->
                <Button class="p-button-sm"
                    type="button"
                    @click="toggleSelectedMenuState"
                    data-testid="productstocks-actions-menu"
                    aria-haspopup="true"
                    aria-controls="overlay_menu"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                >
                    <i class="pi pi-angle-down"></i>
                    <Badge v-if="store.action.items.length > 0"
                           :value="store.action.items.length" />
                </Button>
                <Menu ref="selected_menu_state"
                      :model="store.list_selected_menu"
                      :popup="true" />
                <!--/selected_menu-->

                <!--bulk_menu-->
                <Button
                    type="button"
                    @click="toggleBulkMenuState"
                    data-testid="productstocks-actions-bulk-menu"
                    aria-haspopup="true"
                    aria-controls="bulk_menu_state"
                    :disabled="!store.assets.permissions.includes('can-update-module')"
                    class="ml-1 p-button-sm">
                    <i class="pi pi-ellipsis-h"></i>
                </Button>
                <Menu ref="bulk_menu_state"
                      :model="store.list_bulk_menu"
                      :popup="true" />
                <!--/bulk_menu-->

            </div>
            <!--/left-->

            <!--right-->
            <div >


                <InputGroup>

                            <InputText v-model="store.query.filter.q"
                                       @keyup.enter="store.delayedSearch()"
                                       class="p-inputtext-sm"
                                       @keyup.enter.native="store.delayedSearch()"
                                       @keyup.13="store.delayedSearch()"
                                       data-testid="productstocks-actions-search"
                                       placeholder="Search"/>
                            <Button @click="store.delayedSearch()"
                                    class="p-button-sm"
                                    data-testid="productstocks-actions-search-button"
                                    icon="pi pi-search"/>
                            <Button
                                v-if="!store.isMobile"
                                as="router-link"
                                :to="`/productstocks/filters`"
                                type="button"
                                size="small"
                                data-testid="productstocks-actions-show-filters"
                                @click="store.show_filters = true">
                                Filters
                                <Badge v-if="store.count_filters > 0" :value="store.count_filters"></Badge>
                            </Button>

                            <Button
                                type="button"
                                icon="pi pi-filter-slash"
                                data-testid="productstocks-actions-reset-filters"
                                class="p-button-sm"
                                label="Reset"
                                @click="store.resetQuery()" />

                </InputGroup>
            </div>

        </div>
        <!--/right-->

    </div>
    <!--/actions-->
</template>

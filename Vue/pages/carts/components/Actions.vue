<script  setup>
import {ref, reactive, watch, onMounted} from 'vue';
import {useRoute} from 'vue-router';

import { useCartStore } from '../../../stores/store-carts'

const store = useCartStore();
const route = useRoute();

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
                    data-testid="carts-actions-menu"
                    aria-haspopup="true"
                    aria-controls="overlay_menu">
                    <i class="pi pi-angle-down"></i>
                    <Badge v-if="store.action.items.length > 0"
                           :value="store.action.items.length" />
                </Button>
                <Menu ref="selected_menu_state"
                      :model="store.list_selected_menu"
                      :popup="true" />
                <!--/selected_menu-->
                <Button
                                    type="button"
                                    @click="toggleBulkMenuState"
                                    severity="danger" outlined
                                    data-testid="carts-actions-bulk-menu"
                                    aria-haspopup="true"
                                    aria-controls="bulk_menu_state"
                                    class="ml-1 p-button-sm">
                                    <i class="pi pi-ellipsis-v"></i>
                                </Button>
                                <Menu ref="bulk_menu_state"
                                      :model="store.list_bulk_menu"
                                      :popup="true" />

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
                                       data-testid="carts-actions-search"
                                       placeholder="Search"/>
                            <Button @click="store.delayedSearch()"
                                    class="p-button-sm"
                                    data-testid="carts-actions-search-button"
                                    icon="pi pi-search"/>
                            <Button
                                v-if="!store.isMobile"
                                as="router-link"
                                :to="`/carts/filters`"
                                type="button"
                                size="small"
                                :disabled="Object.keys(route.params).length"
                                data-testid="carts-actions-show-filters"
                                @click="store.show_filters = !store.show_filters">
                                Filters
                                <Badge v-if="store.count_filters > 0" :value="store.count_filters"></Badge>
                            </Button>

                            <Button
                                type="button"
                                icon="pi pi-filter-slash"
                                data-testid="carts-actions-reset-filters"
                                class="p-button-sm !gap-0"
                                label="Reset"
                                @click="store.resetQuery()" />

                                <!--bulk_menu-->
                                
                                <!--/bulk_menu-->

                        </InputGroup>
                    </div>

                </div>

            </div>
            <!--/right-->
</template>

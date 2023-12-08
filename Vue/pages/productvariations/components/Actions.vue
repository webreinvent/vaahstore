<script  setup>
import {ref, reactive, watch, onMounted} from 'vue';
import { useProductVariationStore } from '../../../stores/store-productvariations'

import Filters from './Filters.vue'

const store = useProductVariationStore();

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

const permission=store.assets.permission;
</script>

<template>
    <div>

        <!--actions-->
        <div :class="{'flex justify-content-between': store.isViewLarge()}" class="mt-2 mb-2">

            <!--left-->
            <div v-if="store.view === 'large'">

                <!--selected_menu-->
                <Button class="p-button-sm"
                    type="button"
                    @click="toggleSelectedMenuState"
                    data-testid="productvariations-actions-menu"
                    aria-haspopup="true"
                    aria-controls="overlay_menu"
                        :disabled="!store.assets.permission.includes('can-update-module')">
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
                    data-testid="productvariations-actions-bulk-menu"
                    aria-haspopup="true"
                    aria-controls="bulk_menu_state"
                    class="ml-1 p-button-sm"
                    :disabled="!store.assets.permission.includes('can-update-module')"
                >
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


                <div class="grid p-fluid">


                    <div class="col-12">
                        <div class="p-inputgroup ">

                            <InputText v-model="store.query.filter.q"
                                       @keyup.enter="store.delayedSearch()"
                                       class="p-inputtext-sm"
                                       @keyup.enter.native="store.delayedSearch()"
                                       @keyup.13="store.delayedSearch()"
                                       data-testid="productvariations-actions-search"
                                       placeholder="Search"/>
                            <Button @click="store.delayedSearch()"
                                    class="p-button-sm"
                                    data-testid="productvariations-actions-search-button"
                                    icon="pi pi-search"/>
                            <Button
                                type="button"
                                class="p-button-sm"
                                data-testid="productvariations-actions-show-filters"
                                @click="store.show_filters = true">
                                Filters
                                <Badge v-if="store.count_filters > 0" :value="store.count_filters"></Badge>
                            </Button>

                            <Button
                                type="button"
                                icon="pi pi-filter-slash"
                                data-testid="productvariations-actions-reset-filters"
                                class="p-button-sm"
                                label="Reset"
                                @click="store.resetQuery()"
                                :disabled="store.count_filters===0"
                            />

                        </div>
                    </div>


                    <Filters/>

                </div>

            </div>
            <!--/right-->

        </div>
        <!--/actions-->

    </div>
</template>

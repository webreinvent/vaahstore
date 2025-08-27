<script  setup>
import {ref, reactive, watch, onMounted} from 'vue';
import {useRoute} from 'vue-router';

import { usePaymentStore } from '../../../stores/store-payments'

const store = usePaymentStore();
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
                    data-testid="payments-actions-menu"
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
                    data-testid="payments-actions-bulk-menu"
                    aria-haspopup="true"
                    aria-controls="bulk_menu_state"
                    class="ml-1 p-button-sm">
                    <i class="pi pi-ellipsis-v"></i>
                </Button>
                <Menu ref="bulk_menu_state"
                        :model="store.list_bulk_menu"
                        :popup="true" />
                <!--/bulk_menu-->

            </div>
            <!--/left-->

            <!--right-->
            <div >


                <div class="">


                    <div class="">
                        <div class="flex gap-1 ">

                            <div class="w-1/2">
                            <InputText v-model="store.query.filter.q"
                                       @keyup.enter="store.delayedSearch()"
                                       class="p-inputtext-sm"
                                       @keyup.enter.native="store.delayedSearch()"
                                       @keyup.13="store.delayedSearch()"
                                       data-testid="payments-actions-search"
                                       placeholder="Search"/>
                                </div>
                            <Button @click="store.delayedSearch()"
                                    class="p-button-sm"
                                    data-testid="payments-actions-search-button"
                                    icon="pi pi-search"/>
                            <Button
                                as="router-link"
                                :to="`/payments/filters`"
                                type="button"
                                size="small"
                                data-testid="payments-actions-show-filters"
                            >
                                <span style="font-weight: var(--p-button-label-font-weight);" >Filters</span>
                                <Badge v-if="store.count_filters > 0" :value="store.count_filters"></Badge>
                            </Button>

                            <Button
                                type="button"

                                data-testid="payments-actions-reset-filters"
                                class="p-button-sm"
                                @click="store.resetQuery()" >Reset <Icon icon="mingcute:filter-line" width="14" height="14" style="color: #7b7a7a" /></Button>

                                <!--bulk_menu-->
                                <Button
                                    type="button"
                                    @click="toggleBulkMenuState"
                                    severity="danger" outlined
                                    data-testid="payments-actions-bulk-menu"
                                    aria-haspopup="true"
                                    aria-controls="bulk_menu_state"
                                    class="p-button-sm">
                                    <i class="pi pi-ellipsis-v"></i>
                                </Button>
                                <Menu ref="bulk_menu_state"
                                      :model="store.list_bulk_menu"
                                      :popup="true" />
                                <!--/bulk_menu-->

                        </div>
                    </div>

                </div>

            </div>
            <!--/right-->

        </div>
        <!--/actions-->

    </div>
</template>

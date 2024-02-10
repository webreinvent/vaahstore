<script  setup>
import {ref, reactive, watch, onMounted} from 'vue';
import { useUserStore } from '../../../stores/store-users'

import Filters from './Filters.vue'

const store = useUserStore();

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
        <div :class="{'flex justify-content-between': store.isViewLarge()}">

            <!--left-->
            <div v-if="store.view === 'large'">

                <!--selected_menu-->
                <Button class="p-button-sm"
                        aria-haspopup="true"
                        aria-controls="overlay_menu"
                        data-testid="users-action_menu"
                        @click="toggleSelectedMenuState"
                        >
                    <i class="pi pi-angle-down"></i>
                    <Badge v-if="store.action.items.length > 0"
                           :value="store.action.items.length"
                    />
                </Button>

                <Menu ref="selected_menu_state"
                      :model="store.list_selected_menu"
                      :popup="true"
                />
                <!--/selected_menu-->

                <!--bulk_menu-->
                <Button class="p-button-sm ml-1"
                        icon="pi pi-ellipsis-h"
                        aria-haspopup="true"
                        aria-controls="bulk_menu_state"
                        data-testid="users-action_bulk_menu"
                        @click="toggleBulkMenuState"
                        />

                <Menu ref="bulk_menu_state"
                      :model="store.list_bulk_menu"
                      :popup="true"
                />
                <!--/bulk_menu-->
            </div>
            <!--/left-->

            <!--right-->
            <div>
                <div class="grid p-fluid">
                    <div class="col-12">
                        <div class="p-inputgroup ">
                            <InputText class="p-inputtext-sm"
                                       type="text"
                                       v-model="store.query.filter.q"
                                       @keyup.enter="store.delayedSearch()"
                                       @keyup.enter.native="store.delayedSearch()"
                                       @keyup.13="store.delayedSearch()"
                                       placeholder="Search"
                                       data-testid="users-action_search_input"
                            />

                            <Button class="p-button-sm"
                                    icon="pi pi-search"
                                    data-testid="users-action_search"
                                    @click="store.delayedSearch()"
                            />

                            <Button class="p-button-sm"
                                    label="Filters"
                                    data-testid="users-action_filter"
                                    @click="store.show_filters = true"
                            >

                                <Badge v-if="store.count_filters > 0"
                                       :value="store.count_filters"
                                />
                            </Button>

                            <Button class="p-button-sm"
                                    label="Reset"
                                    icon="pi pi-filter-slash"
                                    data-testid="users-action_reset"
                                    @click="store.resetQuery()"
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

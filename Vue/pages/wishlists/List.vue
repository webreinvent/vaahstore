<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useWishlistStore} from '../../stores/store-wishlists'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";

const store = useWishlistStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Wishlists - Store';
    /**
     * call onLoad action when List view loads
     */
    await store.onLoad(route);

    /**
     * watch routes to update view, column width
     * and get new item when routes get changed
     */
    await store.watchRoutes(route);

    /**
     * watch states like `query.filter` to
     * call specific actions if a state gets
     * changed
     */
    await store.watchStates();
    await store.watchShowFilter();

    /**
     * fetch assets required for the crud
     * operation
     */
    await store.getAssets();


    /**
     * fetch list of records
     */
    await store.getList();

    await store.getListCreateMenu();

});

//--------form_menu
const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
//--------/form_menu


</script>
<template>

    <div class="w-full m-1" v-if="store.assets">

        <div class="lg:flex lg:space-x-4 items-start">
            <div v-if="store.getLeftColumnClasses"
                 :class="store.getLeftColumnClasses">
                <Panel class="is-small">

                    <template class="p-1" #header>

                        <div class="flex flex-row">
                            <div >
                                <b class="mr-1">Wishlists</b>
                                <Badge v-if="store.list && store.list.total > 0"
                                       :value="store.list.total">
                                </Badge>
                            </div>

                        </div>

                    </template>

                    <template #icons>

                        <div class="p-inputgroup">

                            <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                                    data-testid="wishlists-list-create"
                                    class="p-button-sm"
                                    @click="store.toForm()">
                                <i class="pi pi-plus mr-1"></i>
                                Create
                            </Button>

                            <Button data-testid="wishlists-list-reload"
                                    class="p-button-sm"
                                    @click="store.getList()">
                                <i class="pi pi-refresh mr-1"></i>
                            </Button>

                            <!--form_menu-->

                            <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                                    type="button"
                                    :disabled="!store.assets.permissions.includes('can-update-module')"
                                    @click="toggleCreateMenu"
                                    class="p-button-sm"
                                    data-testid="wishlists-create-menu"
                                    icon="pi pi-angle-down"
                                    aria-haspopup="true"/>

                            <Menu ref="create_menu"
                                  :model="store.list_create_menu"
                                  :popup="true" />

                            <!--/form_menu-->

                        </div>

                    </template>

                    <Actions/>

                    <Table/>

                </Panel>
            </div>

            <div v-if="store.getRightColumnClasses"
                 :class="store.getRightColumnClasses">

                <RouterView/>

            </div>
        </div>

    </div>


</template>

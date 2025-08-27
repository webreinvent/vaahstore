<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useCartStore} from '../../stores/store-carts'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './Filters.vue'

const store = useCartStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();

function handleScreenResize() {
    store.setScreenSize();
}



onMounted(async () => {
    document.title = 'Carts - Store';
    store.item = null;

    window.addEventListener('resize', handleScreenResize);
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

    /**
     * fetch assets required for the crud
     * operation
     */
    await store.getAssets();

    /**
     * fetch list of records
     */
    root.initWatchStoreChange(route, store, async () => {
        await store.getList();
    });


});

//--------form_menu
const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
//--------/form_menu


</script>
<template>

    <div class="w-full" v-if="store.assets">

        <div class="lg:flex lg:space-x-4 items-start">
            <div v-if="store.getLeftColumnClasses"
                 :class="store.getLeftColumnClasses"

                 class="mb-4 lg:mb-0">

                <Panel class="!p-0">
                    <template #header>

                        <div class="w-full">
                            <div class="flex items-center gap-2">
                                <Icon icon="solar:cart-large-minimalistic-linear" width="20" height="20" class="text-gray-950"></Icon>

                                <h1 class="text-gray-950 font-medium text-base">Carts</h1>
                                <p class="font-bold bg-[#4f46e51a] py-[2px] px-2 text-[#4f46e5] rounded-md text-[10px]"  v-if="store.list && store.list.total > 0"
                                >{{store.list.total}}
                                </p>
                            </div>

                            <div class="h-[1px] bg-gray-200 w-full mt-3 mb-2"/>
                        </div>

                    </template>
                <Card>
                    <template #content>
                        <Actions/>
                        <Table/>
                    </template>
                </Card>


            </Panel>
        </div>


        <div v-if="store.getRightColumnClasses"
             :class="store.getRightColumnClasses">

            <RouterView/>

        </div>
        <!--/right-->



    </div>
    </div>



</template>

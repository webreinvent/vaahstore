<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRoute } from 'vue-router';

import { useProductVendorStore } from '@/stores/store-productvendors'
import { useRootStore } from '@/stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";

const store = useProductVendorStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();

function handleScreenResize() {
    store.setScreenSize();
}
onMounted(async () => {
    document.title = 'Vendor Products - Store';
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

    <div class="w-full" v-if="store.assets">

        <div class="lg:flex lg:space-x-4 items-start">
            <!--left-->
            <div v-if="store.getLeftColumnClasses" :class="store.getLeftColumnClasses" class="mb-4 lg:mb-0">

                <Panel>
                    <template #header>
                        <div class="flex flex-row items-center gap-2">
                            <Icon icon="icon-park-outline:people-top-card" width="24" height="24" class="text-gray-700"></Icon>
                            <b class="mr-1">Vendor Products</b>
                            <p class="font-bold bg-[#4f46e51a] py-[2px] px-2 text-[#4f46e5] rounded-md text-[10px]"  v-if="store.list && store.list.total > 0">
                                {{store.list.total}}
                            </p>
                            </div>
                    </template>

                    <template #icons>

                        <div class="flex gap-1">

                            <Button data-testid="productvendors-list-create" size="small"
                                :disabled="!store.assets.permissions.includes('can-update-module')"
                                @click="store.toForm()">
                                <i class="pi pi-plus"></i>
                                Create
                            </Button>

                            <Button data-testid="productvendors-list-reload" size="small" @click="store.reload()">
                                <i class="pi pi-refresh "></i>
                            </Button>

                            <!--form_menu-->

                            <Button v-if="root.assets && root.assets.module
                                && root.assets.module.is_dev" type="button" @click="toggleCreateMenu"
                                :disabled="!store.assets.permissions.includes('can-update-module')" size="small"
                                data-testid="productvendors-create-menu" icon="pi pi-angle-down" aria-haspopup="true" />

                            <Menu ref="create_menu" :model="store.list_create_menu" :popup="true" />

                            <!--/form_menu-->

                        </div>

                    </template>

                    <div class="h-[1px] bg-gray-200 w-full mt-3 mb-2"></div>

                    <Card>
                        <template #content>
                            <div class="-mt-2">
                            <Actions />

                            <Table />
                        </div>
                        </template>
                    </Card>
                </Panel>
            </div>

            <div v-if="store.getRightColumnClasses" :class="store.getRightColumnClasses">

                <RouterView />

            </div>

        </div>
    </div>


</template>

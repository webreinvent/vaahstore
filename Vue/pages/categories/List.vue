<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useCategoryStore} from '../../stores/store-categories'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";

const store = useCategoryStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();

function handleScreenResize() {
    store.setScreenSize();
}
onMounted(async () => {
    document.title = 'Categories - Store';
    window.addEventListener('resize', handleScreenResize);
    store.item = null;
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

    <div class="w-full" v-if="store.assets">

        <div class="lg:flex lg:space-x-4 items-start">
            <!--left-->
            <div v-if="store.getLeftColumnClasses"
                 :class="store.getLeftColumnClasses"

                 class="mb-4 lg:mb-0">
                <Panel :pt="root.panel_pt">
                    <template #header>

                    <div class="flex flex-row">

                        <div class="flex items-center gap-2">
                            <div>
                                <Icon icon="bx:basket" width="18" height="18"  style="color: #111113" />
                            </div>
                            <div class="flex items-center mt-1">
                                <b class="mr-1">Categories</b>
                                <p class="font-bold bg-[#4f46e51a] py-[2px] px-2 text-[#4f46e5] rounded-md text-[10px]"  v-if="store.list && store.list.total > 0"
                                       >{{store.list.total}}
                                </p>
                            </div>
                        </div>

                    </div>

                </template>

                <template #icons>

<!--                    <InputGroup>-->
                    <div class=" gap-2 flex">

                    <Button data-testid="categories-list-create"
                            size="small"
                            @click="store.toForm()">
                        <Icon class="-mr-1" icon="ph:plus-light" width="16" height="16"  style="color: #7b7a7a" />
                        Create
                    </Button>

                    <Button data-testid="categories-list-reload"
                            size="small"
                            @click="store.reload()">
                        <Icon class="mx-1"  icon="famicons:reload-sharp" width="16" height="16"  style="color: #7b7a7a" />
                    </Button>

                    <!--form_menu-->

                    <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                        type="button"
                        @click="toggleCreateMenu"
                            size="small"
                        data-testid="categories-create-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="create_menu"
                          :model="store.list_create_menu"
                          :popup="true" />
                    </div>

                    <!--/form_menu-->

<!--                    </InputGroup>-->

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

    </div>
    </div>


</template>

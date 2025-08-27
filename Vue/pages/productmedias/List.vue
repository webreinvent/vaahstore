<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useProductMediaStore} from '../../stores/store-productmedias'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";

const store = useProductMediaStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();


onMounted(async () => {
    document.title = 'ProductMedias - Store';
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

    <div class="w-full m-1" v-if="store.assets">

        <div class="lg:flex lg:space-x-4 items-start">
            <div v-if="store.getLeftColumnClasses"
                 :class="store.getLeftColumnClasses">
                <Panel class="is-small">

                    <template class="p-1" #header>

                        <div class="flex flex-row">
                            <div class="flex items-center gap-2">
                                <div>
                                    <Icon icon="bx:basket" width="18" height="18"  style="color: #111113" />
                                </div>
                                <div class="flex items-center mt-1">
                                    <b class="mr-1">Product Medias</b>
                                    <p class="font-bold bg-[#4f46e51a] py-[2px] px-2 text-[#4f46e5] rounded-md text-[10px]"  v-if="store.list && store.list.total > 0"
                                    >{{store.list.total}}
                                    </p>
                                </div>
                            </div>

                        </div>

                    </template>

                    <template #icons>

<!--                        <div class="p-inputgroup">-->

<!--                            <Button data-testid="productmedias-list-create"-->
<!--                                    class="p-button-sm"-->
<!--                                    :disabled="!store.assets.permissions.includes('can-update-module')"-->
<!--                                    @click="store.toForm()">-->
<!--                                <i class="pi pi-plus mr-1"></i>-->
<!--                                Create-->
<!--                            </Button>-->

<!--                            <Button data-testid="productmedias-list-reload"-->
<!--                                    class="p-button-sm"-->
<!--                                    @click="store.reload()">-->
<!--                                <i class="pi pi-refresh mr-1"></i>-->
<!--                            </Button>-->

<!--                            &lt;!&ndash;form_menu&ndash;&gt;-->

<!--                            <Button v-if="root.assets && root.assets.module-->
<!--                                                && root.assets.module.is_dev"-->
<!--                                    type="button"-->
<!--                                    @click="toggleCreateMenu"-->
<!--                                    class="p-button-sm"-->
<!--                                    :disabled="!store.assets.permissions.includes('can-update-module')"-->
<!--                                    data-testid="productmedias-create-menu"-->
<!--                                    icon="pi pi-angle-down"-->
<!--                                    aria-haspopup="true"/>-->

<!--                            <Menu ref="create_menu"-->
<!--                                  :model="store.list_create_menu"-->
<!--                                  :popup="true" />-->

<!--                            &lt;!&ndash;/form_menu&ndash;&gt;-->

<!--                        </div>-->

                        <div class=" gap-2 flex">
                            <Button data-testid="productmedias-list-create"
                                    size="small"
                                    :disabled="!store.assets.permissions.includes('can-update-module')"
                                    @click="store.toForm()">
                                <Icon class="-mr-1" icon="ph:plus-light" width="16" height="16"  style="color: #7b7a7a" />
                                Create
                            </Button>

                            <Button data-testid="productmedias-list-reload"
                                    size="small"
                                    @click="store.reload()">
                                <Icon class="mx-1"  icon="famicons:reload-sharp" width="16" height="16"  style="color: #7b7a7a" />
                            </Button>

                            <!--form_menu-->

                            <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                                    type="button"
                                    @click="toggleCreateMenu"
                                    class="p-button-sm"
                                    :disabled="!store.assets.permissions.includes('can-update-module')"
                                    data-testid="productmedias-create-menu"
                                    icon="pi pi-angle-down"
                                    aria-haspopup="true"/>

                            <Menu ref="create_menu"
                                  :model="store.list_create_menu"
                                  :popup="true" />
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
        </div>

    </div>


</template>

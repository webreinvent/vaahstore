<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useProductVariationStore} from '../../stores/store-productvariations'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";

const store = useProductVariationStore();
const product_store = useProductStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
import {useProductStore} from '../../stores/store-products'
const confirm = useConfirm();


function handleScreenResize() {
    store.setScreenSize();
}


onMounted(async () => {
    document.title = 'Product Variations - Store ';

    window.addEventListener('resize', handleScreenResize);
    /**
     *
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

const permissions=store.assets ? store.assets.permissions : 0;

</script>
<template>
<!--    <Message v-show="store.show_cart_msg" icon="pi pi-shopping-cart" severity="success" :closable="false" :sticky="true" :life="1000">-->
<!--        ( {{store.total_cart_product}} )-->
<!--        {{store.active_cart_user_name}} <Button @click="store.viewCart(store.cart_id)" class="line-height-1" label="View Cart" link />-->
<!--            <Button @click="store.disableActiveCart()">X</Button>-->
<!--    </Message>-->
    <Message v-show="store.show_cart_msg" icon="pi pi-shopping-cart" severity="success" :sticky="true" :life="1000" @close="store.disableActiveCart()">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                 {{ store.active_cart_user_name }} ( {{ store.total_cart_product }} )
            </div>
            <div>
                <Button @click="store.viewCart(store.cart_id)" class="line-height-1 mr-2" label="View Cart" link />
<!--                <Button @click="store.disableActiveCart()">X</Button>-->
            </div>
        </div>
    </Message>



    <div class="w-full" v-if="store.assets">

        <div class="lg:flex lg:space-x-4 items-start">

            <div v-if="store.getLeftColumnClasses"
                 :class="store.getLeftColumnClasses"

                 class="mb-4 lg:mb-0">

                <Panel :pt="root.panel_pt">
                    <template #header>

                        <div class="flex flex-row">
                            <div>
                                <b class="mr-1">Products Variations</b>
                                <Badge v-if="store.list && store.list.total > 0"
                                       :value="store.list.total">
                                </Badge>
                            </div>

                        </div>

                    </template>

                <template #icons>

                    <div class="p-inputgroup">

                    <Button data-testid="productvariations-list-create"
                            class="p-button-sm"
                            @click="store.toForm()"
                            :disabled="!store.assets.permissions.includes('can-update-module')">
                        <i class="pi pi-plus mr-1"></i>
                        Create
                    </Button>

                    <Button data-testid="productvariations-list-reload"
                            class="p-button-sm"
                            @click="store.reloadPage()">
                        <i class="pi pi-refresh mr-1"></i>
                    </Button>

                    <!--form_menu-->

                    <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                        type="button"
                        @click="toggleCreateMenu"
                        class="p-button-sm"
                        data-testid="productvariations-create-menu"
                        icon="pi pi-angle-down"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
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
            <!--/right-->



        </div>
    </div>


</template>

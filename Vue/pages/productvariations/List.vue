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


onMounted(async () => {
    document.title = 'Product Variations - Store ';
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
<!--        ( {{product_store.total_cart_product}} )-->
<!--        {{store.active_cart_user_name}} <Button class="line-height-1" label="View Cart" link />-->
<!--        <Button @click="product_store.disableActiveCart()">X</Button>-->
<!--    </Message>-->
    <div class="grid" v-if="store.assets">

        <div :class="'col-'+store.list_view_width">
            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div >
                            <b class="mr-1">Product Variations</b>
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

        <RouterView/>

    </div>


</template>

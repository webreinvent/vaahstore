<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useVendorStore} from '../../stores/store-vendors'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Charts from "../../components/Charts.vue";
const store = useVendorStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Vendors - Store';
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

    <div class="grid" v-if="store.assets">

        <div :class="'col-'+store.list_view_width">
            <Accordion>
            <AccordionTab header="Stats">
                <Card class="max-w-max border-round-xl shadow-md">
                    <template #title>
                        <div class="flex align-items-center justify-content-between">
                            <h2 class="text-lg">Top Vendors By Sales</h2>

                        </div>
                    </template>

                    <template #content>
                        <DataTable
                            :value="store.top_selling_vendors"
                            dataKey="id"

                            class="p-datatable-sm p-datatable-hoverable-rows"
                            :nullSortOrder="-1"
                            v-model:selection="store.action.items"
                            stripedRows
                            responsiveLayout="scroll"
                        >
                            <Column field="variation_name" header="" class="overflow-wrap-anywhere" >
                                <template #body="prop">
                                    <div class="flex ">

                                        <div class="product_desc ml-3" >
                                            <h4>{{ prop.data.name }}</h4>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column field="total_sales" header="" class="overflow-wrap-anywhere" >
                                <template #body="prop">
                                    <div class="flex ">

                                        <div class="product_desc ml-3" >
                                            <p><b> {{ prop.data.total_sales }}</b> Items</p>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <template #empty>
                                <div class="text-center py-3">
                                    No records found.
                                </div>
                            </template>
                        </DataTable>
                    </template>
                </Card>
            </AccordionTab>
            </Accordion>
            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div >
                            <b class="mr-1">Vendors</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total">
                            </Badge>
                        </div>

                    </div>

                </template>

                <template #icons>

                    <div class="p-inputgroup">

                        <Button data-testid="vendors-list-create"
                                class="p-button-sm"
                                :disabled="!store.assets.permissions.includes('can-update-module')"
                                @click="store.toForm()">
                            <i class="pi pi-plus mr-1"></i>
                            Create
                        </Button>

                        <Button data-testid="vendors-list-reload"
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
                                data-testid="vendors-create-menu"
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

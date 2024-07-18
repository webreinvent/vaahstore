<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useShipmentStore } from '../../stores/store-shipments'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useShipmentStore();
const route = useRoute();

onMounted(async () => {

    /**
     * If record id is not set in url then
     * redirect user to list view
     */
    if(route.params && !route.params.id)
    {
        store.toList();
        return false;
    }

    /**
     * Fetch the record from the database
     */
    if(!store.item || Object.keys(store.item).length < 1)
    {
        await store.getItem(route.params.id);
    }
    store.getDomainFilterMenu();
});

//--------toggle item menu
const item_menu_state = ref();
const toggleItemMenu = (event) => {
    item_menu_state.value.toggle(event);
};
//--------/toggle item menu
const selected_shipping_status = ref();
const toggleQuickFilterState = (event) => {
    selected_shipping_status.value.toggle(event);
};
</script>
<template>

    <div class="col-6" >

        <Panel class="is-small" v-if="store && store.item">

            <template class="p-1" #header>

                <div class="p-panel-title w-7 white-space-nowrap
                overflow-hidden text-overflow-ellipsis">
                    #{{store.item.id}}
                </div>

            </template>

            <template #icons>


                <div class="p-inputgroup">

                    <Button label="Edit"
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            data-testid="shipments-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="shipments-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="shipments-item-to-list"
                            @click="store.toList()"/>

                </div>



            </template>


            <div class="mt-2" v-if="store.item">

                <Message severity="error"
                         class="p-container-message"
                         :closable="false"
                         icon="pi pi-trash"
                         v-if="store.item.deleted_at">

                    <div class="flex align-items-center justify-content-between">

                        <div class="">
                            Deleted {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="shipments-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <TabView >
                    <TabPanel header="Shipment Details">
                        <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                            <table class="p-datatable-table overflow-wrap-anywhere">
                                <tbody class="p-datatable-tbody">
                                <template v-for="(value, column) in store.item ">

                                    <template v-if="column === 'created_by' || column === 'updated_by'|| column === 'slug'
                        || column === 'deleted_by'">
                                    </template>

                                    <template v-else-if="column === 'id' || column === 'uuid'">
                                        <VhViewRow :label="column"
                                                   :value="value"
                                                   :can_copy="true"
                                        />
                                    </template>

                                    <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'
                        || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                                        <VhViewRow :label="column"
                                                   :value="value"
                                                   type="user"
                                        />
                                    </template>

                                    <template v-else-if="column === 'is_active'">
                                        <tr>
                                            <td><b>Shipment  Tracking Id</b></td>
                                            <td  colspan="2" >
                                                <span class="word-overflow" >9623-41ce-b4fc
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Shipment  Status</b></td>
                                            <td  colspan="2" >
                                        <Badge class="word-overflow" severity="success" value="Delivered">
                                                </Badge>
                                            </td>
                                        </tr>
                                    </template>

                                    <template v-else>
                                        <VhViewRow :label="column"
                                                   :value="value"
                                        />
                                    </template>


                                </template>
                                </tbody>

                            </table>

                        </div>
                    </TabPanel>
                    <TabPanel header="Shipment Items Detail">




                        <DataTable
                            :value="store.order_list1"
                            rowGroupMode="subheader"
                            :groupRowsBy="'order.name'"
                            sortMode="single"
                            :sortField="'order.name'"
                            :sortOrder="1"
                            scrollable
                            scrollHeight="400px"
                        >

                            <Column field="name" header="Order Item">

                            </Column>
                            <Column field="quantity" header="Quantity">

                            </Column>
                            <Column field="shipped" header="Shipped">

                            </Column>
                            <Column field="pending" header="Pending">

                            </Column>
                            <template #groupheader="prop">
                                <div class="flex items-center gap-2">

                                    <span><b>{{ prop.data.order.name }}</b></span>
                                </div>
                            </template>


                        </DataTable>
                    </TabPanel>
                </TabView>


            </div>
        </Panel>

    </div>

</template>

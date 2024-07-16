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
                                        <Badge class="word-overflow" severity="success" value="Out For Delivery">
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
                    <TabPanel header="Shipment Orders Detail">
<!--                        <DataTable  :value="store.order_list" style="border: 1px solid #ccc;margin-top:20px;"-->
<!--                                    :rows="20"-->
<!--                                    :paginator="true"-->
<!--                                    class="p-datatable-sm p-datatable-hoverable-rows">-->
<!--                            <Column field="id" header="Order ID" :style="{width: '80px'}" :sortable="true" style="border: 1px solid #ccc;">-->
<!--                            </Column>-->
<!--                            <Column field="name" header="Order Name" style="border: 1px solid #ccc;">-->
<!--                                <template #body="props">-->
<!--                                    <div  >-->
<!--                                        {{props.data.name}}-->

<!--                                    </div>-->
<!--                                </template>-->
<!--                            </Column>-->

<!--                                        <Column  header="Shipment Order Items" style="border: 1px solid #ccc;" :sortable="false">-->
<!--                                            <template #body="prop">-->
<!--                                                <div class="p-inputgroup justify-content-center">-->
<!--                                        <span class="p-inputgroup-addon cursor-pointer"-->
<!--                                              v-tooltip.top="'Track Order Shipment'"-->

<!--                                        >-->
<!--                                            <b > 2</b>-->
<!--                                        </span>-->
<!--                                                </div>-->
<!--                                            </template>-->
<!--                                        </Column>-->




<!--                            <column field="Action" header="Track Order Shipment" style="border:1px solid #ccc;">-->
<!--                                <template #body="props">-->

<!--                                    <div class="justify-content-center flex">-->
<!--                                    <Button class="p-button-tiny p-button-text"-->
<!--                                            data-testid="shipments-table-to-view"-->
<!--                                            v-tooltip.top="'View'"-->
<!--                                            @click="store.openOrdersPanel(props.data)"-->
<!--                                            icon="pi pi-slack" />-->
<!--                                    </div>-->
<!--                                </template>-->
<!--                            </column>-->
<!--                            <template #empty="prop">-->

<!--                                <div  style="text-align: center;font-size: 12px; color: #888;">No records found.</div>-->

<!--                            </template>-->
<!--                        </DataTable>-->

                        <DataTable :value="store.order_list_table_with_vendor"
                                   dataKey="id"
                                   :rows="10"
                                   :paginator="true"
                                   class="p-datatable-sm p-datatable-hoverable-rows"
                                   :nullSortOrder="-1"
                                   showGridlines
                                   v-model:selection="store.action.items"
                                   responsiveLayout="scroll">



                            <Column field="id" header="Order ID"  >
                            </Column>

                            <Column  header="Item Name"
                                     class="overflow-wrap-anywhere"
                            >
                                <template #body="prop">
                                    {{prop.data.name}}
                                </template>
                            </Column>

                            <Column  header="Vendor Name"
                                     class="overflow-wrap-anywhere"
                            >
                                <template #body="prop">
                                    {{prop.data.vendor_name}}
                                </template>
                            </Column>
                            <Column  header="Quantity "
                                     class="overflow-wrap-anywhere"
                            >
                                <template #body="prop">
                                    {{prop.data.available_quantity}}
                                </template>
                            </Column>
                            <Column  header="Status"
                                     class="overflow-wrap-anywhere"
                            >
                                <template #body="prop">

<!--                                    <Badge  v-if="prop.data.status === 'Out For Delivery'" severity="success">-->
<!--                                        {{ prop.data.status }}-->
<!--                                    </Badge>-->

<!--                                    <Badge v-else severity="warn">-->
<!--                                        {{ prop.data.status }}-->
<!--                                    </Badge>-->

                                    <Button
                                        data-testid="crawledpagedata-domain-filter"
                                        type="button"
                                        @click="toggleQuickFilterState"
                                        aria-haspopup="true"
                                        aria-controls="overlay_menu"
                                        class="ml-1 p-button-sm"
                                        :label=" prop.data.status"
                                        icon="pi pi-angle-down"
                                    >
                                    </Button>
                                    <Menu ref="selected_shipping_status"
                                          :model="store.shipping_status_menu"
                                          :popup="true"/>
                                </template>
                            </Column>
                            <template #empty>
                                <div class="text-center py-3">
                                    No records found.
                                </div>
                            </template>

                        </DataTable>
                    </TabPanel>
                </TabView>


            </div>
        </Panel>

    </div>

</template>

<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useShipmentStore } from '../../stores/store-shipments'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
import {vaah} from "../../vaahvue/pinia/vaah";
import {useVendorStore} from "../../stores/store-vendors";
const store = useShipmentStore();
const vendorStore = useVendorStore();
const route = useRoute();
const useVaah = vaah();
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
const openVendorPage = (id) => {
    window.open(vendorUrl, '_blank');
};

</script>
<template>

    <div>

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

                <Tabs >
                    <TabList>
                        <Tab value="0">Shipment Details</Tab>
                        <Tab value="1">Shipment Items Detail</Tab>

                    </TabList>

                    <TabPanels>
                    <TabPanel value="0">
                        <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                            <table class="p-datatable-table overflow-wrap-anywhere">
                                <tbody class="p-datatable-tbody">
                                <template v-for="(value, column) in store.item ">

                                    <template v-if="column === 'created_by' || column === 'updated_by'|| column === 'slug'|| column === 'tracking_key'|| column === 'tracking_value'|| column === 'tracking_url'
                        || column === 'deleted_by'|| column === 'orders'|| column === 'shipment_order_items'|| column === 'taxonomy_id_shipment_status'
                        ||  column=== 'status' ||  column=== 'total_shipment'||  column=== 'is_items_exist_already'">
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

                                    <template v-else-if="column === 'is_trackable'">

                                        <tr>
                                            <td><b>Tracking Url</b></td>
                                            <td  colspan="2" >
                                                <a :href="store.item.tracking_url" target="_blank" class="word-overflow">
                                                    {{ store.item.tracking_url }}
                                                </a>

                                            </td>
                                        </tr>

                                        <tr>
                                            <td><b>Tracking Key</b></td>
                                            <td  colspan="2" >
                                                <span class="word-overflow" >{{ store.item.tracking_key }}

                                                </span>
                                            </td>
                                        </tr>


                                        <tr>
                                            <td><b>Tracking Value</b></td>
                                            <td  colspan="2" >
                                                <Button  @click="useVaah.copy(store.item.tracking_value)"
                                                        >
                                        {{store.item.tracking_value}}
                                    </Button>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Shipment  Status</b></td>
                                            <td  colspan="2" >
                                                <Badge v-if="store.item.status && store.item.status.name == 'Delivered'"
                                                       severity="success"> {{store.item.status.name}} </Badge>

                                                <Badge v-else
                                                       severity="warn"> {{store.item.status?.name}}</Badge>

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
                    <TabPanel value="1">




                        <DataTable
                            :value="store.item.shipment_order_items"
                            rowGroupMode="subheader"
                            :groupRowsBy="'order.id'"
                            sortMode="single"
                            :sortField="'order.id'"
                            :sortOrder="1"
                            scrollable
                            scrollHeight="700px"
                        >

                            <Column field="name" header="Order Item">
                                <template #body="prop">
                                    <div class="">
                                    {{prop.data.product_variation?.name}}
                                    </div>
                                </template>
                            </Column>
                            <Column  header="Vendor">
                                <template #body="prop">
                                    <div class="">
                                        <span style="color: #1d4ed8;  cursor: pointer;" @click="store.redirectToVendor(prop.data)">
                                        <b>{{prop.data.vendor?.name}}</b>
                                    </span>
                                    </div>
                                </template>
                            </Column>
                            <Column field="quantity" header="Quantity">

                            </Column>
                            <Column field="shipped" header="Shipped">
                                <template #body="prop">
                                   {{prop.data.pivot.quantity}}

                                </template>
                            </Column>
                            <Column field="pending" header=" Pending">
                                    <template #body="prop">
                                    {{prop.data.pivot.pending}}
                                </template>
                            </Column>
                            <template #groupheader="prop">
                                <div class="flex items-center gap-2">
                                    <span style="color: #1d4ed8; text-decoration: underline; cursor: pointer;"  @click="store.toOrderDetails(prop.data.order.id)">
                                        <b>{{ prop.data.order.user.display_name }}</b>
                                    </span>
                                </div>
                            </template>


                        </DataTable>



                    </TabPanel>
                    </TabPanels>
                </Tabs>


            </div>
        </Panel>

    </div>

</template>

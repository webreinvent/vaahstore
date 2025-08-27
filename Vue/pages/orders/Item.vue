<script setup>
import {onMounted, ref, watch, computed} from "vue";
import {useRoute} from 'vue-router';

import {useOrderStore} from '../../stores/store-orders'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';

const store = useOrderStore();
const route = useRoute();

onMounted(async () => {

    /**
     * If record id is not set in url then
     * redirect user to list view
     */
    store.assets_is_fetching = true;
    store.getAssets();
    if (route.params && !route.params.id) {
        store.toList();
        return false;
    }

    /**
     * Fetch the record from the database
     */
    if (route.params && route.params.id) {
        await store.getItem(route.params.id);
    }

    /**
     * Watch if url record id is changed, if changed
     * then fetch the new records from database
     */
    /*watch(route, async (newVal,oldVal) =>
        {
            if(newVal.params && !newVal.params.id
                && newVal.name === 'articles.view')
            {
                store.toList();

            }
            await store.getItem(route.params.id);
        }, { deep: true }
    )*/

});

//--------toggle item menu
const item_menu_state = ref();
const toggleItemMenu = (event) => {
    item_menu_state.value.toggle(event);
};
//--------/toggle item menu

const onTabClick = async (key) => {
    if (key) {
        await store.getItem(store.item.id, key);
    }
};
</script>
<template>

    <div>

        <Panel class="is-small" v-if="store && store.item">

            <template class="p-1" #header>

                <div class="flex flex-row">

                    <div class="p-panel-title">
                        #{{store.item.id}}
                    </div>

                </div>

            </template>

            <template #icons>


                <div class="p-inputgroup">

                    <Button label="Edit"
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            data-testid="orders-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="orders-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true"/>
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="orders-item-to-list"
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
                            Trashed {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="orders-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>
                <Tabs value="0">

                    <TabList>
                        <Tab value="0">Order Details </Tab>
                        <Tab @click="() =>onTabClick('payments')" value="1">Transaction History ({{
                            store.item?.payments_count }})
                        </Tab>
                        <Tab @click="() =>onTabClick('shipments')" value="2">Shipments History ({{
                            store.item?.shipments_count }})
                        </Tab>
                        <div class="ml-auto mr-4"></div>
                        <Button
                            v-tooltip.top="'Download Invoice'"

                            class="p-button-tiny p-button-text mr-4"
                            data-testid="orders-table-to-view"
                            :pt="{ root: { class: '!bg-none !p-0' } }"
                            @click="store.downloadInvoice(store.item)"
                        >
                            <Icon
                                icon="tabler:file-download"
                                width="30"
                                height="25"
                                class="text-green-400"
                            />
                        </Button>

                    </TabList>

                    <TabPanels>
                        <TabPanel value="0">
                            <Message severity="info" :closable="false" v-if="store.item.status_notes">
                                <tr>
                                    <td colspan="2">
                                        <div style="width:300px;word-break: break-word;">
                                            {{store.item.status_notes}}
                                        </div>
                                    </td>
                                </tr>
                            </Message>
                            <div
                                class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                                <table class="p-datatable-table">
                                    <tbody class="p-datatable-tbody">
                                    <template v-for="(value, column) in store.item ">

                                        <template v-if="column === 'created_by' || column === 'delivery_fee'||
                                    column === 'taxes' || column === 'updated_by'||
                                    column === 'order_payment_status'|| column === 'paid' ||
                                    column === 'discount'|| column === 'taxonomy_id_payment_status'||
                                    column === 'payable' || column === 'user'|| column === 'payment_method'||
                                    column === 'status'|| column === 'status_order'|| column === 'amount'||column === 'items_count'||
                                    column === 'is_active_order_item' || column == 'is_invoice_available'
                                    || column == 'meta' || column == 'deleted_by' || column == 'status_notes'
                                     || column === 'order_shipment_status' ||column === 'currency' || column === 'payments'|| column === 'shipments'|| column === 'items'
                                     || column === 'billing_address'|| column === 'shipping_address'
                                     || column === 'vh_st_payment_method_id'|| column === 'payments_count'|| column === 'shipments_count'
                                     || column === 'total_quantity'|| column === 'vh_st_store_id'|| column === 'total_shipped_quantity'|| column === 'shipped_items_count'|| column === 'total_order_items'">
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


                                        <template v-else-if="column === 'order_status'">
                                            <tr>
                                                <td><b>Order Status</b></td>
                                                <td colspan="2">
                                                    <Badge class="word-overflow"
                                                           :severity="store.item.order_status === 'Completed' ? 'success' : ''">
                                                        {{store.item.order_status}}
                                                    </Badge>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Payment Status</b></td>
                                                <td colspan="2">
                                                    <Badge v-if="store.item.order_payment_status?.slug === 'paid'"
                                                           severity="success">
                                                        {{store.item.order_payment_status?.name}}
                                                    </Badge>
                                                    <Badge
                                                        v-else-if="store.item.order_payment_status?.slug === 'partially-paid'"
                                                        severity="info">
                                                        {{store.item.order_payment_status?.name}}
                                                    </Badge>
                                                    <Badge v-else severity="danger">
                                                        {{store.item.order_payment_status?.name}}
                                                    </Badge>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Shipping Status</b></td>
                                                <td colspan="2">
                                                    <Badge class="word-overflow"
                                                           :severity="store.item.order_shipment_status === 'Delivered' ? 'success' : 'warn'">
                                                        {{store.item.order_shipment_status}}
                                                    </Badge>
                                                </td>
                                            </tr>


                                        </template>


                                        <template v-else-if="column === 'is_paid'">
                                            <VhViewRow :label="column"
                                                       :value="value"
                                                       type="yes-no"
                                            />
                                        </template>

                                        <template v-else-if="column === 'vh_user_id'">
                                            <VhViewRow label="Order Name"
                                                       :value="store.item.user?.first_name"
                                            />

                                            <tr>
                                                <td><b>Amount</b></td>
                                                <td colspan="2">
                                        <span class="word-overflow">
                                           <span v-html="store.item?.currency?.symbol"></span> {{store.item && store.item.amount !== null
                                            ? (store.item.amount).toFixed(2)
                                            : '' }}
                                        </span>
                                                </td>
                                            </tr>


                                            <tr>
                                                <td><b>Delivery Fee</b></td>
                                                <td colspan="2">
                                            <span class="word-overflow">
                                              <span v-html="store.item?.currency?.symbol"></span> {{store.item && store.item.delivery_fee !== null
                                                ? (store.item.delivery_fee).toFixed(2)
                                                : '' }}
                                            </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Taxes</b></td>
                                                <td colspan="2">
                                            <span class="word-overflow">
                                               <span v-html="store.item?.currency?.symbol"></span> {{store.item && store.item.taxes !== null
                                                ? (store.item.taxes).toFixed(2)
                                                : '' }}
                                            </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Discount</b></td>
                                                <td colspan="2">
                                            <span class="word-overflow">
                                               <span v-html="store.item?.currency?.symbol"></span> {{store.item && store.item.discount !== null
                                                ? (store.item.discount).toFixed(2)
                                                : '' }}
                                            </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Paid</b></td>
                                                <td colspan="2">
                                            <span class="word-overflow">
                                              <span v-html="store.item?.currency?.symbol"></span>  {{ store.item && store.item.paid !== null
                                                ? (store.item.paid).toFixed(2)
                                                : '' }}
                                            </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Payable</b></td>
                                                <td colspan="2">
                                            <span class="word-overflow">
                                              <span v-html="store.item?.currency?.symbol"></span> {{ store.item && store.item.amount !== null && store.item.paid !== null
                                                ? (store.item.amount - store.item.paid).toFixed(2)
                                                : '' }}
                                            </span>
                                                </td>
                                            </tr>


                                            <tr>
                                                <td><b>Order Items</b></td>
                                                <td colspan="2">
                                                    <Badge class=" cursor-pointer" v-tooltip.top="'View Order Details'"
                                                           @click="store.toOrderDetails(store.item)">
                                                        <b>
                                                            {{store.item.items?.length}}
                                                        </b>
                                                    </Badge>
                                                </td>
                                            </tr>


                                        </template>

                                        <template v-else-if="column === 'is_active'">

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
                            <div class=" justify-content-end flex" v-if="(store.item.paid !== store.item.payable)
                        && (store.item && store.item.payments && store.item.payments.length>0)">
                                <Button label="Create Payment" class="p-button-sm" severity="info" raised
                                        v-tooltip.top="'Create Payment'"
                                        style="border-width : 0; background: #4f46e5;"
                                        @click="store.toOrderPayment(store.item.id)"
                                />
                            </div>
                            <div class="mt-4" v-if="store.item && store.item.payments">
                                <DataTable :value="store.item.payments"
                                           dataKey="id"
                                           :rows="10"
                                           :paginator="true"
                                           class="p-datatable-sm p-datatable-hoverable-rows"
                                           :nullSortOrder="-1"
                                           showGridlines
                                           v-model:selection="store.action.items"
                                           responsiveLayout="scroll">


                                    <Column field="transaction_id" header="Transaction ID">
                                        <template #body="prop">
                                            <span class="text-blue-600 cursor-pointer underline"
                                                  @click="store.toPaymentHistory(prop.data,store.item.user)">{{prop.data.transaction_id}}</span>

                                        </template>
                                    </Column>

                                    <Column header="Transaction Date">
                                        <template #body="prop">
                                            {{store.formatDateTime(prop.data.date)}}
                                        </template>
                                    </Column>
                                    <Column header="Method">
                                        <template #body="prop">
                                            {{prop.data.payment_method?.name}}
                                        </template>
                                    </Column>
                                    <Column header="Payable">
                                        <template #body="prop">
                                            <div class="justify-content-end flex min-w-max">
                                                <span v-html="store.item?.currency?.symbol"></span>
                                                {{prop.data.pivot.payable_amount}}
                                            </div>
                                        </template>
                                    </Column>
                                    <Column header="Paid"

                                    >
                                        <template #body="prop">
                                            <div class="justify-content-end flex min-w-max">
                                                <span v-html="store.item?.currency?.symbol"></span>
                                                {{prop.data.pivot.payment_amount}}
                                            </div>
                                        </template>


                                    </Column>
                                    <Column header="Remaining"

                                    >
                                        <template #body="prop">
                                            <div class="justify-content-end flex min-w-max">
                                                <span v-html="store.item?.currency?.symbol"></span>
                                                {{prop.data.pivot.remaining_payable_amount}}
                                            </div>
                                        </template>


                                    </Column>

                                    <template #empty>
                                        <div class="text-center py-3">
                                            <!--                                        No records found.-->
                                            Click to make payment.
                                            <Button label="Create Payment" severity="info" raised
                                                    v-tooltip.top="'Create Payment'"
                                                    style="border-width : 0; background: #4f46e5;cursor: pointer;"
                                                    @click="store.toOrderPayment(store.item.id)"
                                            />
                                        </div>
                                    </template>

                                </DataTable>
                            </div>
                        </TabPanel>

                        <TabPanel value="2">

                            <div v-if="store.item?.shipments?.length" class="max-h-[800px] overflow-y-auto pr-2">
                                <div v-for="shipment in store.item.shipments" :key="shipment.id" class="mb-6">
                                    <div class=" font-semibold mb-2 flex justify-between items-center gap-2">
                                        <div class="flex items-center gap-2">
                                         <span class="text-blue-600 hover:no-underline cursor-pointer underline"
                                               @click="store.toViewShipment(shipment.id)">
                                                 ID: {{ shipment.id }}
                                         </span>
                                            <Badge :value="shipment.status?.name || 'N/A'"
                                                   :severity="shipment.status?.name === 'Delivered' ? 'success' : 'warn'"
                                            />
                                            <a
                                                v-if="shipment.tracking_url"
                                                :href="shipment.tracking_url"
                                                target="_blank"
                                                class="text-blue-500 underline hover:no-underline whitespace-nowrap text-sm"
                                            >
                                                Track
                                            </a>
                                        </div>
                                        <div class="text-sm">
                                            Created At: <span class="underline">{{store.formatDateTime(shipment.created_at)}}</span>
                                        </div>

                                    </div>
                                    <DataTable :value="shipment.items"
                                               stripedRows
                                    >
                                        <Column field="order_item_id" header="Item ID" style="width: 50px"/>
                                        <Column header="Product" style="width: 300px">
                                            <template #body="prop">
                                                <span
                                                    class="text-blue-600 cursor-pointer hover:no-underline underline"
                                                    @click="store.toViewProduct(prop.data.ordered_product?.id)"
                                                >
                                                {{ prop.data.ordered_product?.name || 'N/A' }}-{{ prop.data.ordered_product?.variation?.name}}
                                              </span>
                                            </template>
                                        </Column>
                                        <Column header="Vendor">
                                            <template #body="prop">
                                                <span
                                                    class="text-blue-600 cursor-pointer hover:no-underline underline"
                                                    @click="store.toViewVendor(prop.data.ordered_product?.vendor?.id)"
                                                >
                                                {{ prop.data.ordered_product?.vendor?.name || '-' }}
                                                    </span>
                                            </template>
                                        </Column>
                                        <Column header="Total Qty">
                                            <template #body="prop">
                                                {{ prop.data.ordered_product?.quantity || 'N/A' }}
                                            </template>
                                        </Column>
                                        <Column field="shipment_quantity" header="Shipped "/>
                                        <Column field="pending" header="Pending "/>
                                    </DataTable>
                                </div>
                            </div>
                            <div v-else class="text-center py-4 text-gray-500">
                                No shipments available.
                            </div>

                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </div>
        </Panel>

    </div>

</template>

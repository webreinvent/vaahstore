<script setup>
import {computed, onMounted, ref, watch, watchEffect} from "vue";
import {useRoute} from 'vue-router';

import { usePaymentStore } from '../../stores/store-payments'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = usePaymentStore();
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
    if(route.params && route.params.id)
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

watchEffect(() => {
    if (!route.query.filter || !route.query.filter.order) {
        store.order_filter_key= '';
    }
});
const selectedTabIndex = ref(route.query && route.query.filter && route.query.filter.order ? 1 : 0);

</script>
<template>

    <div class="col-8" >

        <Panel class="is-small" v-if="store && store.item">

            <template class="p-1" #header>

                <div class="p-panel-title w-7 white-space-nowrap
                overflow-hidden text-overflow-ellipsis">
                    #{{store.item.id}}
                </div>
                <div class="p-panel-title w-7 white-space-nowrap
                overflow-hidden text-overflow-ellipsis">
                     {{'Payment Details'}}
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
                        data-testid="payments-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="payments-item-to-list"
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
                                    data-testid="payments-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>
                <TabView :activeIndex="selectedTabIndex">
                    <TabPanel header="Payment Details">

                        <Message severity="info" :closable="false" v-if="store.item.notes">
                            <pre style="font-family: Inter, ui-sans-serif, system-ui; word-break:break-word;overflow-wrap:break-word;word-wrap:break-word;white-space:pre-wrap;">{{store.item.notes}}</pre>
                        </Message>
                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                    <table class="p-datatable-table">
                        <tbody class="p-datatable-tbody">
                        <template v-for="(value, column) in store.item ">

                            <template v-if="column === 'created_by'|| column === 'updated_by'||column === 'order_payments'
                            || column === 'deleted_by' ||column === 'status'||column === 'date' || column === 'taxonomy_id_payment_status' || column === 'transaction_id'|| column === 'created_at'|| column === 'updated_at'
                             || column === 'updated_by_user'|| column === 'payment_method'||column === 'meta'|| column === 'amount'||column === 'status_notes'||column === 'notes'||
                             column === 'vh_st_payment_method_id' || column==='payment_gate_response'|| column==='payment_gate_status'">
                            </template>

                            <template v-else-if="column === 'id' || column === 'uuid'">
                                <VhViewRow :label="column"
                                           :value="value"
                                           :can_copy="true"
                                />
                            </template>

                            <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'  || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                                <VhViewRow :label="column"
                                           :value="value"
                                           type="user"
                                />

                            </template>



                            <template v-else-if="column === 'is_active'">
                                <tr>
                                    <td><b>Payment  Amount</b></td>
                                    <td  colspan="2" >
                                        <span class="word-overflow">
                                        {{store.payment_amount}}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Payment  Method</b></td>
                                    <td  colspan="2" >
                                        <Button class="p-button-outlined p-button-secondary p-button-sm">
                                            {{store.item.payment_method?.name}}
                                        </Button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Payment Status</b></td>
                                    <td  colspan="2" >
                                        <template v-if="store.item.status">
                                            <Badge v-if="store.item.status.slug == 'approved'" severity="success">
                                                {{store.item.status.name}}
                                            </Badge>
                                            <Badge v-else-if="store.item.status.slug == 'pending'"  severity="warning">
                                                {{store.item.status.name}}
                                            </Badge>
                                            <Badge v-else-if="store.item.status.slug == 'rejected'" @click="vaah().copy(value.name)" severity="danger">
                                                {{store.item.status.name}}
                                            </Badge>
                                            <Badge v-else severity="success">
                                                {{store.item.status.name}}
                                            </Badge>
                                        </template>
                                    </td>
                                </tr>


                                <VhViewRow label="Transaction ID"
                                           :value="store.item.transaction_id"
                                           :can_copy="true"
                                />
                                <tr>
                                    <td><b>Transaction Date</b></td>
                                    <td  colspan="2" >
                                        <span class="word-overflow">
                                         {{ store.formatDateTime(store.item.date) }} </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Payment Status  Note</b></td>
                                    <td  colspan="2" >
                                        <span class="word-overflow" v-html="store.item.status_notes"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Payment Gateway Status</b></td>
                                    <td  colspan="2" >
                                        <span class="word-overflow">
                                        {{store.item.payment_gate_status}}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Payment Gateway  Response</b></td>
                                    <td  colspan="2" >

                                        <Button icon="pi pi-eye"
                                                label="view"
                                                class="p-button-outlined p-button-secondary p-button-rounded p-button-sm"
                                                @click="store.openPaymentGateResponseModal(value)"
                                                data-testid="display-payment-gate-response"
                                        />
                                    </td>
                                </tr>
                                <Dialog
                                    v-model:visible="store.display_response_modal"
                                    :breakpoints="{'960px': '75vw', '640px': '90vw'}"
                                    :style="{width: '50vw'}"
                                    :modal="true"
                                >
                                    <p class="m-0" v-if="store.item.payment_gate_response && store.item.payment_gate_response" v-html="'<pre>' + store.item.payment_gate_response + '</pre>'"></p>
                                    <p class="m-0" v-else>No response available.</p>
                                </Dialog>


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
                    <TabPanel :header="`Orders Payment Details (${store.item?.order_payments?.length})`" >
                        <InputText class="" v-model="store.order_filter_key" placeholder="Search By Order" />
                <div class="mt-4" v-if="store.item && store.item.order_payments">
                <DataTable :value="store.filteredOrders"
                           dataKey="id"
                           :rows="10"
                           :paginator="true"
                           class="p-datatable-sm p-datatable-hoverable-rows"
                           :nullSortOrder="-1"
                           showGridlines
                           v-model:selection="store.action.items"
                           responsiveLayout="scroll">



                    <Column field="order.id" header="Order ID"  >
                    </Column>

                    <Column  header="Order"
                            class="overflow-wrap-anywhere"
                            >
                        <template #body="prop">
                            <span style="text-wrap:nowrap" class="underline text-primary hover:text-primary-700 cursor-pointer" @click="store.toOrderDetails(prop.data.order.id)">{{prop.data.order.user.name}}</span>

                        </template>


                    </Column>

                    <Column  header="Amount"
                             class="overflow-wrap-anywhere"
                            >

                        <template #body="prop">
<!--                            <Badge class="min-w-max" severity="info">-->
                                {{(prop.data.order.amount).toFixed(2)}}
<!--                            </Badge>-->

                        </template>

                    </Column>
                    <Column  header="Payable"
                             class="overflow-wrap-anywhere"
                             >

                        <template #body="prop">
                            <div class="justify-content-end flex">
<!--                            <Badge class="min-w-max" severity="info">-->
                                {{prop.data.payable_amount}}
<!--                            </Badge>-->
                            </div>
                        </template>

                    </Column>

                    <Column  header="Paid"  class="overflow-wrap-anywhere "
                             >

                        <template #body="prop">
                            <div class="justify-content-end flex">
                            <span v-if="prop.data.payment_amount_paid == 0"
                                   value="0" class="min-w-max"
                                   severity="danger">0</span>
                            <span v-else-if="prop.data.payment_amount_paid > 0"
                                   :value="prop.data.payment_amount_paid" class="min-w-max"
                                   severity="secondary">{{prop.data.payment_amount_paid}}</span>
                            </div>
                        </template>

                    </Column>
                    <Column  header="Remaining Payable"  class="overflow-wrap-anywhere"
                             >
                        <template #body="prop">
                            <div class="justify-content-end flex">
                            <span  v-if="prop.data.remaining_payable_amount == 0"
                                   value="0"
                                  >0</span>
                            <Badge class="min-w-max" v-else-if="prop.data.remaining_payable_amount > 0"
                                   :value="prop.data.remaining_payable_amount"
                                   severity="warning"></Badge>
                            </div>
                        </template>

                    </Column>

                    <Column field="paymentStatus"  header="Order Payment Status"  class="overflow-wrap-anywhere"
                           >
                        <template #body="prop">
                            <template v-if="prop.data.taxonomy_order_payment_status">
                                <Badge class="min-w-max" v-if="prop.data.taxonomy_order_payment_status.slug === 'paid'" severity="success">
                                    {{ prop.data.taxonomy_order_payment_status.name }}
                                </Badge>
                                <Badge class="min-w-max" v-else-if="prop.data.taxonomy_order_payment_status.slug === 'partially-paid'" severity="info">
                                    {{ prop.data.taxonomy_order_payment_status.name }}
                                </Badge>
                                <Badge class="min-w-max" v-else severity="danger">
                                    {{ prop.data.taxonomy_order_payment_status.name }}
                                </Badge>
                            </template>
                            <template v-else>
                                <Badge class="min-w-max" severity="danger">
                                    Pending
                                </Badge>
                            </template>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="text-center py-3">
                            No records found.
                        </div>
                    </template>

                </DataTable>
                </div>
                    </TabPanel>
                </TabView>
            </div>
        </Panel>

    </div>

</template>

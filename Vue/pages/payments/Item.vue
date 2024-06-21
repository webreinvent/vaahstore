<script setup>
import {onMounted, ref, watch} from "vue";
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

</script>
<template>

    <div class="col-9" >

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
                <TabView>
                    <TabPanel header="Payment Details">
                        <Message severity="info" :closable="false" v-if="store.item.notes">
                            <div>
                                <pre style="font-family: Inter, ui-sans-serif, system-ui; width:350px;overflow-wrap: break-word;word-wrap:break-word;" v-html="store.item.notes"></pre>
                            </div>
                        </Message>
                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                    <table class="p-datatable-table">
                        <tbody class="p-datatable-tbody">
                        <template v-for="(value, column) in store.item ">

                            <template v-if="column === 'created_by'|| column === 'updated_by'||column === 'orders'
                            || column === 'deleted_by' ||column === 'status'||column === 'date' || column === 'taxonomy_id_payment_status' || column === 'transaction_id'|| column === 'created_at'|| column === 'updated_at'
                             || column === 'updated_by_user'|| column === 'payment_method'||column === 'meta'|| column === 'amount'||column === 'status_notes'||column === 'notes'||
                             column === 'vh_st_payment_method_id'">
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
                                        {{store.item.amount}}</span>
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
                                        {{store.item.date}}</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Payment Status  Note</b></td>
                                    <td  colspan="2" >
                                        <span class="word-overflow">
                                        {{store.item.status_notes}}</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Payment Gate  Response</b></td>
                                    <td  colspan="2" >
                                        <span class="word-overflow">
                                        {{store.item.meta}}</span>
                                    </td>
                                </tr>
                                <VhViewRow :label="column"
                                           :value="value"
                                           type="yes-no"
                                />
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
                    <TabPanel :header="`Orders Payment Details (${store.item.orders.length})`">
                <div class="mt-4" v-if="store.item && store.item.orders">
                <DataTable :value="store.item.orders"
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

                    <Column  header="Order"
                            class="overflow-wrap-anywhere"
                            >
                        <template #body="prop">
                            {{prop.data.user.name}}

                        </template>


                    </Column>
                    <Column  header="">
                        <template #body="prop">
                            <a @click="store.toOrderDetails(prop.data.id)" v-tooltip.top="'View Order Items'"
                                class=" ml-2 pi pi-info-circle">

                            </a>
                        </template>
                    </Column>
                    <Column  header="Amount"
                             class="overflow-wrap-anywhere"
                            >

                        <template #body="prop">
                            <Badge severity="info">{{prop.data.amount}}</Badge>

                        </template>

                    </Column>
                    <Column  header="Payable Amount"
                             class="overflow-wrap-anywhere"
                             >

                        <template #body="prop">
                            <Badge severity="info">{{prop.data.pivot.payable_amount}}</Badge>

                        </template>

                    </Column>

                    <Column  header="Payment Amount"  class="overflow-wrap-anywhere"
                             >

                        <template #body="prop">
                            <Badge v-if="prop.data.pivot.payment_amount_paid == 0"
                                   value="0"
                                   severity="danger"></Badge>
                            <Badge v-else-if="prop.data.pivot.payment_amount_paid > 0"
                                   :value="prop.data.pivot.payment_amount_paid"
                                   severity="secondary"></Badge>
                        </template>

                    </Column>
                    <Column  header="Remaining Payable"  class="overflow-wrap-anywhere"
                             >
                        <template #body="prop">
                            <Badge v-if="prop.data.pivot.remaining_payable_amount == 0"
                                   value="0"
                                  ></Badge>
                            <Badge v-else-if="prop.data.pivot.remaining_payable_amount > 0"
                                   :value="prop.data.pivot.remaining_payable_amount"
                                   severity="warning"></Badge>
                        </template>

                    </Column>

                    <Column field="paymentStatus"  header="Order Payment Status"  class="overflow-wrap-anywhere"
                           >
                        <template #body="prop">
                            <template v-if="prop.data.order_payment_status">
                                <Badge v-if="prop.data.order_payment_status.slug === 'paid'" severity="success">
                                    {{ prop.data.order_payment_status.name }}
                                </Badge>
                                <Badge v-else-if="prop.data.order_payment_status.slug === 'partially-paid'" severity="info">
                                    {{ prop.data.order_payment_status.name }}
                                </Badge>
                                <Badge v-else severity="danger">
                                    {{ prop.data.order_payment_status.name }}
                                </Badge>
                            </template>
                            <template v-else>
                                <Badge severity="danger">
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

<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useOrderStore } from '../../stores/store-orders'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useOrderStore();
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

</script>
<template>

    <div class="col-7" >

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
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="orders-item-to-list"
                            @click="store.toList()"/>

                </div>



            </template>


            <div class="mt-2" v-if="store.item">

                <Message severity="info" :closable="false" v-if="store.item.status_notes">
                    <tr>
                        <td  colspan="2" >
                            <div  style="width:300px;word-break: break-word;">
                                {{store.item.status_notes}}</div>
                        </td>
                    </tr>
                </Message>

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
                <TabView>
                    <TabPanel header="Order Details">
                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by' || column === 'delivery_fee'||
                            column === 'taxes' || column === 'updated_by'||
                            column === 'order_payment_status'|| column === 'paid' ||
                            column === 'discount'|| column === 'taxonomy_id_payment_status'||
                            column === 'payable' || column === 'user'|| column === 'payment_method'||
                            column === 'status'|| column === 'status_order'|| column === 'amount'||
                            column === 'is_active_order_item' || column == 'is_invoice_available'
                            || column == 'meta' || column == 'deleted_by' || column == 'status_notes'
                             || column == 'slug' || column === 'payments'">
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



                        <template v-else-if="column === 'taxonomy_id_order_status'">
                            <tr>
                                <td><b>Order Payment Status</b></td>
                                <td  colspan="2" >
                                    <Badge class="word-overflow">
                                        {{store.item.order_payment_status?.name}}</Badge>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Shipping Status</b></td>
                                <td  colspan="2" >
                                    <Badge class="word-overflow">
                                        {{'Pending'}}</Badge>
                                </td>
                            </tr>
                            <VhViewRow label="Order Status"
                                       :value="store.item.status"
                                       type="status"
                            />
                        </template>

                        <template v-else-if="column === 'vh_st_payment_method_id'">
                            <VhViewRow label="Payment Method"
                                       :value="store.item.payment_method?.name"
                            />
                        </template>

                        <template v-else-if="column === 'is_paid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'vh_user_id'">
                            <VhViewRow label="Order Name"
                                       :value="store.item.user?.display_name"
                            />

                            <tr>
                                <td><b>Order Payable Amount</b></td>
                                <td colspan="2">
                                    <span class="word-overflow">
                                        {{ store.item && store.item.amount !== null && store.item.paid !== null
                                        ? (store.item.amount - store.item.paid).toFixed(2)
                                        : '' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Order Paid Amount</b></td>
                                <td colspan="2">
                                    <span class="word-overflow">
                                        {{ store.item && store.item.paid !== null
                                        ? (store.item.paid).toFixed(2)
                                        : '' }}
                                    </span>
                                </td>
                            </tr>


                            <tr >
                                <td><b>Delivery Fee</b></td>
                                <td colspan="2">
                                    <span class="word-overflow">
                                       {{store.item && store.item.delivery_fee !== null
                                        ? (store.item.delivery_fee).toFixed(2)
                                        : '' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Taxes</b></td>
                                <td colspan="2">
                                    <span class="word-overflow">
                                        {{store.item && store.item.taxes !== null
                                        ? (store.item.taxes).toFixed(2)
                                        : '' }}
                                    </span>
                                </td>
                            </tr>
                            <tr >
                                <td><b>Discount</b></td>
                                <td colspan="2">
                                    <span class="word-overflow">
                                        {{store.item && store.item.discount !== null
                                        ? (store.item.discount).toFixed(2)
                                        : '' }}
                                    </span>
                                </td>
                            </tr>
                            <tr >
                                <td><b>Order Amount</b></td>
                                <td colspan="2">
                                <span class="word-overflow">
                                    {{store.item && store.item.amount !== null
                                    ? (store.item.amount).toFixed(2)
                                    : '' }}
                                </span>
                                </td>
                            </tr>


                        </template>

                        <template v-else-if="column === 'is_active'">
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
                    <TabPanel :header="`Transaction History (${store.item?.payments?.length})`">
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



                                <Column field="transaction_id" header="Transaction ID"  >
                                    <template #body="prop">
                                        <span  class="underline text-primary hover:text-primary-700 cursor-pointer" @click="store.toPaymentHistory(prop.data,store.item.user)">{{prop.data.transaction_id}}</span>

                                    </template>
                                </Column>

                                <Column  header="Transaction Date">
                                    <template #body="prop">
                                        {{store.formatDateTime(prop.data.date)}}
                                    </template>
                                </Column>
                                <Column  header="Created By">
                                    <template #body="prop">
                                        {{prop.data.created_by_user.name}}
                                    </template>
                                </Column>
                                <Column header="Payable" >
                                    <template #body="prop">
                                        <div class="justify-content-end flex">
                                        {{prop.data.pivot.payable_amount}}
                                        </div>
                                    </template>
                                </Column>
                                <Column  header="Paid"

                                >
                                    <template #body="prop" >
                                        <div class="justify-content-end flex">
                                        {{prop.data.pivot.payment_amount_paid}}
                                        </div>
                                    </template>


                                </Column>
                                <Column  header="Remaining"

                                >
                                    <template #body="prop">
                                        <div class="justify-content-end flex">
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
                </TabView>
            </div>
        </Panel>

    </div>

</template>

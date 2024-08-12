<script setup>
import {computed, onMounted, ref, watch} from "vue";
import { usePaymentStore } from '../../stores/store-payments'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';
import {useOrderStore} from "../../stores/store-orders";


const store = usePaymentStore();
const route = useRoute();
const order_store=useOrderStore();

onMounted(async () => {
    if (route.query && route.query.order_id) {
        await order_store.getItem(route.query.order_id);
        if (order_store.item.id) {
            store.item = store.item || {};
            store.item.orders = [{
                id: order_store.item.id,
                user_name: order_store.item.user.name,
                payable_amount: order_store.item.payable-order_store.item.paid,
            }];
        } else {
            store.item.orders = null;
        }
    }

    await store.getFormMenu();

    store.watchOrderAmount();
});

//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};
//--------/form_menu


</script>
<template>

    <div class="col-6" >

        <Panel class="is-small">

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <span v-if="store.item && store.item.id">
                            Update
                        </span>
                        <span v-else>
                            Create
                        </span>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">

                    <Button class="p-button-sm"
                            v-tooltip.left="'View'"
                            v-if="store.item && store.item.id"
                            data-testid="payments-view-item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="payments-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="payments-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="payments-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="payments-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="mt-2">

                <Message severity="error"
                         class="p-container-message mb-3"
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
                                    data-testid="articles-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>
                <div v-if="!$route.params.id">
                    <VhField label="Orders*">
                        <div class="p-inputgroup">
                            <AutoComplete
                                v-model="store.item.orders"
                                class="w-full"
                                multiple
                                :suggestions="store.filtered_orders"
                                @complete="store.searchOrders($event)"
                                placeholder="Select orders"
                                data-testid="payments-order"
                                name="payments-order"
                                :dropdown="true"
                                optionLabel="user_name"
                                forceSelection
                            />
                        </div>
                    </VhField>

<!--                    <VhField >-->
                    <div v-if="store.item.orders && store.item.orders.length > 0 " class="mb-4">
                            <DataTable :value="store.item.orders"
                                       rowGroupMode="subheader">

                            <Column field="user_name" header="Order" >
                                <template #body="prop">{{prop.data.user_name}}
                                </template>
                            </Column>
                            <Column field="payable_amount" header="Payable Amount" >

                                <template #body="prop">
                                    <InputGroup>
                                        <InputGroupAddon>&#8377;</InputGroupAddon>
                                        <InputNumber
                                            style="width: 6rem;"
                                            v-model="prop.data.payable_amount"
                                            readonly
                                            :placeholder="'Total amount'"
                                            data-testid="payments-order-payable"
                                            :minFractionDigits="0"
                                            :maxFractionDigits="2"
                                            inputId="locale-indian"
                                            locale="en-IN"
                                        />
                                    </InputGroup>
                                </template>
                            </Column>
                            <Column header="Payment">
                                <template #body="prop">
                                    <InputGroup>
                                        <InputGroupAddon>&#8377;</InputGroupAddon>
                                        <InputNumber
                                            v-model="prop.data.pay_amount"
                                            :placeholder="'Enter amount'"
                                            data-testid="payments-order-paid"
                                            :minFractionDigits="0"
                                            :maxFractionDigits="2"
                                            style="width: 6rem;"
                                            @input="store.totalPaidAmount($event, prop.data.id)"
                                        />
                                    </InputGroup>

                                </template>
                            </Column>
                            <Column header="">
                                <template #body="prop">
                                    <div class=" justify-content-end">
                                        <Button
                                            class="p-button-primary  text-red-500"
                                            icon="pi pi-times"
                                            data-testid="payments-remove-orders"
                                            @click="store.removeOrderDetail(prop.rowIndex)"
                                        />
                                    </div>

                                </template>
                            </Column>
                            </DataTable>
                         </div>

<!--                    </VhField>-->
                    <VhField label="Total Payment"  v-if="store.item.orders && store.item.orders.length>0">
                        <InputNumber v-model="store.item.amount" placeholder="Total payment amount"
                                     data-testid="payments-total-payment"
                                     :minFractionDigits="0" :maxFractionDigits="2" readonly label="Total Amount" class="w-full" />
                    </VhField>
                    <VhField label="Payment Method*">
                        <AutoComplete
                            value="id"
                            v-model="store.item.payment_method"
                            @change="store.setPaymentMethod($event)"
                            class="w-full"
                            name="orders-payment_method"
                            :suggestions="store.payment_method_suggestion"
                            @complete="store.searchPaymentMethod($event)"
                            placeholder="Select payment method"
                            :dropdown="true" optionLabel="name"
                            data-testid="payments-payment-method"
                            forceSelection>
                        </AutoComplete>
                    </VhField>

                </div>

                <VhField label="Payment Notes">
                    <Textarea placeholder="Enter payment note"
                              v-model="store.item.notes" rows="3" class="w-full"
                              data-testid="payments-payment-notes" name="vendors-status_notes" />
                </VhField>





            </div>
        </Panel>

    </div>

</template>

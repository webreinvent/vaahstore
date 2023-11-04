<script setup>
import {onMounted, ref, watch} from "vue";
import { useOrderStore } from '../../stores/store-orders'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';

const store = useOrderStore();
const route = useRoute();

onMounted(async () => {
    if(route.params && route.params.id)
    {
        await store.getItem(route.params.id);

    }
    await store.getFormMenu();
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
                            v-if="store.item && store.item.id"
                            data-testid="orders-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="orders-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="orders-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="orders-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="orders-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="orders-to-list"
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

                <VhField label="User*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.user"
                        @change="store.setUser($event)"
                        class="w-full"
                        name="orders-user"
                        :suggestions="store.filtered_users"
                        @complete="store.searchUser"
                        placeholder="Select User"
                        :dropdown="true" optionLabel="first_name"
                        data-testid="orders-user"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Amount*">
                    <InputNumber
                        placeholder="Enter a Amount"
                        inputId="minmax-buttons"
                        name="orders-quantity"
                        v-model="store.item.amount"
                        mode="decimal"
                        :min="0"
                        @input = "store.updateAmount($event)"
                        showButtons
                        data-testid="orders-amount"/>
                </VhField>

                <VhField label="Delivery Fee">
                    <InputNumber
                        placeholder="Enter Delivery Fee"
                        inputId="minmaxfraction" :minFractionDigits="2"
                        name="orders-delivery_fee"
                        v-model="store.item.delivery_fee"
                        mode="decimal"
                        :min="0"
                        @input = "store.updateDeliveryFee($event)"
                        showButtons
                        data-testid="orders-delivery_fee"/>
                </VhField>

                <VhField label="Taxes">
                    <InputNumber
                        placeholder="Enter Tax amount"
                        inputId="minmaxfraction" :minFractionDigits="2" showButtons
                        name="orders-taxes"
                        v-model="store.item.taxes"
                        mode="decimal"
                        :min="0"
                        @input ="store.updateTaxAmount($event)"
                        data-testid="orders-taxes"/>
                </VhField>

                <VhField label="Discount">
                    <InputNumber
                        placeholder="Enter Discount Amount"
                        inputId="minmaxfraction" :minFractionDigits="2" showButtons
                        name="orders-discount"
                        v-model="store.item.discount"
                        mode="decimal"
                        :min="0"
                        @input ="store.updateDiscountAmount($event)"
                        data-testid="orders-discount"/>
                </VhField>

                <VhField label="Payable*">
                    <InputNumber
                        placeholder="Enter Payable amount"
                        inputId="minmaxfraction" :minFractionDigits="2"
                        name="orders-payable"
                        v-model="store.item.payable"
                        mode="decimal" showButtons
                        :min="0"
                        data-testid="orders-payable"/>
                </VhField>

                <VhField label="Paid">
                    <InputNumber
                        placeholder="Enter Paid Amount"
                        inputId="minmaxfraction" :min-fraction-digits="2"
                        name="orders-paid"
                        v-model="store.item.paid"
                        mode="decimal" showButtons
                        :min="0"
                        @input ="store.checkPaidAmount($event)"
                        data-testid="orders-paid"/>
                </VhField>

                <VhField label="Is Paid">
                    <InputSwitch
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        v-bind="store.item.paid <= 0 ? store.item.is_paid = 0 : store.item.is_paid = 1"
                        name="products-is_paid"
                        data-testid="products-is_paid"
                        v-model="store.item.is_paid"/>
                </VhField>

                <VhField label="Payment Method">
                    <AutoComplete
                        value="id"
                        v-model="store.item.payment_method"
                        @change="store.setPaymentMethod($event)"
                        class="w-full"
                        name="orders-payment_method"
                        :suggestions="store.payment_method_suggestion"
                        @complete="store.searchPaymentMethod($event)"
                        placeholder="Select Payment Method"
                        :dropdown="true" optionLabel="name"
                        data-testid="orders-payment_method"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        name="orders-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="orders-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="orders-status_notes"
                              data-testid="orders-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="orders-active"
                                 @change="store.selectStatus()"
                                 data-testid="orders-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

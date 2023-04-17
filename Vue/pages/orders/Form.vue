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

    await store.watchItem();
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

        <Panel >

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
                    <Button label="Save"
                            v-if="store.item && store.item.id"
                            data-testid="orders-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
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
                        data-testid="orders-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="orders-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="User">
                    <AutoComplete
                        v-model="store.item.vh_user_id"
                        class="w-full"
                        name="orders-user"
                        :suggestions="store.user_suggestion"
                        @complete="store.searchUser($event)"
                        placeholder="Select User"
                        :dropdown="true" optionLabel="first_name"
                        data-testid="orders-user"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Amount">
                    <InputNumber
                        placeholder="Enter a Amount"
                        inputId="minmax-buttons"
                        name="orders-quantity"
                        v-model="store.item.amount"
                        mode="decimal" showButtons
                        data-testid="orders-amount"/>
                </VhField>

                <VhField label="Delivery Fee">
                    <InputNumber
                        placeholder="Enter a Delivery Fee"
                        name="orders-delivery_fee"
                        v-model="store.item.delivery_fee"
                        inputId="minmaxfraction" :minFractionDigits="2"
                        showButtons
                        data-testid="orders-delivery_fee"/>
                </VhField>

                <VhField label="Taxes">
                    <InputNumber
                        placeholder="Enter a Taxes"
                        name="orders-taxes"
                        v-model="store.item.taxes"
                        inputId="minmaxfraction" :minFractionDigits="2" showButtons
                        data-testid="orders-taxes"/>
                </VhField>

                <VhField label="Discount">
                    <InputNumber
                        placeholder="Enter a Discount"
                        name="orders-discount"
                        v-model="store.item.discount"
                        inputId="minmaxfraction" :minFractionDigits="2" showButtons
                        data-testid="orders-discount"/>
                </VhField>

                <VhField label="Payable">
                    <InputNumber
                        placeholder="Enter a Payable"
                        name="orders-payable"
                        v-model="store.item.payable"
                        inputId="minmaxfraction" :minFractionDigits="2" showButtons
                        data-testid="orders-payable"/>
                </VhField>

                <VhField label="Paid">
                    <InputNumber
                        placeholder="Enter a Paid"
                        inputId="minmax-buttons"
                        name="orders-paid"
                        v-model="store.item.paid"
                        mode="decimal" showButtons
                        :min="0"
                        data-testid="orders-paid"/>
                </VhField>

                <VhField label="Is Paid">
                    <InputSwitch
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        v-bind="store.item.paid == 0 ? store.item.is_paid = 0 : store.item.is_paid = 1"
                        name="products-is_paid"
                        data-testid="products-is_paid"
                        v-model="store.item.is_paid"/>
                </VhField>

                <VhField label="Payment Method">
                    <AutoComplete
                        v-model="store.item.vh_st_payment_method_id"
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

                <VhField label="Status">
                    <AutoComplete
                        v-model="store.item.taxonomy_id_order_status"
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
                                 name="orders-active"
                                 data-testid="orders-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

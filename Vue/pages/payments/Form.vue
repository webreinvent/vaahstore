<script setup>
import {onMounted, ref, watch} from "vue";
import { usePaymentStore } from '../../stores/store-payments'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = usePaymentStore();
const route = useRoute();

onMounted(async () => {
    /**
     * Fetch the record from the database
     */
    if((!store.item || Object.keys(store.item).length < 1)
            && route.params && route.params.id)
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
                            v-tooltip.left="'View'"
                            v-if="store.item && store.item.id"
                            data-testid="payments-view_item"
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


                <VhField label="Orders">
                    <div class="p-inputgroup">
                        <AutoComplete
                            value="id"
                            v-model="store.item.order"

                            class="w-full"
                            multiple
                            :suggestions="store.filtered_orders"
                            @complete="store.searchOrder($event)"
                            placeholder="Select order"
                            data-testid="payments-order"
                            name="payments-order"
                            :dropdown="true"
                            optionLabel="order"
                            forceSelection></AutoComplete>

                    </div>
                </VhField>

                <VhField label="Order Amount">
<!--                    <InputNumber-->
<!--                        placeholder="Enter  amount"-->
<!--                        inputId="minmaxfraction" :minFractionDigits="2"-->
<!--                        name="orders-quantity"-->
<!--                        v-model="store.item.amount"-->
<!--                        mode="decimal"-->
<!--                        :min="0"-->

<!--                        showButtons-->
<!--                        class="p-inputtext-sm w-full h-2rem"-->
<!--                        data-testid="orders-amount"/>-->
                    <InputNumber v-model="store.amount" disabled  class="w-full" />
                </VhField>


                <VhField label="To Pay">
                    <InputNumber
                        placeholder="Enter amount to be paid"
                        inputId="minmaxfraction" :min-fraction-digits="2"
                        name="orders-paid"
                        v-model="store.item.paid"
                        mode="decimal" showButtons
                        :min="0"  class="p-inputtext-sm w-full h-2rem"
                        @input ="store.checkPaidAmount($event)"
                        data-testid="payments-paid"/>
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
                        placeholder="Select payment method"
                        :dropdown="true" optionLabel="name"
                        data-testid="payments-payment_method"
                        forceSelection>
                    </AutoComplete>
                </VhField>
                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 name="payments-active"
                                 data-testid="payments-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

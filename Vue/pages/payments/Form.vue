<script setup>
import {computed, onMounted, ref, watch} from "vue";
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
const totalPaidAmount = computed(() => {
    if (!store.item.order) return 0;

    return store.item.order.reduce((total, detail) => total + parseFloat(detail.pay_amount || 0), 0);
});
watch(totalPaidAmount, (newValue) => {
    store.item.total_paid_amount = newValue;
}, { immediate: true });
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
                            v-model="store.item.order"
                            class="w-full"
                            multiple
                            :suggestions="store.filtered_orders"
                            @complete="store.searchOrders($event)"
                            placeholder="Select order"
                            data-testid="payments-order"
                            name="payments-order"
                            :dropdown="true"
                            optionLabel="user_name"
                            forceSelection
                        />
                    </div>
                </VhField>

                <VhField >
<!--                    <div class="flex items-center w-full ">-->

<!--                        &lt;!&ndash; Render regular InputText and InputNumber for subsequent items &ndash;&gt;-->
<!--                        <InputText placeholder="Order" disabled filled required />-->
<!--                        <InputText filled disabled placeholder="Total amount" />-->
<!--                        <InputNumber filled disabled placeholder="Pay Amount" inputId="minmaxfraction" :minFractionDigits="2" :maxFractionDigits="5" class="w-full" />-->


<!--                        <div class="flex items-center ml-auto">-->
<!--                            <Button-->
<!--                                class="p-button-primary p-button-sm text-red-500"-->
<!--                                label="action"-->
<!--                                data-testid="sources-scraping_details"-->

<!--                            />-->
<!--                        </div>-->
<!--                    </div>-->
                    <div v-for="(detail, index) in store.item.order" :key="index" class="mb-2">
<!--                        <InputNumber type="hidden" v-model="detail.id" />-->
                        <div class="flex items-center w-full ">
                        <label class=" w-full" v-if="index === 0" for="pay-amount-input">Order Name</label>
                        <label class=" w-full" v-if="index === 0" for="pay-amount-input">Payable amount</label>
                        <label class=" w-full" v-if="index === 0" for="pay-amount-input">Payment Amount</label>
                        </div>
                        <div class="flex items-center w-full ">


                            <!-- Render regular InputText and InputNumber for subsequent items -->
                            <InputText v-model="detail.user_name" :placeholder="'Order ' + (index + 1)" required />
                            <InputText v-model="detail.amount" disabled placeholder="Total amount" />
                            <InputNumber v-model="detail.pay_amount" placeholder="Pay Amount" inputId="minmaxfraction" :minFractionDigits="2" :maxFractionDigits="5" class="w-full" />


                        <div class="flex items-center ml-auto">
                            <Button
                                class="p-button-primary p-button-sm text-red-500"
                                icon="pi pi-times"
                                data-testid="sources-scraping_details"
                                @click="store.removeOrderDetail(index)"
                            />
                        </div>
                        </div>

                        <small v-if="parseFloat(detail.pay_amount) > parseFloat(detail.amount)" id="email-error" class="p-error"> Pay Amount cannot be greater than Total amount</small>
                    </div>
<!--                    <InputText v-model="totalPaidAmount" placeholder="Total payment amount" disabled label="Total Amount" class="w-full" />-->
                </VhField>


<!--                <VhField label="Order Amount">-->
<!--&lt;!&ndash;                    <InputNumber&ndash;&gt;-->
<!--&lt;!&ndash;                        placeholder="Enter  amount"&ndash;&gt;-->
<!--&lt;!&ndash;                        inputId="minmaxfraction" :minFractionDigits="2"&ndash;&gt;-->
<!--&lt;!&ndash;                        name="orders-quantity"&ndash;&gt;-->
<!--&lt;!&ndash;                        v-model="store.item.amount"&ndash;&gt;-->
<!--&lt;!&ndash;                        mode="decimal"&ndash;&gt;-->
<!--&lt;!&ndash;                        :min="0"&ndash;&gt;-->

<!--&lt;!&ndash;                        showButtons&ndash;&gt;-->
<!--&lt;!&ndash;                        class="p-inputtext-sm w-full h-2rem"&ndash;&gt;-->
<!--&lt;!&ndash;                        data-testid="orders-amount"/>&ndash;&gt;-->
<!--                    <InputNumber v-model="store.amount" disabled  class="w-full" />-->
<!--                </VhField>-->


<!--                <VhField label="To Pay">-->
<!--                    <InputNumber-->
<!--                        placeholder="Enter amount to be paid"-->
<!--                        inputId="minmaxfraction" :min-fraction-digits="2"-->
<!--                        name="orders-paid"-->
<!--                        v-model="store.item.paid"-->
<!--                        mode="decimal" showButtons-->
<!--                        :min="0"  class="p-inputtext-sm w-full h-2rem"-->
<!--                        @input ="store.checkPaidAmount($event)"-->
<!--                        data-testid="payments-paid"/>-->
<!--                </VhField>-->
                <VhField label="Total Payment Amount" v-if="store.item.order && store.item.order.length>0">
                    <InputText v-model="totalPaidAmount" placeholder="Total payment amount" disabled label="Total Amount" class="w-full" />
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

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
                        <span>
                            Add Order Item
                        </span>
                    </div>

                </div>


            </template>

            <template #icons>

                <div class="p-inputgroup">
                    <Button label="Save"
                            v-if="store.item && store.item.id"
                            data-testid="orderitems-save"
                            @click="store.itemAction('save-orderitems')"
                            icon="pi pi-save"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="orderitems-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="orderitems-to-list"
                            @click="store.toList()">
                    </Button>
                </div>

            </template>


            <div v-if="store.item">

<!--                <VhField label="User">-->
<!--                    <AutoComplete-->
<!--                        v-model="store.item.vh_user_id"-->
<!--                        class="w-full"-->
<!--                        name="orderitems-user"-->
<!--                        :suggestions="store.user_suggestion"-->
<!--                        @complete="store.searchUser($event)"-->
<!--                        placeholder="Select User"-->
<!--                        :dropdown="true" optionLabel="first_name"-->
<!--                        data-testid="orderitems-user"-->
<!--                        forceSelection>-->
<!--                    </AutoComplete>-->
<!--                </VhField>-->

<!--                <VhField label="Order">-->
<!--                    <AutoComplete-->
<!--                        v-model="store.item.vh_st_order_id"-->
<!--                        class="w-full"-->
<!--                        name="orderitems-order"-->
<!--                        :suggestions="store.order_suggestion"-->
<!--                        @complete="store.searchOrder($event)"-->
<!--                        placeholder="Select Order"-->
<!--                        :dropdown="true" optionLabel="id"-->
<!--                        data-testid="orderitems-order"-->
<!--                        forceSelection>-->
<!--                    </AutoComplete>-->
<!--                </VhField>-->

                <VhField label="Types">
                    <AutoComplete
                        v-model="store.item.taxonomy_id_order_items_types"
                        class="w-full"
                        name="orderitems-types"
                        :suggestions="store.type_suggestion"
                        @complete="store.searchType($event)"
                        placeholder="Select Types"
                        :dropdown="true" optionLabel="name"
                        data-testid="orderitems-types"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Product">
                    <AutoComplete
                        v-model="store.item.vh_st_product_id"
                        class="w-full"
                        name="orderitems-product"
                        :suggestions="store.product_suggestion"
                        @complete="store.searchProduct($event)"
                        placeholder="Select Types"
                        :dropdown="true" optionLabel="name"
                        data-testid="orderitems-product"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Product Variation">
                    <AutoComplete
                        v-model="store.item.vh_st_product_variation_id"
                        class="w-full"
                        name="orderitems-product_variation"
                        :suggestions="store.product_variation_suggestion"
                        @complete="store.searchProductVariation($event)"
                        placeholder="Select Product Variation"
                        :dropdown="true" optionLabel="name"
                        data-testid="orderitems-product_variation"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Vendor">
                    <AutoComplete
                        v-model="store.item.vh_st_vendor_id"
                        class="w-full"
                        name="orderitems-vendor"
                        :suggestions="store.vendor_suggestion"
                        @complete="store.searchVendor($event)"
                        placeholder="Select vendor"
                        :dropdown="true" optionLabel="name"
                        data-testid="orderitems-vendor"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Customer Groups">
                    <AutoComplete
                        v-model="store.item.vh_st_customer_group_id"
                        class="w-full"
                        name="orderitems-customer_group"
                        :suggestions="store.customer_group_suggestion"
                        @complete="store.searchCustomerGroup($event)"
                        placeholder="Select Customer Group"
                        :dropdown="true" optionLabel="name"
                        data-testid="orderitems-customer_group"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Invoice Url">
                    <InputText class="w-full"
                               placeholder="Enter a URL"
                               name="orderitems-invoice_url"
                               data-testid="orderitems-invoice_url"
                               v-model="store.item.invoice_url"/>
                </VhField>

                <VhField label="Tracking">
                    <InputText class="w-full"
                               placeholder="Enter a Tracking"
                               name="orderitems-tracking"
                               data-testid="orderitems-tracking"
                               v-model="store.item.tracking"/>
                </VhField>

                <VhField label="Status">
                    <AutoComplete
                        v-model="store.item.taxonomy_id_order_items_status"
                        class="w-full"
                        name="orderitems-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select status"
                        :dropdown="true" optionLabel="name"
                        data-testid="orderitems-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea v-model="store.item.status_notes_order_items"
                              placeholder="Enter Status Notes"
                              data-testid="orderitems-status_notes"
                              :autoResize="true" rows="5" cols="30" />
                </VhField>

                <VhField label="Is Invoice Available">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="orderitems-is_invoice_available"
                                 data-testid="orderitems-is_invoice_available"
                                 v-model="store.item.is_invoice_available"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="orderitems-active"
                                 data-testid="orderitems-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>

        </Panel>

    </div>

</template>

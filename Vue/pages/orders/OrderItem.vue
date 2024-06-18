<script setup>
import {onMounted, ref, watch} from "vue";
import { useOrderStore } from '../../stores/store-orders'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useOrderStore();
const route = useRoute();
store.getOrderProductMenu();
// onMounted(async () => {
//
//     if(route.params && route.params.id)
//     {
//         await store.getItem(route.params.id);
//     }
//
// });

//--------form_menu
const order_product_menu = ref(null);
const toggleOrderProductMenu = (event) => {
    order_product_menu.value.toggle(event);
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
                            Order Products
                        </span>
                    </div>

                </div>


            </template>

            <template #icons>

                <div class="p-inputgroup">
                    <Button label="Add"
                            data-testid="orderitems-save"
                            @click="store.createOrder()"
                            icon="pi pi-save"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleOrderProductMenu"
                        data-testid="orderproduct-form-menu"
                        icon="pi pi-angle-down"
                        class = "p-button-sm"
                        aria-haspopup="true"/>

                    <Menu ref="order_product_menu"
                          :model="store.order_product_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-sm"
                            icon="pi pi-times"
                            data-testid="orderitems-to-list"
                            @click="store.toList()">
                    </Button>
                </div>

            </template>


            <div v-if="store.item">
                <VhField label="Payment Type*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.types"
                        @change="store.setOrderItemType($event)"
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
                <VhField label="Vendor*">
                    <AutoComplete v-model="store.item.vendor"
                                  @change="store.setOrderItemVendor($event)"
                                  value="id"
                                  class="w-full"
                                  data-testid="productstocks-vh_st_vendor_id"
                                  :suggestions="store.filtered_venders"
                                  @complete="store.searchVendor"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Vendor"
                                  forceSelection
                                  :pt="{
                          token: {
                                    class: 'max-w-full'
                                  },
                          removeTokenIcon: {
                                    class: 'min-w-max'
                          },
                          item: { style:
                                {
                                textWrap: 'wrap'
                                }  },
                          panel: { class: 'w-16rem ' }
                            }">
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}<span v-if="slotProps.option.is_default == 1"> (Default) </span></div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>
                <VhField label="Product*">
                    <AutoComplete  v-model="store.item.product"
                                   @change="store.setOrderItemProduct($event)"
                                  value="id"
                                  class="w-full"
                                  data-testid="productstocks-vh_st_product_id"
                                   :suggestions="store.products"
                                   @complete="store.searchProduct($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Product"
                                  forceSelection
                                  :pt="{
                          token: {
                                    class: 'max-w-full'
                                  },
                          removeTokenIcon: {
                                    class: 'min-w-max'
                          },
                          item: { style:
                                {
                                textWrap: 'wrap'
                                }  },
                          panel: { class: 'w-16rem ' }
                            }">

                    </AutoComplete>
                </VhField>

                <VhField label="Product Variation*">
                    <AutoComplete v-model="store.item.product_variation"
                                  @change="store.setOrderItemProductVariation($event)"
                                  value="id"
                                  class="w-full"
                                  data-testid="productstocks-vh_st_product_variation_id"
                                  :suggestions="store.filtered_product_variations"
                                  @complete="store.searchProductVariation($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Product Variation"
                                  forceSelection
                                  :pt="{
                          token: {
                                    class: 'max-w-full'
                                  },
                          removeTokenIcon: {
                                    class: 'min-w-max'
                          },
                          item: { style:
                                {
                                textWrap: 'wrap'
                                }  },
                          panel: { class: 'w-16rem ' }
                            }">

                    </AutoComplete>
                </VhField>







<!--                <VhField label="Vendor*">-->
<!--                    <AutoComplete-->
<!--                        value="id"-->
<!--                        v-model="store.item.vendor"-->
<!--                        @change="store.setOrderItemVendor($event)"-->
<!--                        class="w-full"-->
<!--                        name="orderitems-vendor"-->
<!--                        :suggestions="store.filtered_venders"-->
<!--                        @complete="store.searchVendor"-->
<!--                        placeholder="Select vendor"-->
<!--                        :dropdown="true" optionLabel="name"-->
<!--                        data-testid="orderitems-vendor"-->
<!--                        forceSelection>-->
<!--                    </AutoComplete>-->

<!--                </VhField>-->

                <VhField label="Customer Groups*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.customer_group"
                        @change="store.setOrderItemCustomerGroup($event)"
                        class="w-full"
                        name="orderitems-customer_group"
                        :suggestions="store.filtered_customer_groups"
                        @complete="store.searchCustomerGroup"
                        placeholder="Select Customer Groups"
                        :dropdown="true" optionLabel="name"
                        data-testid="orderitems-customer_group"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Invoice Url*">
                    <InputText class="w-full"
                               placeholder="Enter a URL"
                               name="orderitems-invoice_url"
                               data-testid="orderitems-invoice_url"
                               v-model="store.item.invoice_url"/>
                </VhField>

                <VhField label="Tracking*">
                    <InputText class="w-full"
                               placeholder="Enter a Tracking"
                               name="orderitems-tracking"
                               data-testid="orderitems-tracking"
                               v-model="store.item.tracking"/>
                </VhField>

                <VhField label="Status*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.status_order_items"
                        @change="store.setOrderItemStatus($event)"
                        class="w-full"
                        name="orders-status"
                        :suggestions="store.status_order_items_suggestion"
                        @complete="store.searchStatusOrderItems($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="orderitems-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="orderitems-status_notes"
                              data-testid="orderitems-status_notes"
                              v-model="store.item.status_notes_order_item"/>
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
                                 name="orders-active"
                                 data-testid="orders-active"
                                 v-model="store.item.is_active_order_item"/>
                </VhField>


            </div>

        </Panel>

    </div>

</template>

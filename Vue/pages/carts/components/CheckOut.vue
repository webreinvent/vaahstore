<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useCartStore} from '../../../stores/store-carts'
import VhField from '../../../vaahvue/vue-three/primeflex/VhField.vue'
import {onMounted, ref, computed, watchEffect, watch} from "vue";
import {useRoute} from "vue-router";
const route = useRoute();
const store = useCartStore();
const useVaah = vaah();
let route_params_id = null;
onMounted(async () => {
    document.title = 'Carts - Check-out';
    if (route.params && route.params.id) {
        route_params_id = route.params;
        await store.getItem(route.params.id);await store.onLoad(route);
        await store.getCartItemDetailsAtCheckout(route.params.id);
    }

    // await store.getList();

});

const orderParams = ref([]);
watch(() => store.is_same_as_shipping, (newValue) => {
    if (Array.isArray(newValue) && newValue.length === 0) {
        store.item_billing_address = null;
    }
});



watchEffect(() => {
    orderParams.value = {
        shipping_address: store.selected_shipping_address,
        total_amount: store.total_mrp - 0,
        payable: store.total_mrp - 0,
        discounts: 2000,
        taxes: 0,
        delivery_fee: 0,
        cart_id:route.params.id ,
        billing_address: store.item_billing_address ? store.item_billing_address : store.selected_billing_address,

        payment_method:store.cash_on_delivery,
        order_items:store.cart_item_at_checkout,
    };
});
</script>

<template>
    <div class="p-3 bg-white border-1 border-gray-200">

        <Button
            @click="store.cartDetails(route_params_id)"
            label="Back"/>
        <div class="flex gap-3 my-3">

            <div class="w-full">
                <Accordion :multiple="true" :activeIndex="[0, 1]" class="w-full">
                    <AccordionTab v-if="store.cart_item_at_checkout && store.cart_item_at_checkout.length" :header="`Products (${store.cart_item_at_checkout.length})`" class="w-full">

                    <div >
                        <DataTable :value="store.cart_item_at_checkout"
                                   dataKey="id"
                                   :rows="5"
                                   :paginator="true"
                                   class="p-datatable-sm p-datatable-hoverable-rows"
                                   :nullSortOrder="-1"
                                   v-model:selection="store.action.items"
                                   stripedRows
                                   responsiveLayout="scroll">
                            <Column field="" header="" class="overflow-wrap-anywhere"
                                    style="width:120px;">
                                <template #body="prop">
                                    <div class="flex mt-4">
                                        <div class="product_img">
                                            <div v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0">
                                                <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">
                                                    <Image preview
                                                           :src="'http://localhost/shivam-g001/store-dev/public/' + imageUrl"
                                                           alt="Error" class="shadow-4" width="64"/>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                     alt="Error" class="shadow-4" width="64"/>
                                            </div>
                                        </div>
                                        <div class="product_desc ml-3">
                                            <h4>{{
                                                    prop.data.pivot.cart_product_variation ? prop.data.name + '-' + prop.data.pivot.cart_product_variation : prop.data.name
                                                }}</h4>
                                            <p v-if="prop.data.pivot.quantity"><b>Qty:</b> {{ prop.data.pivot.quantity }}</p>
                                            <p v-if="prop.data.pivot.price !== null && prop.data.pivot.price !== undefined">
                                                ₹{{ prop.data.pivot.price }}</p>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <template #empty>
                                <div class="text-center py-3">
                                    No records found.
                                </div>
                            </template>

                        </DataTable>

                        </div>
                    </AccordionTab>


                <AccordionTab :header="store.accordionHeader" v-if="(store && store.item && store.item_user && store.item_user_address && store.many_adresses && store.many_adresses.length===0) || store.show_new_address_tab">
                    <div >

                    <VhField label="Country/Region">
                                <AutoComplete v-model="store.item_user_address.country"
                                              value="id"

                                              data-testid="warehouses-country"
                                              :suggestions="store.country_suggestions"
                                              @complete="store.searchCountry($event)"
                                              :dropdown="true"
                                              placeholder="Select Country"
                                              forceSelection />

                            </VhField>

                        <VhField label="Full Name ">
                            <InputText class="w-full"
                                       name="products-name"
                                       data-testid="products-name"
                                       placeholder="Enter Full Name "
                                       v-model="store.item_user_address.name "/>
                        </VhField>

                        <VhField label="Phone No.">
                            <InputText class="w-full"
                                       name="products-phone"
                                       data-testid="products-phone"
                                       placeholder="Enter Phone No."
                                       v-model="store.item_user_address.phone"/>
                        </VhField>
                        <VhField label="Address">
                            <InputText class="w-full"
                                       name="cart-email"
                                       data-testid="cart-email"
                                       placeholder="Enter Address (House No, Building, Street, Area)*"
                                       v-model="store.item_user_address.address_line_1"/>
                        </VhField>

                        <VhField label="PIN Code">
                            <InputText class="w-full"
                                       name="cart-pin_code"
                                       data-testid="cart-pin_code"
                                       placeholder="Enter Pin Code"
                                       v-model="store.item_user_address.pin_code"/>
                        </VhField>

                        <VhField label="City">
                            <InputText class="w-full"
                                       name="cart-city"
                                       data-testid="cart-city"
                                       placeholder="Enter City"
                                       v-model="store.item_user_address.city"/>
                        </VhField>
                        <VhField label="State">
                            <InputText class="w-full"
                                       name="cart-address"
                                       data-testid="cart-address"
                                       placeholder="Enter State / Province / Region"
                                       v-model="store.item_user_address.state"/>
                        </VhField>
                    </div>
                    <div class="flex justify-content-end gap-2">
                        <Button v-if="store.many_adresses.length >= 1" type="button" label="Close" severity="secondary" @click="store.removeTab(index)"></Button>
                        <Button v-if="store.is_editing" type="button" label="Update" @click="store.updateAddress(store.item_user_address,store.item_user)"></Button>
                        <Button v-if="!store.is_editing" type="button" label="Save" @click="store.saveShippingAddress(store.item_user_address, store.item_user, store.show_tab_for_billing ? 'billing' : null)"></Button>
                    </div>
                </AccordionTab>
                <AccordionTab header="Shipping Details " v-if="store && store.item && store.item_user  &&store.many_adresses && store.many_adresses.length >= 1">
                        <div>
                            <template v-for="(address, index) in store.displayedAddresses" :key="index">
                                <Card :class="{ 'selected-card': store.isSelectedShippingAddress(address) }" @click="store.setSelectedShippingAddress(address)" class="mt-2" :pt="{ content: { class: 'py-0' } }">
                                    <template #content>
                                        <div class="flex align-items-center">
                                            <RadioButton v-model="store.selected_shipping_address" :inputId="'address' + index" :name="'address'" :value="address"  />
                                            <label :for="'address' + index" class="ml-2"><b>{{  address.name }}</b></label>
                                        </div>
                                        <div class="p-2">
                                            <p>{{ address.address_line_1 }}, {{ address.city }}</p>
                                            <span>{{ address.country }}</span>
                                        </div>
                                        <div class="p-2">
                                            <span>Mobile: </span><b>{{ address.phone }}</b>
                                        </div>
                                        <li class="p-2" v-if="store.isSelectedShippingAddress(address)">Cash On Delivery Available</li>
                                        <div v-if="store.isSelectedShippingAddress(address)"  class="flex justify-content gap-2 mt-5">
                                            <Button type="button" label="Remove" severity="secondary" @click="store.removeAddress(address)"></Button>
                                            <Button type="button" label="Edit" @click="store.editAddress(address,store.item_user)"></Button>
                                        </div>
                                    </template>
                                </Card>
                            </template>

                        </div>
                        <div class="flex justify-content-between mt-3">
                            <Button icon="pi pi-plus" label="Add a new shipping address"  @click="store.toggleNewAddressTab" link />
                            <Button v-if="store.showViewMoreButton" @click="store.showAllAddresses()" :label="`(${store.remainingAddressCount}) More Address`" :link="true" />
                            <Button v-if="!store.showViewMoreButton && store.many_adresses.length >2" @click=" store.hideAddressTab" :label="` Hide Address`" :link="true" />
                        </div>
                    </AccordionTab>



                    <AccordionTab header="Billing Details ">
                        <div v-if="store.selected_shipping_address" class="flex align-items-center mb-2">
                            <Checkbox  v-model="store.is_same_as_shipping" inputId="sameAsShipping" name="sameAsShipping" value="1" @change="store.handleSameAsShippingChange()" />

                            <label for="ingredient1" class="ml-2">Same as Shipping Details</label>
                        </div>
                        <div v-if="store && store.item && store.item_user && store.user_saved_billing_addresses && store.user_saved_billing_addresses.length >= 1 && (!store.is_same_as_shipping || store.is_same_as_shipping.length === 0)">
                            <template v-for="(address, index) in store.displayedBillingAddresses" :key="index">
                                <Card :class="{ 'selected-card': store.isSelectedBillingAddress(address) }" @click="store.setSelectedBillingAddress(address)" class="mt-2" :pt="{ content: { class: 'py-0' } }">
                                    <template #content>
                                        <div class="flex align-items-center">
                                            <RadioButton v-model="store.selected_billing_address" :inputId="'address' + index" :name="'address'" :value="address"  />
                                            <label :for="'address' + index" class="ml-2"><b>{{  address.name }}</b></label>
                                        </div>
                                        <div class="p-2">
                                            <p>{{ address.address_line_1 }}, {{ address.city }}</p>
                                            <span>{{ address.country }}</span>
                                        </div>
                                        <div class="p-2">
                                            <span>Mobile: </span><b>{{ address.phone }}</b>
                                        </div>
                                        <div v-if="store.isSelectedBillingAddress(address)"  class="flex justify-content gap-2 mt-5">
                                            <Button type="button" label="Remove" severity="secondary" @click="store.removeAddress(address)"></Button>
                                            <Button type="button" label="Edit" @click="store.editAddress(address,store.item_user)"></Button>
                                        </div>
                                    </template>
                                </Card>
                            </template>

                        </div>
                        <div class="flex justify-content-between mt-3">
                            <Button icon="pi pi-plus" label="Add a new billing address"  @click="store.toggleNewAddressTabForBilling('billing')" link />
                            <Button v-if="store.showViewMoreBillingAddressButton" @click="store.showAllBillingAddresses()" :label="`(${store.remainingAddressCountBilling}) More Address`" :link="true" />
                            <Button v-if="!store.showViewMoreBillingAddressButton &&  store.user_saved_billing_addresses && store.user_saved_billing_addresses.length >2" @click=" store.hideBillingAddressTab" :label="` Hide Address`" :link="true" />
                        </div>
                    </AccordionTab>
                    <AccordionTab header="Payment">
                        <div class="flex flex-column px-4 gap-2 max-w-14rem">
                            <label for="payment_type" class="cursor-pointer flex align-items-center bg-gray-100 p-2 border-round">
                                    <RadioButton v-model="store.cash_on_delivery" inputId="COD" name="COD" value="COD"/>
                                    <span class="ml-2">Cash On Delivery</span>
                                </label>
                        </div>
                    </AccordionTab>
                </Accordion>
            </div>
            <div>
                <Card class="border-1 border-gray-200 w-25rem" :pt="{content: {class: 'pb-0'} }">
                    <template #title>Check Summary</template>
                    <template #content>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Total MRP :</b>
                            </p>
                            <p class="m-0">
                                ₹{{ store.total_mrp }}
                            </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Delivery :</b>
                            </p>
                            <p class="text-teal-500">
                                Free
                            </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                               <b>Tax :</b>
                            </p>
                            <p>
                                ₹0
                             </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Discount On MRP:</b>
                            </p>
                            <p class="text-teal-500">
                                -₹0
                            </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Coupon Discount :</b>
                            </p>
                            <p><Button  label="Apply Coupon" link /></p>
                        </div>
                        <hr>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Total Amount :</b>
                            </p>
                            <p>
                                <b>₹{{ store.total_mrp - 0 }}</b>
                            </p>
                        </div>

                        <div class="text-center">
                            <Button label="Place an order" @click="store.placeOrder(orderParams)" class="bg-blue-700 text-white p-2 mt-3 border-round w-full"/>
                        </div>
                    </template>
                </Card>

                <div class="table_bottom mt-4 border-1 border-gray-200">
                    <InputText v-model="coupon_code" placeholder="Enter Coupon code" class="w-full"/>
                </div>
            </div>
        </div>

    </div>

</template>

<style scoped>
.selected-card {
    height: 250px;
    /* Add any additional styles as needed */
}

</style>

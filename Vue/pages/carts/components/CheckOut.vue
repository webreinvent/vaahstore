<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useCartStore} from '../../../stores/store-carts'
import VhField from '../../../vaahvue/vue-three/primeflex/VhField.vue'
import {onMounted,ref,computed} from "vue";
import {useRoute} from "vue-router";
const route = useRoute();
const store = useCartStore();
const useVaah = vaah();
let routeParamsId = null;
onMounted(async () => {
    document.title = 'Carts - Check-out';
    if (route.params && route.params.id) {
        routeParamsId = route.params;
        await store.getItem(route.params.id);await store.onLoad(route);
        await store.getCartItemDetailsAtCheckout(route.params.id);
    }

    // await store.getList();

});

// const selectedAddress = ref(null);
// const showAll = ref(false);
//
// const displayedAddresses = computed(() => {
//     return showAll.value ? store.many_adresses : store.many_adresses.slice(0, 2);
// });
//
// const showViewMoreButton = computed(() => {
//     return !showAll.value && store.many_adresses.length >= 3;
// });
// const remainingAddressCount = computed(() => {
//     return store.many_adresses.length - 2;
// });
//
// const showAllAddresses = () => {
//     showAll.value = true;
// };
// const hideAddressTab = () => {
//     showAll.value = !showAll.value;
// };
// const isSelectedAddress = (address) => {
//     return address === selectedAddress.value;
// };


// const saveAddress=()=>{
//     if (store.editingAddress) {
//         store.item_user_address.id = store.editingAddress.id;
//     }
//     store.saveCartUserAddress(store.item_user_address,store.new_user_at_shipping);
// };
</script>

<template>
    <div class="p-3 bg-white border-1 border-gray-200">

        <Button
            @click="store.cartDetails(routeParamsId)"
            label="Back"/>
        <div class="flex gap-3 my-3">

            <div class="w-full">
                <Accordion :multiple="true" :activeIndex="[0, 1]" class="w-full">
                    <AccordionTab v-if="store.cart_item_at_checkout && store.cart_item_at_checkout.length" :header="`Products (${store.cart_item_at_checkout.length})`" class="w-full">

                    <div >
                            <div v-for="(product, index) in store.cart_item_at_checkout" :key="index" class="flex">
                                <div class="product_img">
                                    <div v-if="Array.isArray(product.image_urls) && product.image_urls.length > 0">
                                        <div v-for="(imageUrl, imgIndex) in product.image_urls" :key="imgIndex">
                                            <Image preview :src="'http://localhost/shivam-g001/store-dev/public/' + imageUrl" alt="Error" class="shadow-4" width="64" />
                                        </div>
                                    </div>
                                    <div v-else>
                                        <Image preview :src="'http://localhost/shivam-g001/store-dev/public/' + product.image_urls" alt="Error" class="shadow-4" width="64" />
                                    </div>
                                </div>
                                <div class="product_desc ml-3">
                                    <h4>{{ product.pivot.cart_product_variation ? product.name + '-' + product.pivot.cart_product_variation : product.name }}</h4>

                                    <!-- Replace static price with dynamic data if available -->
                                    <p v-if="product.pivot.price"><b>Price: </b>{{ product.pivot.price }}</p>
                                    <!-- Replace static quantity with dynamic data if available -->
                                    <p v-if="product.pivot.quantity"><b>Qty:</b> {{ product.pivot.quantity }}</p>
                                </div>
                            </div>
                        </div>
                    </AccordionTab>


                <AccordionTab header="Shipping Details (No Address)" v-if="(store && store.item && store.item_user&& store.new_user_at_shipping && store.item_user_address && store.many_adresses && store.many_adresses.length===0) || store.shouldShowNewAddressTab">
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
                        <!-- Add your Remove and Edit buttons here -->
<!--                        <Button type="button" label="Remove" severity="secondary" @click="removeAddress(index)"></Button>-->
<!--                        <Button type="button" label="Save" @click="store.saveCartUserAddress(store.item_user_address, store.item_user.id)"></Button>-->
                        <Button v-if="store.isEditing" type="button" label="Update" @click="store.updateAddress(store.item_user_address,store.item_user)"></Button>

                        <Button v-if="!store.isEditing" type="button" label="Save" @click="store.saveShippingAddress(store.item_user_address,store.item_user)"></Button>

                    </div>
                </AccordionTab>
                <AccordionTab header="Shipping Details" v-if="store && store.item && store.item_user && store.user_address &&store.many_adresses && store.many_adresses.length >= 1">
                        <div>
                            <!-- Iterate over user addresses, limit to 3 initially -->
                            <template v-for="(address, index) in store.displayedAddresses" :key="index">
                                <Card :class="{ 'selected-card': store.isSelectedAddress(address) }" @click="store.setSelectedAddress(address)" class="mt-2" :pt="{ content: { class: 'py-0' } }">
                                    <template #content>
                                        <div class="flex align-items-center">
                                            <!-- Use RadioButton for address selection if needed -->
                                            <RadioButton v-model="store.selectedAddress" :inputId="'address' + index" :name="'address'" :value="address"  />
                                            <label :for="'address' + index" class="ml-2"><b>{{  address.name }}</b></label>
                                        </div>
                                        <div class="p-2">
                                            <p>{{ address.address_line_1 }}, {{ address.city }}</p>
                                            <span>{{ address.country }}</span>
                                        </div>
                                        <div class="p-2">
                                            <span>Mobile: </span><b>{{ address.phone }}</b>
                                        </div>
                                        <li class="p-2" v-if="store.isSelectedAddress(address)">Pay On Delivery Available</li>
                                        <div v-if="store.isSelectedAddress(address)"  class="flex justify-content gap-2 mt-5">
                                            <!-- Add your Remove and Edit buttons here -->
                                            <Button type="button" label="Remove" severity="secondary" @click="store.removeAddress(address)"></Button>
                                            <Button type="button" label="Edit" @click="store.editAddress(address,store.item_user)"></Button>
                                        </div>
                                    </template>
                                </Card>
                            </template>

                        </div>
                        <div class="flex justify-content-between mt-3">
                            <Button icon="pi pi-plus" label="Add a new addresses"  @click="store.toggleNewAddressTab" link />
                            <Button v-if="store.showViewMoreButton" @click="store.showAllAddresses" :label="`(${store.remainingAddressCount}) More Address`" :link="true" />
                            <Button v-if="!store.showViewMoreButton && store.many_adresses.length >2" @click=" store.hideAddressTab" :label="` Hide Address`" :link="true" />
                        </div>
                    </AccordionTab>





                <AccordionTab header="Billing Details" >
                        <div>
                                <div class="flex align-items-center mb-2">
<!--                                    <Checkbox v-model="store.bill_form" inputId="ingredient1" name="bill_form" value="1" />-->
                                    <Checkbox v-model="store.bill_form" inputId="sameAsShipping" name="sameAsShipping" value="1" @change="store.handleSameAsShippingChange()" />

                                    <label for="ingredient1" class="ml-2">Same as Shipping Details</label>
                                </div>
                            <div v-show="!store.bill_form?.length==1">
                                <div v-if="store && store.item && store.item_user_address && store.item_user">
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
                                <div >
                                    <VhField label="Full Name ">
                                        <InputText class="w-full"
                                                   name="products-name"
                                                   data-testid="products-name"
                                                   placeholder="Enter Full Name "
                                                   v-model="store.item_user.name"/>
                                    </VhField>

                                    <VhField label="Phone No.">
                                        <InputText class="w-full"
                                                   name="products-phone"
                                                   data-testid="products-phone"
                                                   placeholder="Enter Phone No."
                                                   v-model="store.item_user.phone"/>
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
                                </div>
                            </div>
                        </div>
                    <div class="flex justify-content-end gap-2">

                        <!--                        <Button type="button" label="Save" @click="store.saveCartUserAddress(store.item_user_address, store.item_user.id)"></Button>-->
                        <Button type="button" label="Save" @click="store.saveBillingAddress(store.item_user_address, store.item_user)"></Button>

                    </div>
                    </AccordionTab>



                    <AccordionTab header="Payment">
                        <div class="flex flex-column px-4 gap-2 max-w-14rem">
                            <label for="ingredient" class="cursor-pointer flex align-items-center bg-gray-100 p-2 border-round">
                                <RadioButton v-model="ingredient" inputId="ingredient1" name="pizza" value="Cheese"/>
                                <span class="ml-2">Gpay</span>
                            </label>
                                <label for="ingredient1" class="cursor-pointer flex align-items-center bg-gray-100 p-2 border-round">
                                    <RadioButton v-model="ingredient" inputId="ingredient1" name="pizza" value="Cheese"/>
                                    <span class="ml-2">Cash On Delivery</span>
                                </label>
                        </div>
                    </AccordionTab>
                </Accordion>
            </div>
            <div>
                <Card class="border-1 border-gray-200 w-20rem" :pt="{content: {class: 'pb-0'} }">
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
                                -₹2000
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
                                <b>₹{{ store.total_mrp - 2000 }}</b>
                            </p>
                        </div>

                        <div class="text-center">
                            <Button label="Place an order" class="bg-blue-700 text-white p-2 mt-3 border-round w-full"/>
                        </div>
                    </template>
                </Card>

                <div class="table_bottom mt-4 border-1 border-gray-200">
                    <InputText v-model="store.item" placeholder="Enter Coupon code" class="w-full"/>
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

<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useCartStore} from '../../../stores/store-carts'
import VhField from '../../../vaahvue/vue-three/primeflex/VhField.vue'

const store = useCartStore();
const useVaah = vaah();

</script>

<template>
    <div class="p-3 bg-white border-1 border-gray-200">

        <Button
            @click="store.cartDetails({id:1})"
            label="Back"/>
        <div class="flex gap-3 my-3">

            <div class="w-full">
                <Accordion :multiple="true" :activeIndex="[0]" class="w-full">
                    <AccordionTab header="Products (count of product)" class="w-full">
                       <div class="flex ">
                           <div class="product_img">
                               <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                    alt="Error" class="shadow-4" width="64" />
                           </div>
                           <div class="product_desc ml-3">
                               <h4>
                                   Nokia 2660 Flip 4G Volte keypad Phone, Pop Pink
                               </h4>
                               <p><b>Price: </b>₹4000 <s class="text-300">₹6000</s></p>
                               <p><b>Qty:</b> 1</p>
                           </div>
                       </div>
                    </AccordionTab>
                    <AccordionTab header="Shipping Details">
                        <div>
                            <VhField label="Country/Region">
                                <Dropdown v-model="store.item"
                                          :options="store.assets"
                                          optionLabel="name"
                                          placeholder="Select a Country/Region"
                                          class="w-full md:w-14rem" />

                            </VhField>

                            <VhField label="Full Name ">
                            <InputText class="w-full"
                                       name="products-name"
                                       data-testid="products-name"
                                       placeholder="Enter Full Name "
                                       v-model="store.item"/>
                            </VhField>

                            <VhField label="Phone No.">
                                <InputText class="w-full"
                                           name="products-phone"
                                           data-testid="products-phone"
                                           placeholder="Enter Phone No."
                                           v-model="store.item"/>
                            </VhField>
                            <VhField label="Address">
                                <InputText class="w-full"
                                           name="cart-email"
                                           data-testid="cart-email"
                                           placeholder="Enter Address (House No, Building, Street, Area)*"
                                           v-model="store.item"/>
                            </VhField>

                            <VhField label="PIN Code">
                                <InputText class="w-full"
                                           name="cart-pin_code"
                                           data-testid="cart-pin_code"
                                           placeholder="Enter Pin Code"
                                           v-model="store.item"/>
                            </VhField>

                            <VhField label="City">
                                <InputText class="w-full"
                                           name="cart-city"
                                           data-testid="cart-city"
                                           placeholder="Enter City"
                                           v-model="store.item"/>
                            </VhField>
                            <VhField label="State">
                                <InputText class="w-full"
                                           name="cart-address"
                                           data-testid="cart-address"
                                           placeholder="Enter State / Province / Region"
                                           v-model="store.item"/>
                            </VhField>
                            <div class="flex justify-content-between">
                                <Button icon="pi pi-plus" label="Add a new address" link />
                                <Button label="2 More Address" link />
                            </div>
                        </div>
                    </AccordionTab>
                    <AccordionTab header="Billing Details">
                        <div>
                                <div class="flex align-items-center mb-2">
                                    <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
                                    <label for="ingredient1" class="ml-2">Same as Shipping Details</label>
                                </div>
                            <VhField label="Country/Region">
                                <Dropdown v-model="store.item"
                                          :options="store.assets"
                                          optionLabel="name"
                                          placeholder="Select a Country/Region"
                                          class="w-full md:w-14rem" />

                            </VhField>

                            <VhField label="Full Name ">
                                <InputText class="w-full"
                                           name="products-name"
                                           data-testid="products-name"
                                           placeholder="Enter Full Name "
                                           v-model="store.item"/>
                            </VhField>

                            <VhField label="Phone No.">
                                <InputText class="w-full"
                                           name="products-phone"
                                           data-testid="products-phone"
                                           placeholder="Enter Phone No."
                                           v-model="store.item"/>
                            </VhField>
                            <VhField label="Address">
                                <InputText class="w-full"
                                           name="cart-email"
                                           data-testid="cart-email"
                                           placeholder="Enter Address (House No, Building, Street, Area)*"
                                           v-model="store.item"/>
                            </VhField>

                            <VhField label="PIN Code">
                                <InputText class="w-full"
                                           name="cart-pin_code"
                                           data-testid="cart-pin_code"
                                           placeholder="Enter Pin Code"
                                           v-model="store.item"/>
                            </VhField>

                            <VhField label="City">
                                <InputText class="w-full"
                                           name="cart-city"
                                           data-testid="cart-city"
                                           placeholder="Enter City"
                                           v-model="store.item"/>
                            </VhField>
                            <VhField label="State">
                                <InputText class="w-full"
                                           name="cart-address"
                                           data-testid="cart-address"
                                           placeholder="Enter State / Province / Region"
                                           v-model="store.item"/>
                            </VhField>
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
                                ₹6000
                            </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Delivery :</b>
                            </p>
                            <p>
                                ₹120
                            </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                               <b>Tax :</b>
                            </p>
                            <p>
                                ₹200
                             </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Discount :</b>
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
                                <b>₹4020</b>
                            </p>
                        </div>

                        <div class="text-center">
                            <Button label="Place an order" class="bg-blue-700 text-white p-2 mt-3 border-round w-full"/>
                        </div>
                    </template>
                </Card>

                <div class="table_bottom mt-4 border-1 border-gray-200">
                    <InputText v-model="store.item" placeholder="Enter promo code" class="w-full"/>
                </div>
            </div>
        </div>

    </div>

</template>

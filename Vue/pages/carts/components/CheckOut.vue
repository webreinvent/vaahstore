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
                           </div>
                       </div>
                    </AccordionTab>
                    <AccordionTab header="Shipping Details">
                        <div>
                            <VhField label="Name">
                            <InputText class="w-full"
                                       name="products-name"
                                       data-testid="products-name"
                                       placeholder="Enter Name"
                                       v-model="store.item"/>
                            </VhField>
                            <VhField label="Email">
                                <InputText class="w-full"
                                           name="cart-email"
                                           data-testid="cart-email"
                                           placeholder="Enter Email"
                                           v-model="store.item"/>
                            </VhField>
                            <VhField label="Phone">
                                <InputText class="w-full"
                                           name="cart-phone"
                                           data-testid="cart-phone"
                                           placeholder="Enter Name"
                                           v-model="store.item"/>
                            </VhField>
                            <VhField label="Address">
                                <InputText class="w-full"
                                           name="cart-address"
                                           data-testid="cart-address"
                                           placeholder="Enter Address"
                                           v-model="store.item"/>
                            </VhField>
                        </div>
                    </AccordionTab>
                    <AccordionTab header="Billing Details">
                        <div>
                            <VhField label="Name">
                                <InputText class="w-full"
                                           name="products-name"
                                           data-testid="products-name"
                                           placeholder="Enter Name"
                                           v-model="store.item"/>
                            </VhField>
                            <VhField label="Email">
                                <InputText class="w-full"
                                           name="cart-email"
                                           data-testid="cart-email"
                                           placeholder="Enter Email"
                                           v-model="store.item"/>
                            </VhField>
                            <VhField label="Phone">
                                <InputText class="w-full"
                                           name="cart-phone"
                                           data-testid="cart-phone"
                                           placeholder="Enter Name"
                                           v-model="store.item"/>
                            </VhField>
                            <VhField label="Address">
                                <InputText class="w-full"
                                           name="cart-address"
                                           data-testid="cart-address"
                                           placeholder="Enter Address"
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
                        <p class="m-0">
                            Total
                        </p>
                        <p class="m-0">
                            Tax
                        </p>
                        <p class="m-0">
                            discount
                        </p>
                        <div class="text-center">
                            <Button label="Place an order" class="bg-blue-700 text-white p-2 mt-3 border-round w-full"/>
                        </div>
                    </template>
                </Card>

                <div class="table_bottom mt-4 border-1 border-gray-200">
                    <InputNumber v-model="store" inputId="integeronly" class="w-full"/>
                </div>
            </div>
        </div>

    </div>

</template>

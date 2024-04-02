<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useCartStore} from '../../../stores/store-carts'

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
                <Accordion :activeIndex="0" class="w-full">
                    <AccordionTab header="Products" class="w-full">
                        <p class="m-0">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                            labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
                            laboris nisi ut aliquip ex ea commodo
                            consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                            fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui
                            officia deserunt mollit anim id est
                            laborum.
                        </p>
                    </AccordionTab>
                    <AccordionTab header="Shipping Details">
                        <p class="m-0">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque
                            laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi
                            architecto beatae vitae dicta sunt explicabo. Nemo
                            enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur
                            magni dolores eos qui ratione voluptatem sequi nesciunt. Consectetur, adipisci velit, sed
                            quia non numquam eius modi.
                        </p>
                    </AccordionTab>
                    <AccordionTab header="Billing Details">
                        <p class="m-0">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque
                            laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi
                            architecto beatae vitae dicta sunt explicabo. Nemo
                            enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur
                            magni dolores eos qui ratione voluptatem sequi nesciunt. Consectetur, adipisci velit, sed
                            quia non numquam eius modi.
                        </p>
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
                <Card class="border-1 border-gray-200 w-20rem">
                    <template #title>Simple Card</template>
                    <template #content>
                        <p class="m-0">
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore sed consequuntur error
                            repudiandae numquam deserunt quisquam repellat libero asperiores earum nam nobis, culpa
                            ratione quam perferendis esse, cupiditate neque
                            quas!
                        </p>
                    </template>
                </Card>
            </div>
        </div>
        <Button label="Place an order" class="bg-blue-700 text-white p-2"/>
    </div>

</template>

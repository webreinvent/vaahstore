<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useCartStore} from '../../../stores/store-carts'
import {onMounted, ref, computed, watchEffect, watch} from "vue";
import {useRoute} from "vue-router";
const route = useRoute();
const store = useCartStore();
const useVaah = vaah();
onMounted(async () => {
    document.title = 'Carts - Order-details';
    if (route.params && route.params.order_id) {

        await store.getOrderDetails(route.params.order_id);
    }


});
</script>




<template>
    <div class="p-3 bg-white border-1 border-gray-200">
        <h1 class="text-center">Thanks for your order</h1>
        <div class="flex gap-3 my-3">
            <div class="w-full">

<!--                <div class="flex justify-center md:justify-end">-->
                    <div v-for="(product, index) in store.ordered_product" :key="index" class="flex">
                        <div class="product_img">
                            <div v-if="Array.isArray(product.image_urls) && product.image_urls.length > 0">
                                <div v-for="(imageUrl, imgIndex) in product.image_urls" :key="imgIndex">
                                    <Image preview :src="'http://localhost/shivam-g001/store-dev/public/' + imageUrl" alt="Error" class="shadow-4" width="100" />
                                </div>
                            </div>
                            <div v-else>
                                <!--                                        <Image preview :src="'http://localhost/shivam-g001/store-dev/public/vaahcms/backend/vaahone/images/vaahcms-logo.svg'" alt="Error" class="shadow-4" width="64" />-->
                                <img src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                     alt="Error" class="shadow-4" width="64" />
                            </div>
                        </div>
                        <div class="product_desc ml-3">
                            <h4>{{ product.pivot.cart_product_variation ? product.name + '-' + product.pivot.cart_product_variation : product.name }}</h4>

                            <p v-if="product.pivot.price !== null && product.pivot.price !== undefined"><b>Price: </b>{{ product.pivot.price }}</p>
                            <p v-if="product.pivot.quantity"><b>Qty:</b> {{ product.pivot.quantity }}</p>
                        </div>
                    </div>
<!--                </div>-->

                    <Card>
                        <template #content v-if="store && store.ordered_shipping_address">
                            <h3 class="text-center">Your Address</h3>
                            <div class="flex align-items-center">
                                <label  class="ml-2"><b>{{ store.ordered_shipping_address.name }}</b></label>
                            </div>
                            <div class="p-2">
                                <p>{{ store.ordered_shipping_address.address_line_1 }}</p>
                                <span>{{ store.ordered_shipping_address.city }}, {{ store.ordered_shipping_address.country }}
                                    - {{ store.ordered_shipping_address.pin_code }}</span>
                            </div>
                            <div class="p-2">
                                <span>Mobile: </span><b>{{store.ordered_shipping_address.phone }}</b>
                            </div>

                        </template>
                    </Card>
            </div>
        <div class="mx-auto max-w-2xl px-4 2xl:px-0">
            <div class="flex flex-wrap justify-between">

                <div class="w-full md:w-1/2 mb-6 md:mb-8">

                    <Card class="border-1 border-gray-200 w-20rem " :pt="{content: {class: 'pb-0'} }">
                        <template #title>Order Summary</template>
                        <!-- Order summary content -->
                        <template #content>
                            <div class="flex justify-content-between">
                                <p class="m-0">
                                    <b>Total MRP :</b>
                                </p>
                                <p class="m-0">
                                    ₹{{ store.ordered_total_mrp }}
                                </p>
                            </div>
<!--                            <div class="flex justify-content-between">-->
<!--                                <p class="m-0">-->
<!--                                    <b>Delivery :</b>-->
<!--                                </p>-->
<!--                                <p class="text-teal-500">-->
<!--                                    Free-->
<!--                                </p>-->
<!--                            </div>-->
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
                                                            <p>-₹0</p>
                            </div>
                            <hr>
                            <div class="flex justify-content-between">
                                <p class="m-0">
                                    <b>Total Amount :</b>
                                </p>
                                <p>
                                    <b>₹{{ store.ordered_total_mrp - 0 }}</b>
                                </p>
                            </div>

                            <div class="text-center">
                                <!--                            <Button label="Place an order" class="bg-blue-700 text-white p-2 mt-3 border-round w-full"/>-->
                            </div>
                        </template>
                    </Card>

                </div>
            </div> <div class="flex items-center space-x-4">
            <a href="#" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Track your order</a>
            <a href="#" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Return to shopping</a>
        </div>
        </div>
        </div>
    </div>
</template>

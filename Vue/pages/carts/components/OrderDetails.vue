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
    await store.loadAssets();

});
</script>


<template>
    <div class="p-3 bg-white border-1 border-gray-200">

        <div class="flex gap-3 my-3">
            <div class="w-full">
                <h1 class="text-center font-bold text-xl mb-3">Thank you for your purchase!</h1>
                <p class="text-center font-bold">Your order will be delivered in 2 days!</p>

                <DataTable :value="store.ordered_product"
                           dataKey="id"

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
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="" header="" class="overflow-wrap-anywhere"
                            style="width:120px;">
                        <template #body="prop">
                            <p v-if="prop.data.pivot.price !== null && prop.data.pivot.price !== undefined">
                                <span v-html="store.assets?.store_default_currency"></span>{{ prop.data.pivot.price }}</p>
                        </template>
                    </Column>


                    <template #empty>
                        <div class="text-center py-3">
                            No records found.
                        </div>
                    </template>

                </DataTable>

            </div>
            <div class="mx-auto max-w-2xl px-4 2xl:px-0">
                <div class="flex justify-content-center  items-center">
                    <Button @click="store.returnToProduct()" data-testid="order-details-to_product" size="small" label="Return To Shopping" class="w-full"/>
                    <Button label="Track Your Order" size="small" data-testid="order-details-to_order" @click="store.toOrderView(store.order.id)" severity="secondary" outlined class="w-full"/>
                </div>
                <div class="flex flex-wrap justify-between">

                    <div class="w-full md:w-1/2 mb-6 md:mb-8">

                        <Card class="border-1 border-gray-200 w-20rem mt-5" :pt="{content: {class: 'pb-0'} }">

                            <template #title> Order Summary </template>

                            <template #subtitle>#{{ store.order?.uuid }}
                                <br>
                                <div class="flex justify-content-between">
                                    <p class="m-0">
                                        <b>Ordered At :</b>
                                    </p>
                                    <p class="m-0">
                                        {{ store.formatDate(store.ordered_at) }}
                                    </p>
                                </div>
                                <div class="flex justify-content-between">
                                    <p class="m-0">
                                        <b>Total Amount Paid :</b>
                                    </p>
                                    <p class="m-0"><span v-html="store.assets?.store_default_currency"></span>{{store.order_paid_amount}}

                                    </p>
                                </div>
                            </template>
                            <template #content>
                                <div class="flex justify-content-between">
                                    <p class="m-0">
                                        <b>Total MRP :</b>
                                    </p>
                                    <p class="m-0">
                                        <span v-html="store.assets?.store_default_currency"></span>{{ store.ordered_total_mrp }}
                                    </p>
                                </div>

                                <div class="flex justify-content-between">
                                    <p class="m-0">
                                        <b>Tax :</b>
                                    </p>
                                    <p>
                                        <span v-html="store.assets?.store_default_currency"></span>0
                                    </p>
                                </div>
                                <div class="flex justify-content-between">
                                    <p class="m-0">
                                        <b>Discount On MRP:</b>
                                    </p>
                                    <p class="text-teal-500">
                                        -<span v-html="store.assets?.store_default_currency"></span>0
                                    </p>
                                </div>
                                <div class="flex justify-content-between">
                                    <p class="m-0">
                                        <b>Coupon Discount :</b>
                                    </p>
                                    <p>-<span v-html="store.assets?.store_default_currency"></span>0</p>
                                </div>
                                <hr>
                                <div class="flex justify-content-between">
                                    <p class="m-0">
                                        <b>Total Amount :</b>
                                    </p>
                                    <p>
                                        <b><span v-html="store.assets?.store_default_currency"></span>{{ store.ordered_total_mrp - 0 }}</b>
                                    </p>
                                </div>
                            </template>
                        </Card>

                        <Card class="border-1 border-gray-100 w-20rem mt-5" :pt="{content: {class: 'pb-0'} }">
                            <template #title>Shipping Address</template>
                            <template #content v-if="store && store.ordered_shipping_address">
                                <div class="flex align-items-center">
                                    <label class="ml-2"><b>{{ store.ordered_shipping_address.name }}</b></label>
                                </div>
                                <div class="p-2">
                                    <p>{{ store.ordered_shipping_address.address_line_1 }}</p>
                                    <span>{{
                                            store.ordered_shipping_address.city
                                        }}, {{ store.ordered_shipping_address.country }}
                                    - {{ store.ordered_shipping_address.pin_code }}</span>
                                </div>
                                <div class="p-2">
                                    <span>Mobile: </span><b>{{ store.ordered_shipping_address.phone }}</b>
                                </div>

                            </template>
                        </Card>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<template>
    <div>
        <div id="paypal-button-container"></div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import axios from 'axios'
import {useCartStore} from "../../../stores/store-carts";
const store = useCartStore();


// Load PayPal SDK and initialize button
onMounted(() => {
    const script = document.createElement('script')
    script.src = 'https://www.paypal.com/sdk/js?client-id=AQt11HNM4lvgR0h_DNEWcatbn0OTA5cwjBXj0J8EqlqpYlcHEATcNWBI1N3TH4PSh1fTW9B8DILeNI4I&currency=USD'
    script.addEventListener('load', initPayPalButton)
    document.body.appendChild(script)
})
const props = defineProps({
    orderParams: Object // accept from parent
})
function initPayPalButton() {
    paypal.Buttons({
        createOrder: () => {
            return new Promise((resolve, reject) => {
                // Save resolver to be used after async request
                store.paypal_order_resolver = resolve;
                store.paypalOrderRejecter = reject;

                // Call your method to create PayPal order
                store.createPaypalOrder(props.orderParams);
            });
        },

        onApprove: (data) => {
            store.capturePaypalOrder(data.orderID);
        },

        onError: (err) => {
            if (store.paypalOrderRejecter) {
                store.paypalOrderRejecter(err);
                store.paypalOrderRejecter = null;
            }
        }
    }).render('#paypal-button-container');
}

</script>

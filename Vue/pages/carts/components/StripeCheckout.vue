<template>
    <div>
        <div ref="cardElement" class="border p-3 rounded mb-4 " />
        <Button
            @click="handlePayment"
            :disabled="processing"
            :pt="{ root: { class: '!bg-[#0E9F6E] !py-2 !rounded-lg' } }"
            class="bg-blue-700 text-white p-2 mt-3 border-round "
        >
            <span v-if="processing">Processing...</span>
            <span v-else>
                Pay <span v-html="root.selected_store_at_sidebar?.default_currency.symbol"></span>{{ store.total_mrp }}
            </span>
        </Button>
        <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
    </div>
</template>

<script setup >
import { loadStripe } from '@stripe/stripe-js'
import { onMounted, ref } from 'vue'
const store = useCartStore();
// const stripePromise = loadStripe('pk_test_51RqCq6BXcn39MG7PtP6xQMBEMTmBRstCb1z1mU843FtvljwrYoULF4eAwM2Gru0utUH9QAsXIDs0fZ3XlvowbOCU00rtuDG9sg') // Your publishable key
const base_url = document.getElementsByTagName('base')[0]?.getAttribute('href') || ''
const ajax_url = `${base_url}/store/carts`
const cardElement = ref()
const stripe = ref(null)
const elements = ref(null)
const card = ref(null)
const root = useRootStore();
const error = ref('')
const processing = ref(false)
const props = defineProps({
    orderParams: Object // accept from parent
})
let stripePromise = null
onMounted(async () => {
    const { data } = await axios.get(`${ajax_url}/stripe/publishable-key`)
    stripePromise = loadStripe(data.data.key)
    stripe.value = await stripePromise
    elements.value = stripe.value.elements()
    card.value = elements.value.create('card')
    card.value.mount(cardElement.value)
})

import axios from 'axios'
import {useCartStore} from "../../../stores/store-carts";
import {useRootStore} from "../../../stores/root";

const handlePayment = async () => {
    error.value = ''
    processing.value = true
    try {
        // Get client secret from your  backend
        const response = await axios.post(`${ajax_url}/stripe/payment-intent`, {
            order_details: props.orderParams
        })
        const res = response.data;
        const result = await stripe.value.confirmCardPayment(res.data.clientSecret, {
            payment_method: {
                card: card.value,
            },
        })
        if (result.error) {
            error.value = result.error.message
        } else if (result.paymentIntent.status === 'succeeded') {
            //  Success! Redirect or show confirmation
            const confirmRes = await axios.post(`${ajax_url}/stripe/confirm-payment`, {
                payment_intent_id: result.paymentIntent.id,
            });
            store.orderConfirmation(confirmRes.data.data);
        }
    } catch (err) {
        error.value = err.response?.data?.message || err.message || 'Payment failed'
    } finally {
        processing.value = false
    }
}
</script>

<template>
  <div class="bg-gray-100 rounded-lg w-full">
      <div v-if="!paymentMethods || paymentMethods.length === 0" class="text-center text-gray-600 py-4 flex flex-col items-center">
          <i class="pi pi-exclamation-circle text-4xl text-gray-400 mb-3"></i>
          No payment methods are available at this time. Please contact support or try again later.
      </div>
    <div v-else class="flex space-x-2 mb-4">
      <Button
        v-for="method in paymentMethods"
        :key="method.id"
        :class="{
          'border !border-[#1C64F2] !text-[#1C64F2] bg-blue-50': selectedMethod === method.slug,
          '!border-none  !text-gray-500': selectedMethod !== method.slug,
        }"
        class="flex-1 bg-gray-100 flex flex-col !items-start !justify-start p-3 border !rounded-xl"
        @click="selectedMethod = method.slug"
        type="button"
      >
        <div class="flex items-center gap-2">
          <input
            type="radio"
            :value="method.slug"
            v-model="selectedMethod"
            @click.stop
          />
          <Icon :icon="method.icon" width="24" height="24" />
          <p>{{ method.name }}</p>
        </div>
      </Button>
    </div>

    <div v-if="selectedMethod === 'credit-card' || selectedMethod === 'debit-card'">
      <label class="block font-normal text-xs text-gray-400 mb-1">Card Number</label>
      <InputText
        v-model="cardNumber"
        class="font-bold text-sm text-gray-950 w-full p-2 border rounded-lg mb-2"
        placeholder="1234 1234 1234 1234"
      />
      <div class="flex space-x-2">
        <div class="flex-1">
          <label class="block font-normal text-xs text-gray-400 mb-1">Expiry</label>
          <InputText
            v-model="expiry"
            class="font-bold text-sm text-gray-950 w-full p-2 border rounded-lg"
            placeholder="MM/YY"
          />
        </div>
        <div class="flex-1">
          <label class="block font-normal text-xs text-gray-400 mb-1">CVC</label>
          <InputText
            v-model="cvc"
            class="font-bold text-sm text-gray-950 w-full p-2 border rounded-lg"
            placeholder="000"
          />
        </div>
      </div>
    </div>


  </div>
</template>

<script setup>
import { ref, watch } from "vue";
const emit = defineEmits(['update:paymentMethod']);

const props = defineProps({
  paymentMethods: {
    type: Array,
    required: true,
    default: () => [],
  },
});

// Set default selected method to first available, or empty string
const selectedMethod = ref(props.paymentMethods[0]?.slug || "");

// Watch for prop changes to reset selectedMethod if paymentMethods changes
watch(
  () => props.paymentMethods,
  (methods) => {
    if (methods.length && !methods.find(m => m.slug === selectedMethod.value)) {
      selectedMethod.value = methods[0].slug;
    }
  },
  { immediate: true }
);

// Emit on selection change
watch(selectedMethod, (val) => {
  emit('update:paymentMethod', val);
});

// Card form fields
const cardNumber = ref("");
const expiry = ref("");
const cvc = ref("");


</script>

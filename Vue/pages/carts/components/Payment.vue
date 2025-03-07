<template>
  <div class="bg-gray-50 rounded-lg w-full">
    <!-- Payment Method Tabs -->
    <div class="flex space-x-2 mb-4">
      <Button
        v-for="method in paymentMethods"
        :key="method.id"
        @click="selectedMethod = method.id"
        :class="{
          '!border-[#1C64F2] !text-[#1C64F2] bg-blue-50':
            selectedMethod === method.id,
          '!border-0  !text-gray-500': selectedMethod !== method.id,
        }"
        class="flex-1 bg-gray-100 flex flex-col !items-start !justify-start p-3 border !rounded-xl"
      >
        <Icon :icon="method.icon" width="24" height="24" class=""></Icon>
        <p>{{ method.label }}</p>
      </Button>
    </div>

    <!-- Card Payment Form -->
    <div v-if="selectedMethod === 'card'">
      <label class="block font-normal text-xs text-gray-400 mb-1"
        >Card Number</label
      >
      <InputText
        v-model="cardNumber"
        class="font-bold text-sm text-gray-950 w-full p-2 border rounded-lg mb-2"
        placeholder="1234 1234 1234 1234"
      />

      <div class="flex space-x-2">
        <div class="flex-1">
          <label class="block font-normal text-xs text-gray-400 mb-1"
            >Expiry</label
          >
          <InputText
            v-model="expiry"
            class="font-bold text-sm text-gray-950 w-full p-2 border rounded-lg"
            placeholder="MM/YY"
          />
        </div>
        <div class="flex-1">
          <label class="block font-normal text-xs text-gray-400 mb-1"
            >CVC</label
          >
          <InputText
            v-model="cvc"
            class="font-bold text-sm text-gray-950 w-full p-2 border rounded-lg"
            placeholder="000"
          />
        </div>
      </div>

      <div class="mt-3 flex items-center">
        <Checkbox
          id="billing"
          v-model="billingSameAsShipping"
          :binary="true"
          class="mr-2"
          size="large"
        />
        <label for="billing" class="font-bold text-sm text-gray-950"
          >Billing is same as shipping information</label
        >
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end gap-3 mt-4">
      <Button
        label="Cancel"
        class="px-5 py-2 bg-gray-200 text-gray-400 rounded-lg"
      />
      <Button
        label="Save"
        class="px-5 py-2 !bg-[#1958F7] text-white rounded-lg"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const selectedMethod = ref("card");
const billingSameAsShipping = ref(true);
const cardNumber = ref("");
const expiry = ref("");
const cvc = ref("");

const paymentMethods = ref([
  { id: "card", label: "Card", icon: "solar:card-linear" },
  { id: "google-pay", label: "Google Pay", icon: "logos:google-pay" },
  { id: "bank", label: "Bank", icon: "fluent:building-bank-28-regular" },
  { id: "cod", label: "Cash On Delivery", icon: "iconoir:hand-cash" },
]);
</script>

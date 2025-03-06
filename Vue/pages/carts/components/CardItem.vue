<template>
  <div
    class="flex !p-3 first:border-b-0 border-b border-gray-100 bg-gray-100 rounded-xl"
  >
    <!-- Product Image -->
    <div
      v-if="Array.isArray(product.image_urls) && product.image_urls.length > 0"
      class="shrink-0 size-[80px] !grid place-items-center bg-white rounded-md"
    >
      <div
        v-for="(imageUrl, imgIndex) in product.image_urls"
        :key="imgIndex"
        class="mt-1"
      >
        <Image
          preview
          :src="baseUrl + imageUrl"
          alt="Product image"
          class="overflow-hidden rounded"
          width="50"
        />
      </div>
    </div>
    <div v-else class="p-2 pb-1 bg-white rounded-md">
      <img
        src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
        alt="Default product image"
        class="overflow-hidden rounded"
        width="64"
      />
    </div>

    <!-- Product Details -->
    <div class="product_desc mx-2 flex flex-col justify-between flex-grow">
      <h4 class="line-clamp-1 text-sm text-gray-950 font-semibold">
        {{ productName }}
      </h4>

      <p class="text-xs leading-[18px] font-semibold text-gray-400">
        {{ displayStats }}
      </p>

      <div class="flex justify-between items-center gap-2">
        <p class="line-clamp-1 text-sm text-gray-950 font-semibold">
          ${{ productPrice }}
        </p>

        <div v-if="showRating" class="flex justify-end items-center">
          <h4 class="text-xs font-semibold text-center mr-2">Qty:</h4>
          <div
            class="p-inputgroup justify-between !items-center border py-1 px-2 rounded-lg !gap-2"
          >
            <Button
              :pt="{ icon: { class: '!text-[8px]' } }"
              icon="pi pi-minus"
              class="quantity-button !rounded bg-gray-200"
              severity="info"
            />

            <span
              class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
            >
              <p class="text-gray-950 font-bold text-xs">1</p>
            </span>
            <Button
              :pt="{ icon: { class: '!text-[8px]' } }"
              icon="pi pi-plus"
              class="quantity-button !rounded"
              severity="info"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, computed } from "vue";

const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({
      name: "",
      pivot: { price: 0 },
      image_urls: [],
      vendor: null,
      rating: 0,
    }),
  },
  baseUrl: {
    type: String,
    default: "",
  },
  showRating: {
    type: Boolean,
    default: false,
  },
  showVendor: {
    type: Boolean,
    default: true,
  },
  price: {
    type: Number,
    default: 0,
  },
});

// Computed properties for flexible data handling
const productName = computed(() => {
  // Handle different possible name structures
  if (props.product.pivot?.cart_product_variation) {
    return `${props.product.name} - ${props.product.pivot.cart_product_variation}`;
  }
  return props.product.name || "";
});

const productPrice = computed(() => {
  if (props.product.pivot?.price) {
    return `${props.product.pivot.price}`;
  }
});

const hasStockData = computed(() => {
  return (
    props.product.stock !== undefined &&
    props.product.stock_percentage !== undefined
  );
});

const stockPercentage = computed(() => {
  if (hasStockData.value) {
    return Math.min(props.product.stock_percentage || 0, 100);
  }
  return 0;
});

const progressBarColorClass = computed(() => {
  const percentage = stockPercentage.value;
  if (percentage < 20) return "p-progressbar-danger";
  if (percentage < 50) return "p-progressbar-warning";
  return "p-progressbar-success";
});

const displayStats = computed(() => {
  // Prioritize stock data if available
  if (hasStockData.value) {
    return `${props.product.stock} in stock (${
      props.product.stock_percentage || 0
    }%)`;
  }

  // Fallback to quantity from pivot or total sales
  const quantity =
    props.product.pivot?.quantity || props.product.total_sales || 0;
  return `${quantity} Units ${hasStockData.value ? "in Stock" : "Sold"}`;
});

const vendorName = computed(() => {
  // Handle different vendor data structures
  if (props.product.vendor?.name) return props.product.vendor.name;
  if (props.product.pivot?.selected_vendor_id)
    return `Vendor ${props.product.pivot.selected_vendor_id}`;
  return null;
});

const productRating = computed(() => {
  // Convert rating to number, default to 0
  return Number(props.product.rating) || 0;
});
</script>

<style scoped>
/* Custom progress bar color styles */
:deep(.p-progressbar-danger .p-progressbar-value) {
  background-color: #f44336; /* Red for low stock */
}

:deep(.p-progressbar-warning .p-progressbar-value) {
  background-color: #ff9800; /* Orange/yellow for medium stock */
}

:deep(.p-progressbar-success .p-progressbar-value) {
  background-color: #4caf50; /* Green for good stock */
}
</style>

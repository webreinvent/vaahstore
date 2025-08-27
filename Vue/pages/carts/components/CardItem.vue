<template>
  <div
    class="flex !p-3 first:border-b-0 border-b border-gray-100 bg-gray-100 rounded-xl"
  >
    <!-- Product Image -->
    <div
      v-if="Array.isArray(product.media) && product.media.length > 0"
      class="shrink-0 size-[80px] !grid place-items-center bg-white rounded-md"
    >
      <div
        v-for="(mediaItem, imgIndex) in product.media"
        :key="imgIndex"
        class="mt-1"
      >
          <div
              v-for="(image, imgIndex) in mediaItem.images"
              :key="imgIndex"
              class="mt-1"
          >
              <Image
                  preview
                  :src="baseUrl + image.webp_url"
                  alt="Product image"
                  class="overflow-hidden rounded"
                  width="50"
              />
          </div>
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
        {{ productName }}-{{product.product_variation?.name}}

      </h4>



      <div class="flex justify-between items-center gap-2">
        <p class="line-clamp-1 text-sm text-gray-950 font-semibold">
            <span v-html="product.price.currency?.symbol"></span>{{ productPrice }}
        </p>

        <div v-if="showRating" class="flex justify-end items-center">
          <h4 class="text-xs font-semibold text-center mr-2">Qty:</h4>
            {{product.pivot.quantity}}
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

<template>
  <div class="flex align-items-center !mx-1.5 py-2 first:border-b-0 border-b border-gray-100">
    <!-- Product Image -->
    <div class="shrink-0">
      <div v-if="Array.isArray(product.image_urls) && product.image_urls.length > 0">
        <div v-for="(imageUrl, imgIndex) in product.image_urls" :key="imgIndex" class="p-2 pb-1 bg-white rounded-md">
          <Image 
            preview 
            :src="baseUrl + imageUrl" 
            alt="Product image" 
            class="overflow-hidden rounded" 
            width="34" 
          />
        </div>
      </div>
      <div v-else class="p-2 pb-1 bg-white rounded-md">
        <img 
          src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg" 
          alt="Default product image" 
          class="overflow-hidden rounded" 
          width="40" 
        />
      </div>
    </div>
    
    <!-- Product Details -->
    <div class="product_desc mx-2 flex-grow">
      <h4 class="line-clamp-1 text-sm text-gray-900 font-semibold">{{ productName }}</h4>
      <p class="text-[10px] font-semibold text-gray-400">{{ displayStats }}</p>
      
      <!-- Stock Progress Bar using PrimeVue -->
      <div v-if="hasStockData" class="w-full mt-1">
        <ProgressBar style="width: 15rem; height:8px"
          :value="stockPercentage" 
          :class="progressBarColorClass"
        />
      </div>
      
      <!-- <p v-if="showVendor && vendorName" class="text-[10px] text-gray-400 mt-1">
        Vendor: {{ vendorName }}
      </p> -->
    </div>
    
    <!-- Rating (if available) -->
    <div v-if="showRating" class="ml-auto">
      <Rating v-model="productRating" :cancel="false" />
    </div>
  </div>
</template>

<script setup>
import { defineProps, computed } from 'vue';

const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({
      name: '',
      total_sales: 0,
      image_urls: [],
      rating: "4"
    })
  },
  baseUrl: {
    type: String,
    required: true
  },
  showRating: {
    type: Boolean,
    default: false
  },
  showVendor: {
    type: Boolean,
    default: true
  }
});

// Computed properties to handle different data structures
const productName = computed(() => {
  if (props.product.product && props.product.product.name) {
    return props.product.product.name;
  }
  return props.product.name || '';
});

const hasStockData = computed(() => {
  return props.product.stock !== undefined && props.product.stock_percentage !== undefined;
});

const stockPercentage = computed(() => {
  if (hasStockData.value) {
    return Math.min(props.product.stock_percentage || 0, 100);
  }
  return 0;
});

const progressBarColorClass = computed(() => {
  const percentage = stockPercentage.value;
  if (percentage < 20) {
    return 'p-progressbar-danger'; // Apply custom class for low stock
  } else if (percentage < 50) {
    return 'p-progressbar-warning'; // Apply custom class for medium stock
  }
  return 'p-progressbar-success'; // Apply custom class for good stock
});

const displayStats = computed(() => {
  if (hasStockData.value) {
    return `${props.product.stock} in stock (${props.product.stock_percentage || 0}%)`;
  }
  return `${props.product.total_sales || 0} Units Sold`;
});

const vendorName = computed(() => {
  if (props.product.vendor && props.product.vendor.name) {
    return props.product.vendor.name;
  }
  return null;
});

const productRating = computed(() => {
  return props.product.rating || 0;
});
</script>

<style scoped>
/* Custom styles for the progress bar colors */
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
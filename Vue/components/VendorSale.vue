<template>
  <!-- Vendor Grid -->
  <div v-for="(vendor, index) in processedVendors" :key="vendor.id" class="!grid place-items-center relative border-round-xl size-full bg-white">
      <!-- Vendor Logo/Image -->
      <div class="mb-3 p-2 w-full">
        <!-- Case 1: When we have image_urls array -->
        <div v-if="Array.isArray(vendor.image_urls) && vendor.image_urls.length > 0">
          <div v-for="(imageUrl, imgIndex) in vendor.image_urls" :key="imgIndex">
            <Image preview :src="baseUrl + '/' + imageUrl" alt="Vendor image" class="shadow-4 mx-auto" width="60" />
          </div>
        </div>
        <!-- Case 2: When we have a logo property -->
        <img v-else-if="vendor.logo" :src="baseUrl + (vendor.logo.startsWith('/') ? '' : '/') + vendor.logo" :alt="vendor.name" class="mx-auto" width="60" />
        <!-- Case 3: Fallback to default icon -->
        <i v-else class="pi pi-building text-4xl text-primary mx-auto block"></i>
      </div>
      
      <div class="p-1 rounded-md bg-gray-100 text-center absolute -bottom-6 w-[75%] left-1/2 -translate-x-1/2 shadow-2">
        <!-- Ranking -->
        <div class="font-bold text-[10px]">
          {{ getRankText(index) }}
        </div>

        <!-- Vendor Name -->
        <div class="text-center text-[8px] text-700 line-clamp-1 font-bold text-gray-400">
          {{ vendor.name }}
        </div>
      </div>
  </div>

  <!-- Empty state -->
  <div v-if="!processedVendors || processedVendors.length === 0" class="col-12 text-center py-5">
    <i class="pi pi-info-circle text-xl mb-3 text-blue-300"></i>
    <p>No vendors found.</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

// Props
const props = defineProps({
  vendorData: {
    type: Array,
    default: () => []
  },
  baseUrl: {
    type: String,
    default: ''
  }
});

// Sample logos for demonstration (replace with your actual vendor logos)
const sampleLogos = [
  '/parth-s001/store-dev/public/storage/media/1st-vendor.png',
  '/parth-s001/store-dev/public/storage/media/2nd-vendor.png',
  '/parth-s001/store-dev/public/storage/media/3rd-vendor.png',
  '/parth-s001/store-dev/public/storage/media/4th-vendor.png',
  '/parth-s001/store-dev/public/storage/media/1st-vendor.png',
];

// Process vendors to handle different data structures
const processedVendors = computed(() => {
  return (props.vendorData || []).map((vendor, index) => {
    return {
      ...vendor,
      logo: vendor.logo || (!Array.isArray(vendor.image_urls) || vendor.image_urls.length === 0) 
        ? sampleLogos[index % sampleLogos.length] 
        : vendor.logo,
    };
  });
});


// Helper function to get rank text
const getRankText = (index) => {
  const ranks = ['1st', '2nd', '3rd', '4th'];
  return ranks[index] || `${index + 1}th`;
};
</script>

<style scoped>
/* Add any additional custom styles here */
</style>
<template>
  <!-- Header -->

  <!-- Vendor Grid -->
  <div v-for="(vendor, index) in vendors" :key="vendor.id" class="p-2 relative">
      <!-- Vendor Logo/Image -->
      <div class="mb-3 p-3 border-round-xl bg-white w-full">
        <img v-if="vendor.logo" :src="vendor.logo" :alt="vendor.name" class="w-full"
          style="max-width: 80px; max-height: 80px;" />
        <i v-else class="pi pi-building text-4xl text-primary"></i>
      </div>
      <div class="p-2 rounded-lg bg-gray-100 text-center absolute bottom-0 w-[80%] left-1/2 -translate-x-1/2">
        <!-- Ranking -->
        <div class="font-bold text-[10px] ">
          {{ getRankText(index) }}
        </div>

        <!-- Vendor Name -->
        <div class="text-center text-[8px] text-700">
          {{ vendor.name }}
        </div>
      </div>

  </div>

  <!-- Empty state -->
  <div v-if="!vendors || vendors.length === 0" class="col-12 text-center py-5">
    <i class="pi pi-info-circle text-xl mb-3 text-blue-300"></i>
    <p>No vendors found.</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// Props
const props = defineProps({
  vendorData: {
    type: Array,
    default: () => []
  }
});

// Sample logos for demonstration (replace with your actual vendor logos)
const sampleLogos = [
  '/assets/images/vendor-logos/nature-nest.png',
  '/assets/images/vendor-logos/tech-hive.png',
  '/assets/images/vendor-logos/style-spheres.png',
  '/assets/images/vendor-logos/digi-heaven.png'
];

// Compute vendors with added logo property
const vendors = computed(() => {
  return props.vendorData.map((vendor, index) => ({
    ...vendor,
    logo: vendor.logo || sampleLogos[index % sampleLogos.length]
  })).slice(0, 4); // Limit to 4 vendors
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
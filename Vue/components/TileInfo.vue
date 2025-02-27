<template>
    <div class="flex align-items-center p-3 border-b border-gray-100">
      <!-- Product Image -->
      <div class="">
        <div v-if="Array.isArray(product.image_urls) && product.image_urls.length > 0">
          <div v-for="(imageUrl, imgIndex) in product.image_urls" :key="imgIndex" class="p-2 pb-1 bg-white">
            <Image 
              preview 
              :src="baseUrl + '/' + imageUrl" 
              alt="Product image" 
              class="" 
              width="50" 
            />
          </div>
        </div>
        <div v-else>
          <img 
            src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg" 
            alt="Default product image" 
            class="shadow-4" 
            width="35" 
          />
        </div>
      </div>
      
      <!-- Product Details -->
      <div class="product_desc ml-2">
        <h4>{{ product.name }}</h4>
        <p><b>{{ product.total_sales }}</b> Sold</p>
      </div>
      
      <!-- Rating (if available) -->
      <div v-if="showRating" class="ml-auto">
        <Rating v-model="product.rating" :cancel="false" />
      </div>
    </div>
  </template>
  
  <script setup>
  import { defineProps } from 'vue';
  
  const props = defineProps({
    product: {
      type: Object,
      required: true,
      default: () => ({
        name: '',
        total_sales: 0,
        image_urls: [],
        rating: 4
      })
    },
    baseUrl: {
      type: String,
      required: true
    },
    showRating: {
      type: Boolean,
      default: false
    }
  });
  </script>
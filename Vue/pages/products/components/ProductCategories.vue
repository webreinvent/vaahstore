<script setup>
import { defineProps, inject, onMounted, ref } from 'vue';
import { vaah } from '../../../vaahvue/pinia/vaah';
import { useProductStore } from '../../../stores/store-products';

const store = useProductStore();
const useVaah = vaah();




// const injectedCategories=ref();
const injectedCategories = ref({ categories: [] });

const dialogRef = inject('dialogRef');

onMounted(() => {
    if (dialogRef && dialogRef.value && dialogRef.value.data) {
        injectedCategories.value = dialogRef.value.data;
        console.log(injectedCategories.value.categories)
    }
})


</script>

<template>
    <div>

        <div v-if="injectedCategories.categories && injectedCategories.categories.length">
            <table>
                <thead>
                <tr>
<!--                    <th>ID</th>-->
                    <th>Name</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="category in injectedCategories.categories" :key="category.id">
<!--                    <td>{{ category.id }}</td>-->
                    <td>{{ category.name }}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-else>
            No categories available.
        </div>
    </div>
</template>


<style scoped>
</style>

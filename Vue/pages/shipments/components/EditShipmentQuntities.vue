<script setup>
import {computed, defineProps, inject, onMounted, ref} from 'vue';
import { vaah } from '../../../vaahvue/pinia/vaah';
import {useShipmentStore} from "../../../stores/store-shipments";

const store = useShipmentStore();
const useVaah = vaah();
const injectedCategories = ref({ shipment_item_id: [] });
const header = ref('');
const dialogRef = inject('dialogRef');
onMounted(() => {

    if (dialogRef && dialogRef.value && dialogRef.value.data) {
        injectedCategories.value = dialogRef.value.data;
        header.value = dialogRef.value.options.props.header;
        store.getShipmentItemList(dialogRef.value.data.shipment_item_id)
    }
})
// const categoriesData = computed(() => {
//     const search = (store.item.search_category || '').toLowerCase().trim();
//     return injectedCategories.value.categories.filter(category =>
//         category.name.toLowerCase().includes(search)
//     );
// });



</script>


<template>
    <div class="card">
        <Message :closable="false" severity="warn">This will impact quantity on other shipments as well.</Message>
    </div>
</template>

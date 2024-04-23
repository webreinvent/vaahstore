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
        // console.log(injectedCategories.value.categories);

        // const categoriesArray = Object.values(injectedCategories.value.categories);
        // store.convertToTreeSelectFormat(categoriesArray);
        // store.convertToTreeSelectFormat(injectedCategories.value.categories);
    }
})


</script>

<template>
    <div>
<!--        <TreeSelect-->
<!--            v-model="store.item.parent_category"-->
<!--            :options="store.categories_dropdown_data"-->
<!--            selectionMode="multiple"-->
<!--            placeholder="Select Category"-->
<!--            :show-count="true"-->
<!--            data-testid="product-category"-->
<!--            @change="store.setParentId()"-->
<!--            class=" w-full" />{{store.item.parent_category}}-->
        <div v-if="injectedCategories.categories && injectedCategories.categories.length">
        <DataTable
            :value="injectedCategories.categories"
            :rows="20"
            :paginator="true"
            style="border: 1px solid #ccc; margin-top: 20px;"
            class="p-datatable-sm p-datatable-hoverable-rows"
        >
            <Column field="name" header="Category" style="border: 1px solid #ccc;">
                <template #body="props">
                    {{ props.data.name }}
                </template>
            </Column>

            <Column header="Action"  style="border: 1px solid #ccc;">
                <template #body="props">{{props.data.id}}
                    <Button class="p-button-tiny p-button-danger p-button-text"
                            data-testid="products-table-action-trash"
                            @click="store.removeCategory('delete', props.data)"
                            v-tooltip.top="'Remove'"
                            icon="pi pi-trash" />
                </template>
            </Column>

            <template #empty="prop">
                <div style="text-align: center; font-size: 12px; color: #888;">
                    No records found.
                </div>
            </template>
        </DataTable>



    </div>
    </div>
</template>


<style scoped>
</style>

<script setup>
import {computed, defineProps, inject, onMounted, ref} from 'vue';
import { vaah } from '../../../vaahvue/pinia/vaah';
import { useProductStore } from '../../../stores/store-products';

const store = useProductStore();
const useVaah = vaah();
const injectedCategories = ref({ categories: [] });
const header = ref('');
const dialogRef = inject('dialogRef');
onMounted(() => {
    if (dialogRef && dialogRef.value && dialogRef.value.data) {
        injectedCategories.value = dialogRef.value.data;
        header.value = dialogRef.value.options.props.header;
    }
})
const categoriesData = computed(() => {
    const search = (store.item.search_category || '').toLowerCase().trim();
    return injectedCategories.value.categories.filter(category =>
        category.name.toLowerCase().includes(search)
    );
});

const removeCategory = async (category) => {
    await store.removeCategory('delete', category);
    injectedCategories.value.categories = injectedCategories.value.categories.filter(c => c.id !== category.id);
};

</script>

<template>
    <div v-if="store.item">
        <InputText
            v-model="store.item.search_category"
            placeholder="Search category..."
            data-testid="products-product_categories"
            class="p-mb-2"
        />
        <DataTable
            :value="categoriesData"
            :rows="10"
            :paginator="true"
            style="border: 1px solid #ccc; margin-top: 20px;"
            class="p-datatable-sm p-datatable-hoverable-rows"
        >
            <Column header="Sr No" style="border: 1px solid #ccc; width: 10px">
                <template #body="props">
                    {{ props.index + 1 }}
                </template>
            </Column>
            <Column field="name" header="Category Name" style="border: 1px solid #ccc;">
                <template #body="props">
                    {{ props.data.name }}
                </template>
            </Column>

            <Column header="Action"  style="border: 1px solid #ccc;">
                <template #body="props">
                    <Button class="p-button-tiny p-button-danger p-button-text"
                            data-testid="products-product_categories-action-remove"
                            @click="removeCategory(props.data)"
                            v-tooltip.top="'Remove'"
                            icon="pi pi-trash" />
                </template>
            </Column>

            <template #empty="prop">
                <div style="text-align: center; font-size: 12px; color: #888;">
                    No category found.
                </div>
            </template>
        </DataTable>

    </div>
</template>




<script setup>

import {useImportStore} from "../../../stores/store-import";
import {ref, reactive, computed} from 'vue';

import {useProductStore} from "../../../stores/store-products";
const store = useImportStore();
const product_store=useProductStore()
const isExporting = ref(false);
import VhField from './../../../vaahvue/vue-three/primeflex/VhField.vue'


const columnOptions = [
    { label: 'Export all columns', value: 'all' },
    { label: 'Basic columns only', value: 'basic' },
    { label: 'Custom selection', value: 'custom' }
];

const productTypeOptions = [
    { label: 'Export all products', value: 'all' },
    { label: 'Simple products', value: 'simple' },
    { label: 'Variable products', value: 'variable' },
    { label: 'Digital products', value: 'digital' }
];

const categoryOptions = [
    { label: 'Export all categories', value: 'all' },
    { label: 'Selected categories', value: 'selected' }
];

const handleExport = async () => {
    try {
        isExporting.value = true;
        await new Promise(resolve => setTimeout(resolve, 2000));

        console.log('Exporting with settings:', formData);

        // Here you would typically:
        // 1. Make an API call to generate the CSV
        // 2. Receive a blob or download URL
        // 3. Trigger the download

        // Example download trigger:
        // const response = await fetch('/api/export-csv', {
        //   method: 'POST',
        //   headers: { 'Content-Type': 'application/json' },
        //   body: JSON.stringify(formData)
        // });
        // const blob = await response.blob();
        // const url = window.URL.createObjectURL(blob);
        // const link = document.createElement('a');
        // link.href = url;
        // link.download = 'products-export.csv';
        // link.click();

    } catch (error) {
        console.error('Export failed:', error);
        // Here you might want to show an error message to the user
        // For example, using PrimeVue Toast component
    } finally {
        isExporting.value = false;
    }
};


const formData = ref({
    columns: [], // Ensure columns is always defined as an array
});
const options = computed(() => {
    const customOptions = [
        { label: 'Export All Columns', column: 'export_all' },
        { label: 'Custom Columns', column: 'custom_columns' },
    ];
    const dynamicFields = store.assets?.types?.fields || [];

    return [...customOptions, ...dynamicFields];
});
const handleSelection = (selected) => {
    // Ensure formData.value.columns exists before modification
    if (!formData.value.columns) {
        formData.value.columns = [];
    }

    if (selected.includes('export_all')) {
        // Select all dynamic columns
        formData.value.columns = (store.assets.types.fields || []).map((field) => field.column);
    } else if (selected.includes('custom_columns')) {
        // Clear columns for manual selection
        formData.value.columns = [];
    }
};

</script>


<template>



    <Card >
        <template #title>Export</template>
        <template #content>

            <p class="text-color-secondary mb-2">
                <b>This allows you to generate and download a CSV file containing a list of all products.</b>
            </p>
             <div class="flex border-1 p-5 border-round-md border-dashed bg-gray-50 border-400 flex-column gap-2">

                    <VhField label="Columns Exported*">
                    <MultiSelect
                        id="columns"
                        filter
                        v-model="formData.columns"
                        :options="options"
                        optionLabel="label"
                        optionValue="column"
                        display="chip"
                        placeholder="Select columns"
                        class="w-full"
                        @change="handleSelection(formData.columns)"
                    />
                    </VhField>
                </div>

        </template>
        <template #footer>
            <div class="grid grid-nogutter justify-content-between">
                <Button label="Back" @click="store.toProducts()" icon="pi pi-angle-left" />
                <Button
                    type="submit"
                    label="Generate CSV"
                    icon="pi pi-download"
                    @click="product_store.exportProducts('export')"
                    :loading="isExporting"
                    :disabled="isExporting"
                />

            </div>
        </template>
    </Card>

</template>



<style scoped>

</style>

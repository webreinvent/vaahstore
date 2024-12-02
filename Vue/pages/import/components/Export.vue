<script setup>

import {useImportStore} from "../../../stores/store-import";
import {ref, reactive, computed} from 'vue';

import {useProductStore} from "../../../stores/store-products";
const store = useImportStore();
const product_store=useProductStore()
const isExporting = ref(false);
import VhField from './../../../vaahvue/vue-three/primeflex/VhField.vue'





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
    if (!formData.value.columns) {
        formData.value.columns = [];
    }

    if (selected.includes('export_all')) {
        formData.value.columns = (store.assets.types.fields || []).map((field) => field.column);
    } else if (selected.includes('custom_columns')) {
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

                    <VhField label="Columns Exported*">{{formData.columns}}
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
                    @click="product_store.exportProducts('export',formData.columns)"
                    :loading="isExporting"
                    :disabled="isExporting"
                />

            </div>
        </template>
    </Card>

</template>



<style scoped>

</style>

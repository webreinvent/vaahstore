<script setup>

import {useImportStore} from "../../../stores/store-import";
import {ref, reactive, computed} from 'vue';

import {useProductStore} from "../../../stores/store-products";
const store = useImportStore();
const product_store=useProductStore()
const isExporting = ref(false);
import VhField from './../../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from "vue-router";

const route = useRoute();
const export_Type = ref(route.query.type || 'export');
//-------------------------------------------------

const options = computed(() => {
    const customOptions = [
        { label: 'Export All Columns', column: 'export_all_columns' },
    ];
    const dynamicFields = store.assets?.types?.fields || [];

    return [...customOptions, ...dynamicFields];
});

//-------------------------------------------------

const handleSelection = (selected) => {
    if (selected.includes("export_all_columns")) {
        product_store.column_to_export.columns = ["export_all_columns"];
    } else {
        product_store.column_to_export.columns = selected;
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
                        v-model="product_store.column_to_export.columns"
                        :options="options"
                        optionLabel="label"
                        optionValue="column"
                        display="chip"
                        placeholder="Select Columns"
                        class="w-full"
                        @change="handleSelection(product_store.column_to_export.columns)"
                    />
                    </VhField>

                 <VhField label="Is Custom Meta">
                     <Checkbox v-model="product_store.is_export_custom_meta" :binary="true" />
                 </VhField>

                </div>

        </template>
        <template #footer>
            <div class="grid grid-nogutter justify-content-between">
                <Button label="Back" @click="store.toProducts()" icon="pi pi-angle-left" />
                <Button
                    type="submit"
                    label="Export"
                    icon="pi pi-download"
                    @click="product_store.exportProducts(export_Type,[product_store.column_to_export.columns, product_store.is_export_custom_meta])"
                    :loading="isExporting"
                    :disabled="isExporting"
                />

            </div>
        </template>
    </Card>

</template>

<style scoped>

</style>



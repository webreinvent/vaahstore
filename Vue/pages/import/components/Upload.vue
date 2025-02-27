<template>
    <Card :pt="{
        content: {
            class: 'pb-0'
        }
    }">
        <template #title>Bulk Import</template>
        <template #content>
            <div class="flex align-items-center justify-content-center border-1 p-6 border-round-md border-dashed bg-gray-50 border-400">
                <span :style="{ fontSize: '14px', fontWeight: '500' }" class="p-text-secondary ">Select a CSV file to Import</span>
                <div class="ml-2 flex align-items-center">
                    <FileUpload
                        mode="basic"
                        name="file"
                        ref="file"
                        accept=".csv"
                        :maxFileSize="10000000"
                        @select="onSelect"
                        :chooseLabel="store.label"
                    />

                </div>

                <Button
                    style="margin-left:5px;font-size:5px;"
                    v-if="store.selected_file"
                    icon="pi pi-times"
                    class="p-button-danger p-button-text"
                    @click="clearFile()" />


                <div style="width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 8px;border: 2px solid #ccc;margin-left:20px;">
                    <span style="color: #8393A5; font-weight:400;font-size: 10px;">OR</span>
                </div>

                <div class="ml-3">
                    <Button label="Download Sample CSV File" outlined @click="store.downloadSampleFile" style="border: none; border-bottom: 1px dashed;" />
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-content-between">
                <Button label="Back" @click="store.toProducts()" icon="pi pi-angle-left" />
                <Button
                    label="Next"
                    @click="nextPage()"
                    icon="pi pi-angle-right"
                    icon-pos="right"
                    :disabled="!store.selected_file || !store.type"
                />

            </div>
        </template>
    </Card>

</template>
<script setup>
import {useImportStore} from "../../../stores/store-import";
import {ref} from "vue";

const emit = defineEmits(['next-page', 'prev-page'])
const store = useImportStore();
const file = ref(null);

//-----------------functions
function nextPage(){

    if(!store.selected_file){
        store.errors.file = "Please select a file";
        return;
    }
    store.clearErrors();
    store.readCSV();
}

function onSelect(event){

        if(file)
        {
            store.file = file;
            store.label = event.files[0].name;
            store.onSelect();
        }
}

function clearFile() {

    file.value.files = [];
    store.file = null;
    store.selected_file = null;
    store.label = 'Click to Upload';

}




</script>

<style>
.p-card-title{
    padding : 1rem;
    border-bottom: 1px solid #D3D3D3;
}

</style>

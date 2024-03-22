<template>

    <div>

    </div>
    <div class="card flex justify-content-center">
        <Dropdown v-model="selectedCrud" :options="crud" optionLabel="name" placeholder="Select a Crud" class="w-full md:w-14rem mr-1"  />
        <InputText id="username" v-model="quantity_val" placeholder="Enter Quantity" class="mr-1" />
        <Button label="Submit" @click="store.createBulkRecords([selectedCrud.name ,quantity_val])" />
    </div>
</template>

<script setup>
import {useSettingStore} from '../../../../stores/store-settings'

const store = useSettingStore();

import { ref } from "vue";

const value = ref("");
const items = ref([]);
const quantity_val = ref(null);

const selectedCrud = ref();
const crud = ref([
    { name: 'Store' },
    { name: 'Rome' },
    { name: 'London' },
    { name: 'Istanbul' },
    { name: 'Paris' }
]);

const search = (event) => {
    items.value = [...Array(10).keys()].map((item) => event.query + '-' + item);
}

</script>

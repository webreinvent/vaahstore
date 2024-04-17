<template>

    <div>

    </div>
    <div class="card flex justify-content-center">
        <Dropdown class="w-full md:w-14rem mr-1"
                  v-model="store.selected_crud"
                  :options="store.crud_options"
                  optionLabel="label"
                  optionValue="value"
                  name="sample-crud_options"
                  placeholder="Select Crud"
                  data-testid="sample-crud_options"
        />
        <InputNumber id="input_text" v-model="store.quantity" placeholder="Enter no. of records" class="mr-1" />

        <Button label="Submit" @click="store.createBulkRecords()" :disabled="store.is_button_disabled"   />
    </div>
</template>

<script setup>
import {useSettingStore} from '../../../../stores/store-settings'
import {useRoute} from "vue-router";
import {onMounted} from "vue";

const store = useSettingStore();

const route = useRoute();

onMounted(async () => {

    store.is_button_disabled = false;
    store.quantity = null;
    store.selected_crud = null;
});

</script>

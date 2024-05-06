<template>

<!--    <div class="card flex justify-content-center">
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
    -->


    <Button type="button" label="Fill All Inputs" icon="pi pi-arrow-down" @click="store.fillAll()" style="margin-left: 50rem"/>

    <div class="card justify-content-center gap-3 mt-5">
        <div v-for="item in store.crud_options" class="flex align-items-center mb-3">
            <div>
                <Checkbox v-model="item.isChecked" :value="item.value"
                          class="ml-3" @input="store.checkAll(item)"
                          :binary="true" :disabled="item.disabled" />
                <label class="ml-3" > {{ item.label}}  </label>
                <label class="ml-3" >( {{item.count }} )  </label>
            </div>

            <InputNumber placeholder="Enter quantity" class="ml-3" v-model="item.quantity" />
        </div>

        <Button label="Submit"
                @click="store.createBulkRecords()"
                style="width: 10rem; margin-top: 1.5rem; border-radius: 4rem"
                :disabled="store.is_button_disabled" />
    </div>

</template>

<script setup>
import {useSettingStore} from '../../../../stores/store-settings'
import {useRoute} from "vue-router";
import {onMounted , ref , getCurrentInstance} from "vue";

const store = useSettingStore();

const route = useRoute();

onMounted(async () => {

    // store.is_button_disabled = false;
    // store.quantity = null;
    // store.selected_crud = null;

    store.updateCounts();
});


//
// const is_checked = ref()



</script>

<style scoped>

.container {
    display: flex;
    justify-content: space-between;
}

</style>

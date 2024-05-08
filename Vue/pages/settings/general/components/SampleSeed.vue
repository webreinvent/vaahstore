<template>


    <Button type="button" label="Fill All Inputs" icon="pi pi-arrow-down" @click="store.fillAll()"
            style="margin-left: 50rem"/>

    <table>
        <tr v-for="item in store.crud_options">
            <td>
                <Checkbox v-model="item.isChecked" :value="item.value"
                          @input="store.checkAll(item)"
                          :binary="true" :disabled="item.disabled"/>
            </td>

                <label class="ml-3"> {{ item.label }} </label>

            <td>
                <label class="ml-3">( {{ item.count }} ) </label>

            </td>
            <td>
                <InputNumber placeholder="Enter quantity" class="ml-4" v-model="item.quantity"/>
            </td>
        </tr>
    </table>

    <Button label="Submit"
            @click="store.createBulkRecords()"
            style="width: 10rem; margin-top: 1.5rem; border-radius: 4rem"
            :disabled="store.is_button_disabled"/>

</template>

<script setup>
import {useSettingStore} from '../../../../stores/store-settings'
import {useRoute} from "vue-router";
import {onMounted, ref, getCurrentInstance} from "vue";

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

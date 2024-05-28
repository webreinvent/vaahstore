<template>


    <Button type="button" label="Fill All" icon="pi pi-arrow-down" @click="store.fillAll()"
            style="margin-left: 50rem;  margin-top: 1.5rem; border-radius: 4rem; width: 6rem;"/>

    <table>
        <tr v-for="item in store.crud_options">
            <td>
                <Checkbox v-model="item.isChecked" :value="item.value"
                          @input="store.checkAll(item)"
                          :binary="true" :disabled="item.disabled"/>
            </td>
            <td>
                <label class="ml-3"> {{ item.label }} </label>
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

    <Button label="Reset All"
            @click="store.resetAll()"
            style="width: 10rem; margin-top: 1.5rem; border-radius: 4rem; margin-left: 1rem"
            :disabled="store.is_button_disabled"/>

    <Button label="Delete All"
            @click="store.deleteAll()"
            icon="pi pi-trash" class="p-button-danger"
            style="width: 10rem; margin-top: 1.5rem; border-radius: 4rem; margin-left: 1rem"
            :disabled="store.is_button_disabled"/>


    <Dialog header="Delete"
            v-model:visible="store.show_delete_modal"
            :breakpoints="{'960px': '75vw', '640px': '90vw'}"
            :style="{width: '50vw'}">


        <Message severity="error" icon="null" :closable="false">
            <p>You are going to <b>Delete</b> all the records in vaahstore.
                This will remove all the data of the store module.</p>
            <p>After delete you <b>CANNOT</b> be restored data!
                Are you <b>ABSOLUTELY</b> sure?</p>
        </Message>

        <div>
            <p>This action can lead to data loss.
                To prevent accidental actions we ask you to confirm your intention.</p>

            <p class="has-margin-bottom-5">
                Please type <b>DELETE</b> to proceed and click
                Confirm button or close this modal to cancel.
            </p>
        </div>

        <InputText v-model="store.delete_inputs.confirm"
                   placeholder="Type DELETE to Confirm" class="p-inputtext-md"
                   required
        />

        <div class="mt-2" v-if="store.delete_inputs.confirm === 'DELETE' ">

            <div class="field-checkbox">
                <Checkbox inputId="delete_records"
                          v-model="store.delete_inputs.delete_records"
                          value="true"
                />
                <label>
                    Delete All Records (VaahStore)
                </label>
            </div>
        </div>

        <template #footer>
            <Button label="No"
                    icon="pi pi-times"
                    @click="store.show_delete_modal = false"
                    class="p-button-text"/>

            <Button class="p-button-danger"
                    label="Confirm"
                    icon="pi pi-check"
                    :loading="store.delete_confirm"
                    @click="store.confirmReset()"
                    autofocus />
        </template>
    </Dialog>

</template>

<script setup>
import {useSettingStore} from '../../../../stores/store-settings'
import {useRoute} from "vue-router";
import {onMounted} from "vue";

const store = useSettingStore();

const route = useRoute();

onMounted(async () => {
    store.updateCounts();
});


</script>

<script setup>
import {onMounted, ref, watch} from "vue";
import { useOrderStore } from '../../stores/store-orders'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useOrderStore();
const route = useRoute();

onMounted(async () => {
    if(route.params && route.params.id)
    {
        await store.getItem(route.params.id);
    }

    await store.watchItem();
});

//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};
//--------/form_menu

</script>
<template>

    <div class="col-6" >

        <Panel >

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <span v-if="store.item && store.item.id">
                            Update
                        </span>
                        <span v-else>
                            Create
                        </span>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">
                    <Button label="Save"
                            v-if="store.item && store.item.id"
                            data-testid="orders-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="orders-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="orders-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="orders-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Amount">
                    <InputNumber
                        :disabled="store.item.in_stock==0"
                        placeholder="Enter a Amount"
                        inputId="minmax-buttons"
                        name="orders-quantity"
                        v-model="store.item.amount"
                        mode="decimal" showButtons
                        data-testid="orders-amount"/>
                </VhField>

                <VhField label="Status">
                    <AutoComplete
                        v-model="store.item.status"
                        class="w-full"
                        name="orders-status"
                        :suggestions="store.suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="orders-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="5" cols="30"
                              placeholder="Enter a Status Note"
                              name="orders-status_notes"
                              data-testid="orders-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="orders-active"
                                 data-testid="orders-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

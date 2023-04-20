 <script setup>
import {onMounted, ref, watch} from "vue";
import { useStorePaymentMethodStore } from '../../stores/store-storepaymentmethods'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useStorePaymentMethodStore();
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
                            data-testid="storepaymentmethods-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="storepaymentmethods-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="storepaymentmethods-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="storepaymentmethods-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="storepaymentmethods-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Store">

                    <AutoComplete
                        value="id"
                        v-model="store.item.vh_st_store_id"
                        class="w-full"
                        :suggestions="store.store_suggestion"
                        @complete="store.searchStore($event)"
                        placeholder="Select Store"
                        data-testid="storepaymentmethods-store"
                        name="storepaymentmethods-store"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Payment Method">

                    <AutoComplete
                        value="id"
                        v-model="store.item.vh_st_payment_method_id"
                        class="w-full"
                        :suggestions="store.payment_method_suggestion"
                        @complete="store.searchPaymentMethod($event)"
                        placeholder="Select Payment Method"
                        data-testid="storepaymentmethods-payment_method"
                        name="storepaymentmethods-payment_method"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Last Payment at" >
                    <Calendar tabindex="0"
                              :showIcon="true"
                              name="storepaymentmethods-last_payment_at"
                              id="last_payment_at"
                              value="last_payment_at"
                              data-testid="storepaymentmethods-last_payment_at"
                              dateFormat="yy-mm-dd"
                              :showTime="true" :showSeconds="true"
                              placeholder="Select date"
                              v-model="store.item.last_payment_at"></Calendar>
                </VhField>

                <VhField label="Status">

                    <AutoComplete
                        value="id"
                        v-model="store.item.taxonomy_id_payment"
                        class="w-full"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        data-testid="storepaymentmethods-status"
                        name="storepaymentmethods-status"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="storepaymentmethods-status_notes"
                              data-testid="storepaymentmethods-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="storepaymentmethods-active"
                                 data-testid="storepaymentmethods-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

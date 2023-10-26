<script setup>
import {onMounted, ref, watch} from "vue";
import { useCustomerGroupStore } from '../../stores/store-customergroups'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useCustomerGroupStore();
const route = useRoute();

onMounted(async () => {
    if(route.params && route.params.id)
    {
        await store.getItem(route.params.id);
    }

    await store.getFormMenu();
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

        <Panel class="is-small">

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
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="customergroups-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="customergroups-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="customergroups-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>



                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="customergroups-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="customergroups-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="pt-2">

                <VhField label="Name">
                    <InputText class="w-full"
                               placeholder="Enter a Name"
                               name="customergroups-name"
                               data-testid="customergroups-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="customergroups-slug"
                               placeholder="Enter a Slug"
                               data-testid="customergroups-slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Customer Count">
                    <InputNumber
                        placeholder="Enter a Customer Count"
                        name="customergroups-customer_count"
                        v-model="store.item.customer_count"
                        inputId="minmaxfraction" showButtons
                        :min=0
                        data-testid="customergroups-customer_count"/>
                </VhField>

                <VhField label="Order Count">
                    <InputNumber
                        placeholder="Enter a Order Count"
                        name="customergroups-order_count"
                        v-model="store.item.order_count"
                        :min=0
                        inputId="minmaxfraction" showButtons
                        data-testid="customergroups-order_count"/>
                </VhField>

                <VhField label="Status">
                    <AutoComplete
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        value="id"
                        name="customergroups-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="customergroups-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="customergroups-status_notes"
                              data-testid="customergroups-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

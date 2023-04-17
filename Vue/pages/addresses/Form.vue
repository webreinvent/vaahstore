<script setup>
import {onMounted, ref, watch} from "vue";
import { useAddressStore } from '../../stores/store-addresses'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useAddressStore();
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
                            data-testid="addresses-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="addresses-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="addresses-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="addresses-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="addresses-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="User">
                    <AutoComplete
                        v-model="store.item.vh_user_id"
                        class="w-full"
                        name="addresses-user"
                        :suggestions="store.user_suggestion"
                        @complete="store.searchUser($event)"
                        placeholder="Select User"
                        :dropdown="true" optionLabel="first_name"
                        data-testid="addresses-user"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Type">
                    <AutoComplete
                        v-model="store.item.taxonomy_id_address_types"
                        class="w-full"
                        name="addresses-type"
                        :suggestions="store.type_suggestion"
                        @complete="store.searchType($event)"
                        placeholder="Select Type"
                        :dropdown="true" optionLabel="name"
                        data-testid="addresses-type"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Address line 1">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Address Line 1"
                              name="addresses-address_line_1"
                              data-testid="addresses-address_line_1"
                              v-model="store.item.address_line_1"/>
                </VhField>

                <VhField label="Address line 2">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Address Line 2"
                              name="addresses-address_line_2"
                              data-testid="addresses-address_line_2"
                              v-model="store.item.address_line_2"/>
                </VhField>

                <VhField label="Status">
                    <AutoComplete
                        v-model="store.item.taxonomy_id_address_status"
                        class="w-full"
                        name="addresses-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="addresses-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="orders-status_notes"
                              data-testid="orders-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

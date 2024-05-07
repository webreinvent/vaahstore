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
                    <Button class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="addresses-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>
                    <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                            label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="addresses-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                            label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="addresses-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="addresses-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>


                    <!--form_menu-->
                    <Button
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="addresses-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="addresses-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="pt-2">

                <VhField label="User*">
                    <AutoComplete
                        v-model="store.item.user"
                        @change="store.setUser($event)"
                        class="w-full"
                        value="id"
                        name="addresses-user"
                        :suggestions="store.user_suggestion"
                        @complete="store.searchUser($event)"
                        placeholder="Select User"
                        :dropdown="true" optionLabel="first_name"
                        data-testid="addresses-user"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Type*">
                    <AutoComplete
                        v-model="store.item.address_type"
                        @change="store.setAddressType($event)"
                        class="w-full"
                        value="id"
                        name="addresses-type"
                        :suggestions="store.type_suggestion"
                        @complete="store.searchType($event)"
                        placeholder="Select Type"
                        :dropdown="true" optionLabel="name"
                        data-testid="addresses-type"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Address line 1*">
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


                <VhField label="City">
                    <InputText class="w-full"
                               name="warehouses-city"
                               data-testid="warehouses-city"
                               placeholder="Enter City"
                               v-model="store.item.city"/>
                </VhField>
                <VhField label="State">
                    <InputText class="w-full"
                               name="warehouses-state"
                               data-testid="warehouses-state"
                               placeholder="Enter State"
                               v-model="store.item.state"/>
                </VhField>
                <VhField label="Country*">
                    <AutoComplete v-model="store.item.country"
                                  value="id"
                                  class="w-full"
                                  data-testid="warehouses-country"
                                  :suggestions="store.country_suggestions"
                                  @complete="store.searchCountry($event)"
                                  :dropdown="true"
                                  placeholder="Select Country"
                                  forceSelection />
                </VhField>


                <VhField label="Status*">
                    <AutoComplete
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        name="addresses-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        value="id"
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

                <VhField label="Is Default">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="addresses-active"
                                 data-testid="addresses-active"
                                 v-model="store.item.is_default"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

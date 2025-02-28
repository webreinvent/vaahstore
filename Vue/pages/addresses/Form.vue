<script setup>
import {onMounted, ref, watch} from "vue";
import { useAddressStore } from '../../stores/store-addresses'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import { useRootStore } from '@/stores/root.js'
import {useRoute} from 'vue-router';


const store = useAddressStore();
const root = useRootStore();
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

    <Panel :pt="root.panel_pt">

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

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <AutoComplete
                        v-model="store.item.user"
                        @change="store.setUser($event)"
                        class="w-full"
                        value="id"
                        name="addresses-user"
                        :suggestions="store.user_suggestion"
                        @complete="store.searchUser($event)"
                        :dropdown="true" optionLabel="first_name"
                        data-testid="addresses-user"
                        forceSelection>
                    </AutoComplete>
                    <label for="addressess-user">Select User <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <AutoComplete
                        v-model="store.item.address_type"
                        @change="store.setAddressType($event)"
                        class="w-full"
                        value="id"
                        name="addresses-type"
                        :suggestions="store.type_suggestion"
                        @complete="store.searchType($event)"
                        :dropdown="true" optionLabel="name"
                        data-testid="addresses-type"
                        forceSelection>
                    </AutoComplete>
                    <label for="addressess-type">Select Type <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea rows="3" class="w-full"
                              name="addresses-address_line_1"
                              data-testid="addresses-address_line_1"
                              v-model="store.item.address_line_1"/>

                    <label for="addressess-address_line_1">Enter a Address Line 1 <span class="text-red-500">*</span> </label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea rows="3" class="w-full"
                              name="addresses-address_line_2"
                              data-testid="addresses-address_line_2"
                              v-model="store.item.address_line_2"/>

                    <label for="addressess-address_line_2">Enter a Address Line 2 </label>

                </FloatLabel>


                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="warehouses-city"
                               data-testid="warehouses-city"
                               v-model="store.item.city"/>
                    <label for="warehouses-city">Select City </label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="warehouses-state"
                               data-testid="warehouses-state"
                               v-model="store.item.state"/>
                    <label for="warehouses-state">Select State </label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <AutoComplete v-model="store.item.country"
                                  value="id"
                                  class="w-full"
                                  data-testid="warehouses-country"
                                  :suggestions="store.country_suggestions"
                                  @complete="store.searchCountry($event)"
                                  :dropdown="true"
                                  forceSelection />
                    <label for="warehouses-country">Select Country <span class="text-red-500">*</span></label>

                </FloatLabel>


                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <AutoComplete
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        name="addresses-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        value="id"
                        :dropdown="true" optionLabel="name"
                        data-testid="addresses-status"
                        forceSelection>
                    </AutoComplete>
                    <label for="addresses-status">Select Status <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea rows="3" class="w-full"
                              name="orders-status_notes"
                              data-testid="orders-status_notes"
                              v-model="store.item.status_notes"/>
                    <label for="orders-status_notes">Status Notes</label>

                </FloatLabel>

                <div class="flex items-center gap-2 my-3" >
                    <ToggleSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="addresses-active"
                                 data-testid="addresses-active"
                                 v-model="store.item.is_default"/>
                    <label for="addresses-active">Is Active</label>
                </div>

            </div>
        </Panel>


</template>

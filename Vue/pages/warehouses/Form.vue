<script setup>
import {onMounted, ref, watch} from "vue";
import { useWarehouseStore } from '../../stores/store-warehouses'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useWarehouseStore();
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
                            data-testid="warehouses-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="warehouses-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="warehouses-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="warehouses-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="warehouses-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="pt-2">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="warehouses-name"
                               data-testid="warehouses-name"
                               placeholder="Enter Name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="warehouses-slug"
                               data-testid="warehouses-slug"
                               placeholder="Enter Slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Vendor">
                    <AutoComplete v-model="store.item.vendor"
                                  @change="store.setVendor($event)"
                                  value="id"
                                  class="w-full"
                                  data-testid="warehouses-vendor"
                                  :suggestions="store.vendor_suggestion"
                                  @complete="store.searchVendors($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Vendor"
                                  forceSelection />
                </VhField>

                <VhField label="Country">
                    <AutoComplete v-model="store.item.country"
                                  value="id"
                                  class="w-full"
                                  data-testid="warehouses-country"
                                  :suggestions="store.country_suggestion"
                                  @complete="store.searchCountry($event)"
                                  :dropdown="true"
                                  placeholder="Select Country"
                                  forceSelection />
                </VhField>

                <VhField label="State">
                    <InputText class="w-full"
                               name="warehouses-state"
                               data-testid="warehouses-state"
                               placeholder="Enter State"
                               v-model="store.item.state"/>
                </VhField>

                <VhField label="City">
                    <InputText class="w-full"
                               name="warehouses-city"
                               data-testid="warehouses-city"
                               placeholder="Enter City"
                               v-model="store.item.city"/>
                </VhField>

                <VhField label="Status">
                    <AutoComplete v-model="store.item.status"
                                  @change="store.setStatus($event)"
                                  value="id"
                                  class="w-full"
                                  data-testid="warehouses-taxonomy_status"
                                  :suggestions="store.status_suggestion"
                                  @complete="store.searchStatus($event)"
                                  :dropdown="true"
                                  placeholder="Select Status"
                                  optionLabel="name"
                                  forceSelection />
                </VhField>

                <VhField label="Status notes">
                    <Textarea v-model="store.item.status_notes"
                              data-testid="warehouses-taxonomy_status_notes"
                              :autoResize="true"
                              rows="3" class="w-full"
                              placeholder="Enter Status Note"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 name="warehouses-active"
                                 data-testid="warehouses-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

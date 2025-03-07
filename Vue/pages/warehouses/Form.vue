<script setup>
import {onMounted, ref, watch} from "vue";
import { useWarehouseStore } from '../../stores/store-warehouses'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import { useRootStore } from '@/stores/root.js'
import {useRoute} from 'vue-router';


const store = useWarehouseStore();
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
                            data-testid="warehouses-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>
                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="warehouses-save"
                            @click="store.itemAction('save')"
                            :disabled="store.assets.is_guest_impersonating"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="warehouses-create-and-new"
                            :disabled="store.assets.is_guest_impersonating"
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
                        :disabled="store.assets.is_guest_impersonating"
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


            <div v-if="store.item" class="mt-2">
                <Message severity="error"
                         class="p-container-message mb-3"
                         :closable="false"
                         icon="pi pi-trash"
                         v-if="store.item.deleted_at">

                    <div class="flex align-items-center justify-content-between">

                        <div class="">
                            Trashed {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="articles-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="warehouses-name"
                               data-testid="warehouses-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                    <label for="warehouses-name">Name <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="warehouses-slug"
                               data-testid="warehouses-slug"
                               v-model="store.item.slug"/>
                    <label for="warehouses-slug">Slug <span class="text-red-500">*</span></label>

                </FloatLabel>


                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <AutoComplete
                        value="id"
                        v-model="store.item.vendor"
                        @change="store.setVendor($event)"
                        class="w-full"
                        :suggestions="store.vendor_suggestions"
                        @complete="store.searchActiveVendor($event)"
                        data-testid="warehouses-vendor"
                        name="warehouses-vendor"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>
                    <label for="warehouses-vendor">Select Vendor <span class="text-red-500">*</span></label>

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
                    <InputText class="w-full"
                               name="warehouses-state"
                               data-testid="warehouses-state"
                               v-model="store.item.state"/>
                    <label for="warehouses-state">Select State</label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="warehouses-city"
                               data-testid="warehouses-city"
                               v-model="store.item.city"/>
                    <label for="warehouses-city">Select City</label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea rows="3" class="w-full"
                              name="warehouses-address_1"
                              data-testid="warehouses-address_1"
                              v-model="store.item.address_1"/>

                    <label for="warehouses-address_1">Enter a Address Line 1 <span class="text-red-500">*</span> </label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea rows="3" class="w-full"
                              name="warehouses-address_2"
                              data-testid="warehouses-address_2"
                              v-model="store.item.address_2"/>

                    <label for="warehouses-address_2">Enter a Address Line 2 </label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputNumber id="number-input"
                                 name="warehouses-postal_code"
                                 :useGrouping="false"
                                 data-testid="warehouses-postal_code"
                                 v-model="store.item.postal_code"
                                 class="w-full"/>
                    <label for="warehouses-postal_code">Enter Postal Code </label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <AutoComplete v-model="store.item.status"
                                  @change="store.setStatus($event)"
                                  value="id"
                                  class="w-full"
                                  data-testid="warehouses-taxonomy_status"
                                  :suggestions="store.status_suggestions"
                                  @complete="store.searchStatus($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  forceSelection />
                    <label for="warehouses-taxonomy_status">Select Status <span class="text-red-500">*</span> </label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea v-model="store.item.status_notes"
                              data-testid="warehouses-taxonomy_status_notes"
                              :autoResize="true"
                              rows="3" class="w-full"
                              />
                    <label for="warehouses-taxonomy_status_notes">Enter Status Note</label>

                </FloatLabel>

                <div class="flex items-center gap-2 my-3" >
                    <ToggleSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="warehouses-active"
                                 data-testid="warehouses-active"
                                 v-model="store.item.is_active"/>
                    <label for="addresses-active">Is Active</label>
                </div>

            </div>
        </Panel>


</template>

<script setup>
import {onMounted, ref, watch} from "vue";
import { useVendorStore } from '../../stores/store-vendors'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useVendorStore();
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
                            data-testid="vendors-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="vendors-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="vendors-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="vendors-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="vendors-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="vendors-name"
                               placeholder="Enter Name"
                               data-testid="vendors-name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               placeholder="Enter Slug"
                               name="vendors-slug"
                               data-testid="vendors-slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Store">
                    <AutoComplete v-model="store.item.store_record"
                                  @change="store.setStore($event)"
                                  value="id"
                                  class="w-full"
                                  placeholder="Select Store"
                                  data-testid="vendors-vh_st_store_id"
                                  :suggestions="store.store_suggestions"
                                  @complete="store.searchStore($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}<span v-if="slotProps.option.is_default == 1"> (Default) </span></div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Approve By">
                    <AutoComplete v-model="store.item.approved_by_user"
                                  @change="store.setApprovedBy($event)"
                                  value="id"
                                  class="w-full"
                                  placeholder="Select Approve By"
                                  data-testid="vendors-approved_by"
                                  name="vendors-approved_by"
                                  :suggestions="store.approved_by_suggestions"
                                  @complete="store.searchApprovedBy($event)"
                                  :dropdown="true"
                                  optionLabel="first_name"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}  </div>
                                <span>&nbsp;-&nbsp;{{slotProps.option.email}}</span>
                            </div>
                        </template>
                    </AutoComplete>

                </VhField>

                <VhField label="Owned By">
                    <AutoComplete v-model="store.item.owned_by_user"
                                  @change="store.setOwnedBy($event)"
                                  value="id"
                                  class="w-full"
                                  data-testid="vendors-owned_by"
                                  name="vendors-owned_by"
                                  :suggestions="store.owned_by_suggestions"
                                  @complete="store.searchOwnedBy($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Owned By"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}  </div>
                                <span>&nbsp;-&nbsp;{{slotProps.option.email}}</span>
                            </div>
                        </template>
                    </AutoComplete>

                </VhField>

                <VhField label="Status">
                    <AutoComplete v-model="store.item.status_record"
                                  @change="store.setStatus($event)"
                                  value="id"
                                  data-testid="vendors-taxonomy_id_vendor_status"
                                  name="vendors-taxonomy_id_vendor_status"
                                  class="w-full"
                                  placeholder="Select Status"
                                  :suggestions="store.vendor_status_suggestions"
                                  @complete="store.searchStatus($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  forceSelection />

                </VhField>

                <VhField label="Status Notes">
                    <Textarea placeholder="Enter Status Note"
                              v-model="store.item.status_notes" rows="3" class="w-full"
                              data-testid="vendors-status_notes" name="vendors-status_notes" />
                </VhField>

                <VhField label="Is Default">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="vendors-default"
                                 data-testid="vendors-default"
                                 v-model="store.item.is_default"/>
                </VhField>

                <VhField label="Auto Approve Products">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="vendors-auto-approve-products"
                                 data-testid="vendors-auto_approve_products"
                                 v-model="store.item.auto_approve_products"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="vendors-is_active"
                                 data-testid="vendors-is_active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

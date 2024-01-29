<script setup>
import {onMounted, ref, watch} from "vue";
import { useVendorStore } from '../../stores/store-vendors'
import Editor from 'primevue/editor';
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';
import FileUploader from './components/FileUploader.vue'

const store = useVendorStore();
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

const handleFileUploaded = (responseData) => {

    console.log(responseData.data.name);

    const file_name =responseData.data.name;

    const path = responseData.data.path;
    console.log(path);

    if (responseData.data && responseData.data.url) {

        store.item.business_document_file = responseData.data.url

    }
    else {

        console.error('image not found');
    }

   /* response.value = JSON.stringify(responseData, null, 2);*/

};


const removeImage = () => {

    store.item.file_link = '';
};

const getFileExtension = (filePath) => {
    const parts = filePath.split('.');
    return parts[parts.length - 1];
};

watch(() => store.item.name, (item_name) => {
    try {
        if (item_name.length === 0) {
            store.item.slug = '';
        }
    } catch (error) {
    }
});
//--------/form_menu

const permissions=store.assets.permissions;

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
                            data-testid="vendors-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="vendors-save"
                            @click="store.itemAction('save')"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="vendors-create-and-new"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            icon="pi pi-save"/>
                    <Button data-testid="vendors-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            class="p-button-sm"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="vendors-form-menu"
                        icon="pi pi-angle-down"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="vendors-to-list"
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


                <VhField label="Name*">
                    <InputText class="w-full"
                               name="vendors-name"
                               placeholder="Enter Name"
                               data-testid="vendors-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug*">
                    <InputText class="w-full"
                               placeholder="Enter Slug"
                               name="vendors-slug"
                               data-testid="vendors-slug"
                               v-model="store.item.slug"/>
                </VhField>


                <VhField label="Store*">
                    <AutoComplete v-model="store.item.store"
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

                <VhField label="Business Type*">

                    <Dropdown v-model="store.item.business_type"
                              @change="store.setBusinessType($event)"
                              :options="store.business_types_list"
                              data-testid="vendors-business-types"
                              optionLabel="name"
                              placeholder="Select a Business type"
                              class="w-full" />
                </VhField>


                <VhField label="Approved By*">
                    <AutoComplete v-model="store.item.approved_by_user"
                                  @change="store.setApprovedBy($event)"
                                  value="id"
                                  class="w-full"
                                  placeholder="Select Approved By"
                                  data-testid="vendors-approved_by"
                                  name="vendors-approved_by"
                                  :suggestions="store.approved_by_suggestions"
                                  @complete="store.searchApprovedBy($event)"
                                  :dropdown="true"
                                  optionLabel="first_name"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.first_name }}  </div>
                            </div>
                        </template>
                    </AutoComplete>

                </VhField>

                <VhField label="Owned By*">
                    <AutoComplete v-model="store.item.owned_by_user"
                                  @change="store.setOwnedBy($event)"
                                  value="id"
                                  class="w-full"
                                  data-testid="vendors-owned_by"
                                  name="vendors-owned_by"
                                  :suggestions="store.owned_by_suggestions"
                                  @complete="store.searchOwnedBy($event)"
                                  :dropdown="true"
                                  optionLabel="first_name"
                                  placeholder="Select Owned By"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.first_name }}  </div>
                            </div>
                        </template>
                    </AutoComplete>

                </VhField>

                <VhField label="Status*">
                    <AutoComplete v-model="store.item.status"
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



                <VhField label="Years in Business">

                    <InputNumber class="w-full"
                               name="vendors-business-years"
                               placeholder="Enter years in business"
                               data-testid="vendors-name"
                               v-model="store.item.years_in_business"
                                />
                </VhField>

                <VhField label="Services Offered">
                    <Editor
                        class="w-full"
                        name="vendors-services-offered"
                        placeholder="Services description"
                        data-testid="vendors-services-offered"
                        v-model="store.item.services_offered"
                        editorStyle="min-height: 80px"
                        >
                        <template v-slot:toolbar>
                            <span class="ql-formats">
                                <button v-tooltip.bottom="'Bold'" class="ql-bold"></button>
                                <button v-tooltip.bottom="'Italic'" class="ql-italic"></button>
                                <button v-tooltip.bottom="'Underline'" class="ql-underline"></button>
                            </span>
                        </template>
                    </Editor>

                </VhField>

                <Accordion class="mt-3 mb-3">

                    <AccordionTab header="Contact Info" style="margin-top:0;margin-bottom:0">

                        <VhField label="Country Code">
                            <InputText class="w-full"
                                       name="vendors-country-code"
                                       placeholder="Enter Country Code"
                                       data-testid="vendors-country-code"
                                       v-model="store.item.country_code"/>
                        </VhField>

                        <VhField label="Phone Number">
                            <InputNumber class="w-full"
                                         name="vendors-phone-number"
                                         placeholder="Enter your phone number"
                                         data-testid="vendors-phone-number"
                                         v-model="store.item.phone_number"
                                         inputmode="numeric"
                                         pattern="[0-9]*"
                                        />
                        </VhField>

                        <VhField label="Email">
                            <InputText class="w-full"
                                       name="vendors-email"
                                       placeholder="Enter Email"
                                       data-testid="vendors-email"
                                       v-model="store.item.email"/>
                        </VhField>

                        <VhField label="Address">
                                <Textarea
                                    class="w-full"
                                    name="vendors-address"
                                    placeholder="Enter Address"
                                    data-testid="vendors-addresses"
                                    v-model="store.item.address"
                                    rows="3"
                                    cols="30" />
                        </VhField>

                    </AccordionTab>

                </Accordion>
                <Accordion class="mt-3 mb-3">

                    <AccordionTab header="Business Details" style="margin-top:0;margin-bottom:0">
                        <VhField label="Document Type">
                            <InputText class="w-full"
                                         name="vendors-document-type"
                                         placeholder="Enter Document Type"
                                         data-testid="vendors-document-type"
                                         v-model="store.item.business_document_type"/>
                        </VhField>

                        <VhField label="Document Details">
                            <InputText class="w-full"
                                       name="vendors-document-detail"
                                       placeholder="e.g registration number"
                                       data-testid="vendors-document-detail"
                                       v-model="store.item.business_document_detail"/>
                        </VhField>

                            <VhField label="Upload File">

                                <FileUploader
                                    placeholder="Upload document"
                                              v-model="store.item.business_document_file"
                                              :is_basic="true"
                                              data-testid="vendors-document-image"
                                              :auto_upload="true"
                                              :uploadUrl="store.assets.urls.upload"
                                              @fileUploaded="handleFileUploaded">

                                </FileUploader>



                                <template v-if="store.item.business_document_file">
                                    <template v-if="['png', 'jpg', 'jpeg'].includes(getFileExtension(store.item.business_document_file))">
                                        <img :src="store.item.business_document_file"
                                             style="width: auto !important;
                                             height: auto !important;
                                              max-width: 100%;"
                                             alt="Uploaded Image"/>
                                    </template>
                                    <template v-else>
                                        <a :href="store.item.business_document_file" :src="store.item.business_document_file">Download</a>
                                    </template>
                                </template>
                                <Button v-if="store.item.business_document_file"
                                        icon="pi pi-times"
                                        severity="danger"
                                        @click="store.removeImage()"
                                        text rounded aria-label="Cancel"
                                        class="close-button"
                                />

                            </VhField>



                    </AccordionTab>

                </Accordion>

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

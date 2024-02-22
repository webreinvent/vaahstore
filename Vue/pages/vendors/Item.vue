<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useVendorStore } from '../../stores/store-vendors'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
const store = useVendorStore();
const route = useRoute();

onMounted(async () => {

    /**
     * If record id is not set in url then
     * redirect user to list view
     */
    if(route.params && !route.params.id)
    {
        store.toList();
        return false;
    }

    /**
     * Fetch the record from the database
     */
    if(!store.item || Object.keys(store.item).length < 1)
    {
        await store.getItem(route.params.id);
    }

    /**
     * Watch if url record id is changed, if changed
     * then fetch the new records from database
     */

});

//--------toggle item menu
const item_menu_state = ref();
const toggleItemMenu = (event) => {
    item_menu_state.value.toggle(event);
};
//--------/toggle item menu


const visible = ref(false);

const getFileExtension = (filePath) => {
    const parts = filePath.split('.');
    return parts[parts.length - 1];
};

const permissions=store.assets.permissions;

</script>
<template>

    <div class="col-6" >

        <Panel class="is-small" v-if="store && store.item">

            <template class="p-1" #header>

                <div class="flex flex-row">

                    <div class="p-panel-title">
                        #{{store.item.id}}
                    </div>

                </div>

            </template>

            <template #icons>


                <div class="p-inputgroup">

                    <Button label="Edit"
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="vendors-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="vendors-item-menu"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        icon="pi pi-angle-down"

                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="vendors-item-to-list"
                            @click="store.toList()"/>

                </div>



            </template>


            <div class="mt-2" v-if="store.item">
                <Message severity="info" :closable="false" v-if="store.item.status_notes">
                    <div>
                        <pre style="white-space: pre-wrap">{{ store.item.status_notes }}</pre>
                    </div>
                </Message>

                <Message severity="error"
                         class="p-container-message"
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
                                    data-testid="vendors-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                    <table class="p-datatable-table">
                        <tbody class="p-datatable-tbody">
                        <template v-for="(value, column) in store.item ">

                            <template v-if="column === 'created_by' || column === 'updated_by' || column === 'store'
                                       || column === 'status'|| column === 'approved_by_user' || column === 'deleted_by'
                                       || column === 'owned_by_user'|| column === 'vendor_products' || column === 'meta'
                                       || column === 'products' || column === 'taxonomy_id_vendor_business_type'
                                       || column === 'business_type' || column === 'store' || column === 'vh_st_store_id'
                                       || column === 'approved_by_user' || column === 'status_notes'
                                       || column === 'years_in_business' || column === 'services_offered'
                                       || column === 'owned_by' || column === 'taxonomy_id_vendor_services'
                                       || column === 'approved_by' || column === 'email' || column === 'registered_at' || column === 'approved_at'
                                       || column === 'address' || column ==='business_document_detail' || column === 'business_document_file'
                                       || column === 'country_code' || column === 'taxonomy_id_vendor_status' || column === 'users'">
                            </template>

                            <template v-else-if="column === 'id' || column === 'uuid'">
                                <VhViewRow :label="column"
                                           :value="value"
                                           :can_copy="true"
                                />
                            </template>
                            <template v-else-if="column === 'name'">
                                <tr>
                                    <td>
                                        <b>Name</b>
                                    </td>
                                    <td colspan="2" >
                                        <div style=" overflow-wrap: break-word; word-wrap:break-word;">
                                            {{store.item.name}}</div>
                                    </td>
                                </tr>
                            </template>
                            <template v-else-if="column === 'slug'">
                                <tr>
                                    <td>
                                        <b>Slug</b>
                                    </td>
                                    <td colspan="2" >
                                        <div style="  overflow-wrap: break-word; word-wrap:break-word;">
                                            {{store.item.slug}}</div>
                                    </td>
                                </tr>

                                <tr v-if="store.item.store">
                                    <td>
                                        <b>Store</b>
                                    </td>
                                    <td colspan="2" >
                                        {{store.item.store.name}}
                                    </td>
                                </tr>


                                <tr v-if="store.item.business_type && store.item.business_type.name">
                                    <td>
                                        <b>Business Type</b>
                                    </td>
                                    <td colspan="2">
                                        {{ store.item.business_type.name }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <b>Approved By</b>
                                    </td>
                                    <td colspan="2" >
                                        <Button class="p-button-outlined p-button-secondary p-button-sm">
                                            {{store.item.approved_by_user.first_name}}
                                        </Button>
                                    </td>
                                </tr>

                                <tr v-if="store.item.owned_by_user && store.item.owned_by_user.name">
                                    <td>
                                        <b>Owned By</b>
                                    </td>
                                    <td colspan="2" >
                                        <Button class="p-button-outlined p-button-secondary p-button-sm">
                                            {{store.item.owned_by_user.name}}
                                        </Button>
                                    </td>
                                </tr>

                                <tr v-if="store.item.years_in_business">
                                    <td>
                                        <b>Years in Business</b>
                                    </td>
                                    <td colspan="2" >
                                        {{store.item.years_in_business}}
                                    </td>
                                </tr>

                                <tr v-if="store.item.services_offered">
                                    <td>
                                        <b>Services Offered</b>
                                    </td>
                                    <td colspan="2">
                                        <div v-html="store.item.services_offered"></div>
                                    </td>
                                </tr>


                            </template>

                            <template v-else-if="column === 'phone_number'">
                                <tr>
                                    <td colspan="3">
                                        <Accordion class="mt-3 mb-3">

                                            <AccordionTab header="Contact Info">

                                                <VhField label="Country Code">
                                                    <InputText class="w-full"
                                                               name="vendors-country-code"
                                                               placeholder="Enter Country Code"
                                                               data-testid="vendors-country-code"
                                                               v-model="store.item.country_code"
                                                               readonly />
                                                </VhField>

                                                <VhField label="Phone Number">
                                                    <InputText class="w-full"
                                                               name="vendors-phone-number"
                                                               placeholder="Enter your phone number"
                                                               data-testid="vendors-phone-number"
                                                               v-model="store.item.phone_number"
                                                               inputmode="numeric"
                                                               pattern="[0-9]*"
                                                               readonly
                                                    />
                                                </VhField>

                                                <VhField label="Email">
                                                    <InputText class="w-full"
                                                               name="vendors-email"
                                                               placeholder="Enter Email"
                                                               data-testid="vendors-email"
                                                               v-model="store.item.email"
                                                               readonly/>
                                                </VhField>

                                                <VhField label="Address">
                                                    <Textarea
                                                        class="w-full"
                                                        name="vendors-address"
                                                        placeholder="Enter Address"
                                                        data-testid="vendors-addresses"
                                                        v-model="store.item.address"
                                                        rows="3"
                                                        cols="30"
                                                        readonly/>
                                                </VhField>

                                            </AccordionTab>

                                        </Accordion>
                                    </td>

                                </tr>

                            </template>
                            <template v-else-if="column === 'business_document_type'">
                                <tr>
                                    <td colspan="3">
                                        <Accordion class="mt-3 mb-3">

                                            <AccordionTab header="Business Details">
                                                <VhField label="Document Type">
                                                    <InputText class="w-full"
                                                               name="vendors-document-type"
                                                               placeholder="Enter Document Type"
                                                               data-testid="vendors-document-type"
                                                               readonly
                                                               v-model="store.item.business_document_type"/>
                                                </VhField>

                                                <VhField label="Document Details">
                                                    <InputText class="w-full"
                                                               name="vendors-document-detail"
                                                               placeholder="e.g registration number"
                                                               data-testid="vendors-document-detail"
                                                               readonly
                                                               v-model="store.item.business_document_detail"/>
                                                </VhField>

                                                <VhField label="Upload File">


                                                    <Button label="Show"
                                                            icon="pi pi-external-link"
                                                            @click="visible = true"
                                                            :disabled="!store.item.business_document_file"  />

                                                    <Dialog v-model:visible="visible" modal header="File" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">

                                                        <template v-if="store.item.business_document_file">
                                                            <template v-if="['png', 'jpg', 'jpeg'].includes(getFileExtension(store.item.business_document_file))">
                                                                <img :src="store.item.business_document_file" style="width: auto !important; height: auto !important; max-width: 100%;" alt="Uploaded Image"/>
                                                            </template>
                                                            <template v-else>
                                                                <a :href="store.item.business_document_file" :src="store.item.business_document_file">Download</a>
                                                            </template>
                                                        </template>


                                                    </Dialog>




                                                </VhField>

                                            </AccordionTab>

                                        </Accordion>
                                    </td>

                                </tr>

                                <tr v-if="store.item.status && store.item.status.name">
                                    <td>
                                        <b>Status</b>
                                    </td>
                                    <td colspan="2" >
                                        <Badge v-if="store.item.status && store.item.status.name == 'Approved'"
                                               severity="success"> {{store.item.status.name}} </Badge>
                                        <Badge v-else-if="store.item.status &&store.item.status.name == 'Rejected'"
                                               severity="danger"> {{store.item.status.name}} </Badge>
                                        <Badge v-else
                                               severity="warning"> {{store.item.status.name}} </Badge>
                                    </td>
                                </tr>

                            </template>

                            <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'
                        || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                                <VhViewRow :label="column"
                                           :value="value"
                                           type="user"
                                />
                            </template>

                            <template v-else-if="column === 'auto_approve_products'">
                                <VhViewRow :label="column"
                                           :value="value"
                                           type="yes-no"
                                />
                            </template>

                            <template v-else-if="column === 'is_active'">
                                <VhViewRow label="Is Active"
                                           :value="value"
                                           type="yes-no"
                                />
                            </template>

                            <template v-else-if="column === 'is_default'">
                                <VhViewRow label="Is Default"
                                           :value="value"
                                           type="yes-no"
                                />
                            </template>



                            <template v-else-if="column === 'owned_by'">
                                <VhViewRow :label="column"
                                           :value="store.item.owned_by_user"
                                           type="userEmail"
                                />
                            </template>

                            <template v-else>
                                <VhViewRow :label="column"
                                           :value="value"
                                />
                            </template>


                        </template>
                        </tbody>

                    </table>

                </div>
            </div>
        </Panel>

    </div>

</template>

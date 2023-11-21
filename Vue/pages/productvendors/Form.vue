<script setup>
import {onMounted, ref, watch} from "vue";
import { useProductVendorStore } from '../../stores/store-productvendors'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductVendorStore();
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
                            data-testid="productvendors-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="productvendors-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="productvendors-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="productvendors-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            class="p-button-sm"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="productvendors-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="productvendors-to-list"
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


                <VhField label="Vendor" >
                    <div class="p-inputgroup">
                    <AutoComplete
                        value="id"
                        v-model="store.item.vendor"
                        @change="store.setVendor($event)"
                        :suggestions="store.vendor_suggestion"
                        @complete="store.searchVendor($event)"
                        placeholder="Select Vendor"
                        data-testid="productvendors-vendor"
                        name="productvendors-vendor"
                        :dropdown="true" optionLabel="name" forceSelection
                    >
                    </AutoComplete>
                    <Button v-tooltip.left="'Vendor will be able to manage store'" icon="pi pi-info-circle" />
                    </div>
                </VhField>

                <VhField label="Store">
                    <MultiSelect class="w-full"
                                 v-model="store.item.store_vendor_product"
                                 display="chip"
                                 data-testid="productvendors-stores"
                                 name="productvendors-stores"
                                 :options="store.assets.active_stores"
                                 optionLabel="name"
                                 placeholder="Select Stores"
                                 :maxSelectedLabels="3"
                                 @change="store.getProductsListForStore()" />
                </VhField>

                <VhField label="Product" v-if="store.item.store_vendor_product && store.item.store_vendor_product.length > 0">
                    <AutoComplete v-model="store.item.product"
                                  @change="store.setProduct($event)"
                                  value="id"
                                  @complete="store.searchProduct($event)"
                                  :suggestions="store.product_suggestion"
                                  class="w-full"
                                  placeholder="Select Product"
                                  data-testid="productvendors-product"
                                  name="productvendors-product"
                                  :dropdown="true"
                                  optionLabel="name"
                                  forceSelection>
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }} - {{ slotProps.option.store.name }}</div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Can Update">
                    <div class="flex flex-row">
                        <div class="col-4">
                            <div class="p-selectbutton p-buttonset p-component" role="group" aria-labelledby="single" style="box-shadow: none;">
                                <div role="radio" class="p-button p-component" :class="store.item.can_update == 0 ? 'bg-red-500 text-white' : ''">
                                    <span data-testid="productvendors-can_update" name="productvendors-can_update" class="p-button-label" @click="store.item.can_update = 0">no</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                                <div role="radio" class="p-button p-component"  :class="store.item.can_update == 1 ? 'p-highlight' : ''">
                                    <span data-testid="productvendors-can_update" name="productvendors-can_update" class="p-button-label" @click="store.item.can_update = 1">yes</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </VhField>

                <VhField label="Added By">
                    <AutoComplete
                        value="id"
                        v-model="store.item.added_by_user"
                        @change="store.setAddedBy($event)"
                        class="w-full"
                        name="productvendors-added_by"
                        id="added_by"
                        data-testid="productvendors-added_by"
                        :suggestions="store.added_by_suggestion"
                        @complete="store.searchAddedBy($event)"
                        placeholder="Select Added by"
                        :dropdown="true"
                        optionLabel="first_name"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status">

                    <AutoComplete
                        value="id"
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        data-testid="productvendors-status"
                        name="productvendors-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true"
                        optionLabel="name"
                        forceSelection>
                    </AutoComplete>

                </VhField>


                <VhField label="Status Notes">
                    <Textarea  rows="3" class="w-full"
                               placeholder="Enter a Status Note"
                               name="productvendors-status_notes"
                               data-testid="productvendors-status_notes"
                               v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="productvendors-active"
                                 data-testid="productvendors-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

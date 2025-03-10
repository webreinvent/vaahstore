<script setup>
import {onMounted, ref, watch} from "vue";
import { useProductVendorStore } from '../../stores/store-productvendors'
import { useRootStore } from '@/stores/root.js'
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductVendorStore();
const route = useRoute();
const root = useRootStore();
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
                            v-tooltip.left="'View'"
                            v-if="store.item && store.item.id"
                            data-testid="productvendors-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="productvendors-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
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
                        :disabled="!store.assets.permissions.includes('can-update-module')"
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

                <VhField label="Vendor*" >
                    <div class="p-inputgroup">
                    <AutoComplete
                        value="id"
                        v-model="store.item.vendor"
                        @change="store.setVendor($event)"
                        :suggestions="store.active_vendors_list"
                        @complete="store.searchVendor($event)"
                        :pt="{
                                       panel: { class: 'w-16rem ' },
                                       item: { style: {
                                                    textWrap: 'wrap'
                                                }  }
                                  }"
                        placeholder="Select Vendor"
                        data-testid="productvendors-vendor"
                        name="productvendors-vendor"
                        :dropdown="true" optionLabel="name" forceSelection
                    >
                    </AutoComplete>
                    <Button v-tooltip.left="'Vendor will be able to manage store'" icon="pi pi-info-circle" />
                    </div>
                </VhField>

                <VhField label="Store*">
                        <AutoComplete
                            data-testid="productvendors-stores"
                            v-model="store.item.store_vendor_product"
                            optionLabel="name"
                            multiple
                            :dropdown="true"
                            :complete-on-focus = "true"
                            :pt="{
                                      token: {
                                        class: 'max-w-full'
                                      },
                                      removeTokenIcon: {
                                          class: 'min-w-max'
                                      },
                                      item: { style: {
                                                    textWrap: 'wrap'
                                                }  },
                                       panel: { class: 'w-16rem ' }
                                  }"
                            :suggestions="store.active_stores"
                            @complete="store.searchActiveStores($event)"
                            placeholder="Select Stores "
                            @change="store.setStores($event)"
                            class="w-full "

                        />
                </VhField>

                <VhField label="Product*" >
                    <AutoComplete v-model="store.item.product"
                                  @change="store.setProduct($event)"
                                  value="id"
                                  @complete="store.getProductsListForStore($event)"
                                  :suggestions="store.product_suggestion"
                                  class="w-full"
                                  placeholder="Select Product"
                                  data-testid="productvendors-product"
                                  name="productvendors-product"
                                  :dropdown="true"
                                  :pt="{
                                       panel: { class: 'w-16rem ' },
                                       item: { style: {
                                                    textWrap: 'wrap'
                                                }  }
                                  }"
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
                                    <span data-testid="productvendors-can_update" name="productvendors-can_update" class="p-button-label" @click="store.item.can_update = 0">No</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                                <div role="radio" class="p-button p-component"  :class="store.item.can_update == 1 ? 'p-highlight' : ''">
                                    <span data-testid="productvendors-can_update" name="productvendors-can_update" class="p-button-label" @click="store.item.can_update = 1">Yes</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </VhField>

                <VhField label="Added By*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.added_by_user"
                        @change="store.setAddedBy($event)"
                        class="w-full"
                        name="productvendors-added_by"
                        id="added_by"
                        data-testid="productvendors-added_by"
                        :suggestions="store.active_users_list"
                        @complete="store.searchAddedBy($event)"
                        placeholder="Select Added by"
                        :dropdown="true"
                        optionLabel="first_name"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status*">

                    <AutoComplete
                        value="id"
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        data-testid="productvendors-status"
                        name="productvendors-status"
                        :suggestions="store.status_suggestion_list"
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
                    <ToggleSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="productvendors-active"
                                 data-testid="productvendors-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

<!--    </div>-->

</template>

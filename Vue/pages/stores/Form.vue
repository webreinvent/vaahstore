<script setup>
import {onMounted, ref, watch} from "vue";
import { useStoreStore } from '../../stores/store-stores'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useStoreStore();
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
                            data-testid="stores-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="stores-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="stores-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="stores-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="stores-to-list"
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
                            Deleted {{store.item.deleted_at}}
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
                               placeholder="Enter Name"
                               name="stores-name"
                               data-testid="stores-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug*">
                    <InputText class="w-full"
                               placeholder="Enter Slug"
                               name="stores-slug"
                               data-testid="stores-slug"
                               v-model="store.item.slug"/>
                </VhField>


                <VhField label="Is Multi Currency">

                    <div class="flex flex-row">
                        <div class="col-3">
                            <InputSwitch v-bind:false-value="0"
                                         v-bind:true-value="1"
                                         class="p-inputswitch"
                                         name="stores-multi-currency"
                                         data-testid="stores-multi-currency"
                                         v-model="store.item.is_multi_currency"/>
                        </div>

                        <div v-if="store.item && store.item.currencies && store.item.currencies.length >= 1" class="pl-5 col-9 flex flex-row">
                            <Dropdown v-model="store.item.default_currency"
                                      :options="store.item.currencies"
                                      data-testid="store-default_currency"
                                      filter
                                      optionLabel="name"
                                      placeholder="Select Default Currency"
                                      style="width:180px"
                                      >
                                <template #option="slotProps">
                                    <div class="flex align-items-center">
                                        <span>&nbsp;&nbsp&nbsp;{{slotProps.option.name}}</span>
                                    </div>
                                </template>
                            </Dropdown>
                            <Button
                                    v-if="store.item && store.item.default_currency"
                                    @click="store.item.default_currency = null"
                                    class="p-button-sm"
                                    data-testid="store-remove-default-currency"
                                    icon="pi pi-times"
                                    style="width:30px;"
                                    />
                        </div>
                    </div>
                </VhField>

                <VhField label="Currencies*" v-show="store.item.is_multi_currency == 1">

                    <AutoComplete name="store-currencies"
                                  data-testid="store-currencies"
                                  v-model="store.item.currencies"
                                  option-label ="name"
                                  multiple
                                  placeholder="Select Currencies"
                                  :complete-on-focus = "true"
                                  :suggestions="store.currency_suggestion_list"
                                  @change = "store.addCurrencies()"
                                  @complete="store.searchCurrencies"
                                  class="w-full"
                    />


                </VhField>

                <VhField label="Is Multi Lingual">
                    <div class="flex flex-row">
                        <div class="col-3">
                            <InputSwitch v-bind:false-value="0"
                                         v-bind:true-value="1"
                                         class="p-inputswitch"
                                         name="stores-multi-currency"
                                         data-testid="stores-multi-currency"
                                         v-model="store.item.is_multi_lingual"/>
                        </div>

                        <div v-if="store.item && store.item.languages && store.item.languages.length >= 1" class="pl-5 col-9 flex flex-row">
                            <Dropdown v-model="store.item.default_language"
                                      :options="store.item.languages"
                                      data-testid="store-language_default"
                                      filter
                                      optionLabel="name"
                                      placeholder="Select Default Language"
                                      style="width:180px">
                                <template #option="slotProps">
                                    <div class="flex align-items-center">
                                        <span>{{slotProps.option.name}}</span>
                                    </div>
                                </template>
                            </Dropdown>
                            <Button
                                v-if="store.item && store.item.default_language"
                                @click="store.item.default_language = null"
                                class="p-button-sm"
                                data-testid="store-remove-default-currency"
                                icon="pi pi-times"/>
                        </div>
                    </div>
                </VhField>

                <VhField label="Languages*" v-show="store.item.is_multi_lingual == 1">
                    <AutoComplete name="store-languages"
                                  data-testid="store-languages"
                                  v-model="store.item.languages"
                                  option-label = "name"
                                  multiple
                                  placeholder="Select Languages"
                                  :complete-on-focus = "true"
                                  :suggestions="store.language_suggestion_list"
                                  @change = "store.addLanguages()"
                                  @complete="store.searchLanguages"
                                  class="w-full"
                    />

                </VhField>

                <VhField label="Is Multi Vendor">
                    <div class="flex flex-row">
                        <div class="col-4">
                            <InputSwitch v-bind:false-value="0"
                                         v-bind:true-value="1"
                                         class="p-inputswitch"
                                         name="stores-multi-vendor"
                                         data-testid="stores-multi-vendor"
                                         v-model="store.item.is_multi_vendor"/>
                        </div>

                    </div>
                </VhField>

                <VhField label="Allowed IPs">
                    <Chips class="w-full"
                           v-model="store.item.allowed_ips"
                           placeholder="e.g. 192.168.1.1 , 203.23.15.68"
                           data-testid="store-allowed-ips"
                           @keydown.enter = "store.handleNewIP($event)"
                           type="number" />

                </VhField>

                <VhField label="Status*">
                    <AutoComplete v-model="store.item.status"
                                  @change="store.setStatus($event)"
                                  value="id"
                                  class="w-full"
                                  data-testid="store-taxonomy_status"
                                  :suggestions="store.status_suggestion_list"
                                  @complete="store.searchStatus($event)"
                                  :dropdown="true"
                                  placeholder="Select Status"
                                  optionLabel="name"
                                  forceSelection />
                </VhField>
                <VhField label="Status Notes">
                    <Textarea placeholder="Enter Status Note"
                              v-model="store.item.status_notes"
                              data-testid="store-taxonomy_status_notes"
                              :autoResize="true" rows="3" class="w-full" />
                </VhField>
                <VhField label="Is Default">
                    <InputSwitch    v-bind:false-value="0"
                                    v-bind:true-value="1"
                                    class="p-inputswitch"
                                    name="stores-is_default"
                                    data-testid="store-is_default"
                                    v-model="store.item.is_default" />

                </VhField>


                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="stores-active"
                                 @change="store.selectStatus()"
                                 data-testid="stores-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

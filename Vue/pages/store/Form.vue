<script setup>
import {onMounted, ref, watch} from "vue";
import { useStoreStore } from '../../stores/store-store'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useStoreStore();
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
                            data-testid="store-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="store-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="store-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="store-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="store-name"
                               data-testid="store-name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="store-slug"
                               data-testid="store-slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Is Multi Currency">
                    <div class="flex flex-row">
                        <div class="col-4">
                            <div class="p-selectbutton p-buttonset p-component" role="group" aria-labelledby="single">
                                <div role="radio"
                                     class="p-button p-component"
                                     data-testid="store-is_multi_currency_no"
                                     @click="store.item.is_multi_currency = 0;  store.item.currencies = null;"
                                     style="border: none;"
                                     :class="store.item.is_multi_currency == 0 ? 'p-danger' : ''">
                                    <span class="p-button-label">no</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                                <div role="radio"
                                     class="p-button p-component"
                                     data-testid="store-is_multi_currency_yes"
                                     @click="store.item.is_multi_currency = 1"
                                     style="border: none;"
                                     :class="store.item.is_multi_currency == 1 ? 'p-highlight' : ''">
                                    <span class="p-button-label">yes</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>

                        <div v-if="store.item && store.item.currencies && store.item.currencies.length >= 1" class="pl-5 col-8">
                            <Dropdown v-model="store.item.currency_default"
                                      :options="store.item.currencies"
                                      data-testid="store-currency_default"
                                      filter
                                      optionLabel="code"
                                      placeholder="Select default currencys"
                                      class="w-full">
                                <template #option="slotProps">
                                    <div class="flex align-items-center">
                                        <div>{{ slotProps.option.code }}  </div>
                                        <span>&nbsp;-&nbsp;{{slotProps.option.name}}</span>
                                        (<b>{{slotProps.option.symbol}}</b>)
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                </VhField>

                <VhField label="currencies" v-show="store.item.is_multi_currency == 1">
                    <MultiSelect v-model="store.item.currencies"
                                 filter
                                 :options="store.currencies_list"
                                 data-testid="store-currencies"
                                 optionLabel="code"
                                 placeholder="Select currencys"
                                 display="chip"
                                 class="w-full">
                        <template #option="slotProps">
                            <div class="flex align-items-center">
                                <div>{{ slotProps.option.code }}  </div>
                                <span>&nbsp;-&nbsp;{{slotProps.option.name}}</span>
                                (<b>{{slotProps.option.symbol}}</b>)
                            </div>
                        </template>
                        <template #footer>
                            <div class="py-2 px-3">
                                <b>{{ store.item.currencies ? store.item.currencies.length : 0 }}</b>
                                item{{ (store.item.currencies ? store.item.currencies.length : 0) > 1 ? 's' : '' }} selected.
                            </div>
                        </template>
                    </MultiSelect>
                </VhField>

                <VhField label="Is Multi Lingual">
                    <div class="flex flex-row">
                        <div class="col-4">
                            <div class="p-selectbutton p-buttonset p-component" role="group" aria-labelledby="single">
                                <div role="radio"
                                     class="p-button p-component"
                                     data-testid="store-is_multi_lingual_no"
                                     @click="store.item.is_multi_lingual = 0;  store.item.languages = null;"
                                     style="border: none;"
                                     :class="store.item.is_multi_lingual == 0 ? 'p-danger' : ''">
                                    <span class="p-button-label">no</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                                <div role="radio"
                                     class="p-button p-component"
                                     data-testid="store-is_multi_lingual_yes"
                                     @click="store.item.is_multi_lingual = 1"
                                     style="border: none;"
                                     :class="store.item.is_multi_lingual == 1 ? 'p-highlight' : ''">
                                    <span class="p-button-label" >yes</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>

                        <div v-if="store.item && store.item.languages && store.item.languages.length >= 1" class="pl-5 col-8">
                            <Dropdown v-model="store.item.language_default"
                                      :options="store.item.languages"
                                      data-testid="store-language_default"
                                      filter
                                      optionLabel="name"
                                      placeholder="Select default language"
                                      class="w-full">
                                <template #option="slotProps">
                                    <div class="flex align-items-center">
                                        <span>{{slotProps.option.name}}</span>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </VhField>

                <VhField label="Languages" v-show="store.item.is_multi_lingual == 1">
                    <MultiSelect v-model="store.item.languages"
                                 filter
                                 :options="store.languages_list"
                                 data-testid="store-languages"
                                 optionLabel="name"
                                 placeholder="Select languages"
                                 display="chip"
                                 class="w-full">
                        <template #option="slotProps">
                            <div class="flex align-items-center">
                                <span>{{slotProps.option.name}}</span>
                            </div>
                        </template>
                        <template #footer>
                            <div class="py-2 px-3">
                                <b>{{ store.item.languages ? store.item.languages.length : 0 }}</b>
                                item{{ (store.item.languages ? store.item.languages.length : 0) > 1 ? 's' : '' }} selected.
                            </div>
                        </template>
                    </MultiSelect>
                </VhField>

                <VhField label="Is Multi Vendor">
                    <div class="flex flex-row">
                        <div class="col-4">
                            <div class="p-selectbutton p-buttonset p-component" role="group" aria-labelledby="single">
                                <div role="radio"
                                     class="p-button p-component"
                                     data-testid="store-is_multi_lingual_no"
                                     @click="store.item.is_multi_vendor = 0"
                                     style="border: none;"
                                     :class="store.item.is_multi_vendor == 0 ? 'p-danger' : ''">
                                    <span class="p-button-label">no</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                                <div role="radio"
                                     class="p-button p-component"
                                     data-testid="store-is_multi_lingual_yes"
                                     @click="store.item.is_multi_vendor = 1"
                                     style="border: none;"
                                     :class="store.item.is_multi_vendor == 1 ? 'p-highlight' : ''">
                                    <span class="p-button-label">yes</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </VhField>

                <VhField label="Allowed Ips">
                    <Chips class="w-full"
                           v-model="store.item.allowed_ips"
                           separator=","
                           data-testid="store-allowed-ips"
                           type="number" />

                </VhField>

                <VhField label="Status">
                    <AutoComplete v-model="store.item.taxonomy_id_store_status"
                                  class="w-full"
                                  data-testid="store-taxonomy_status"
                                  :suggestions="store.status_suggestion_list"
                                  @complete="store.searchStatus($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  forceSelection />
                </VhField>

                <VhField label="Status Notes">
                    <Textarea v-model="store.item.status_notes" data-testid="store-taxonomy_status_notes" :autoResize="true" rows="5" cols="30" />
                </VhField>

                <VhField label="Store Notes">
                    <Textarea v-model="store.item.notes" data-testid="store-taxonomy_status_notes" :autoResize="true" rows="5" cols="30" />
                </VhField>

                <VhField label="Is Default">
                    <InputSwitch v-model="store.item.is_default" data-testid="store-is_default" />

                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-model="store.item.is_active" data-testid="store-is_active" />

                </VhField>

            </div>
        </Panel>

    </div>

</template>
<style>
.p-selectbutton .p-button.p-highlight {
    background-color: #007ad9 !important;
    font-weight: 700;
}
.p-selectbutton .p-button.p-danger {
    background-color: #ee1742 !important;
    font-weight: 700;
}


</style>

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

    await store.watchCurrency();

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
                                <div role="radio" class="p-button p-component" style="border: none;" :class="store.item.is_multi_currency == 0 ? 'p-danger' : ''">
                                    <span class="p-button-label" @click="store.item.is_multi_currency = 0">no</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                                <div role="radio" class="p-button p-component" style="border: none;" :class="store.item.is_multi_currency == 1 ? 'p-highlight' : ''">
                                    <span class="p-button-label" @click="store.item.is_multi_currency = 1">yes</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>

                        <div v-if="store.showCurrencyDefault || (store.item && store.item.currency && store.item.currency.length > 1)">
                            <AutoComplete v-model="store.item.currency_default"
                                          class="w-full"
                                          data-testid="vendors-approved_by"
                                          name="vendors-approved_by"
                                          :suggestions="store.currency_default_suggestion_list"
                                          @complete="store.searchCurrencyDefault($event)"
                                          optionLabel="name"
                                          placeholder="select a default currency"
                                          forceSelection >
                                <template #option="slotProps">
                                    <div class="flex align-options-center">
                                        <div>{{ slotProps.option.code }}  </div>
                                        <span>&nbsp;-&nbsp;{{slotProps.option.name}}</span>
                                        (<b>{{slotProps.option.symbol}}</b>)
                                    </div>
                                </template>
                            </AutoComplete>
                        </div>
                    </div>

                </VhField>

                <VhField label="Currency" v-show="store.item.is_multi_currency == 1">
                    <AutoComplete v-model="store.item.currency"
                                  class="w-full"
                                  multiple
                                  data-testid="vendors-approved_by"
                                  name="vendors-approved_by"
                                  :suggestions="store.currency_suggestion_list"
                                  @complete="store.searchCurrency($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.code }}  </div>
                                <span>&nbsp;-&nbsp;{{slotProps.option.name}}</span>
                                (<b>{{slotProps.option.symbol}}</b>)
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Is Multi Lingual">
                    <div class="flex flex-row">
                        <div class="col-4">
                            <div class="p-selectbutton p-buttonset p-component" role="group" aria-labelledby="single">
                                <div role="radio" class="p-button p-component" style="border: none;" :class="store.item.is_multi_lingual == 0 ? 'p-danger' : ''">
                                    <span class="p-button-label" @click="store.item.is_multi_lingual = 0">no</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                                <div role="radio" class="p-button p-component" style="border: none;" :class="store.item.is_multi_lingual == 1 ? 'p-highlight' : ''">
                                    <span class="p-button-label" @click="store.item.is_multi_lingual = 1">yes</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>

                        <div v-if="store.item.is_multi_lingual == 1" class="pl-5 col-8">
                            <MultiSelect placeholder="Select Languages" />
                        </div>
                    </div>
                </VhField>

                <VhField label="Is Multi Vendor">
                    <div class="flex flex-row">
                        <div class="col-4">
                            <div class="p-selectbutton p-buttonset p-component" role="group" aria-labelledby="single">
                                <div role="radio" class="p-button p-component" style="border: none;" :class="store.item.is_multi_vendor == 0 ? 'p-danger' : ''">
                                    <span class="p-button-label" @click="store.item.is_multi_vendor = 0">no</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                                <div role="radio" class="p-button p-component" style="border: none;" :class="store.item.is_multi_vendor == 1 ? 'p-highlight' : ''">
                                    <span class="p-button-label" @click="store.item.is_multi_vendor = 1">yes</span>
                                    <span class="p-ink" role="presentation" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>

                        <div v-if="store.item.is_multi_vendor == 1" class="pl-5 col-8">
                            <MultiSelect placeholder="Select Languages" />
                        </div>
                    </div>
                </VhField>

                <VhField label="Allowed Ips">
                    <InputText class="w-full"
                               name="store-allowed-ips"
                               data-testid="store-allowed-ips"
                               v-model="store.item.allowed_ips"/>

                </VhField>

                <VhField label="Is Default">
                    <InputSwitch v-model="store.item.is_default" />

                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-model="store.item.is_active" />

                </VhField>

                <VhField label="Status">
                    <AutoComplete v-model="store.item.taxonomy_id_store_status"
                                  class="w-full"
                                  :suggestions="store.status_suggestion_list"
                                  @complete="store.searchStatus($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  forceSelection />
                </VhField>

                <VhField label="Status Notes">
                    <Textarea v-model="store.item.status_notes" :autoResize="true" rows="5" cols="30" />
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

/*.btn-1{*/
/*    width:45%;*/
/*    float:left;*/
/*    padding:0px 5px 0px 5px;*/
/*    margin-bottom:20px;*/
/*}*/
/*.btn-2{*/
/*    width:45%;*/
/*    float:right;*/
/*    padding:0px 5px 0px 5px;*/
/*    margin-bottom:20px;*/
/*}*/

</style>

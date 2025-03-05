<script setup>
import {onMounted, ref, watch} from "vue";
import {useStoreStore} from '@/stores/store-stores'
import {useRootStore} from '@/stores/root.js'
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useStoreStore();
const route = useRoute();
const root = useRootStore();
onMounted(async () => {
    if (route.params && route.params.id) {
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

                <Button v-tooltip.left="'View'"
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
                      :popup="true"/>
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


            <FloatLabel class="my-3" :variant="store.float_label_variants">
                <InputText class="w-full"
                           name="stores-name"
                           data-testid="stores-name"
                           @update:modelValue="store.watchItem"
                           v-model="store.item.name" required/>
                <label for="articles-name">Enter the name<span class="text-red-500">*</span></label>
            </FloatLabel>

            <FloatLabel class="my-3" :variant="store.float_label_variants">
                <InputText class="w-full"
                           name="stores-slug"
                           data-testid="stores-slug"
                           v-model="store.item.slug" required/>
                <label for="articles-name">Enter the Slug<span class="text-red-500">*</span></label>
            </FloatLabel>

            <div class="flex items-center mb-2">
                <FloatLabel class="my-1 w-full !rounded-r-none" :variant="store.float_label_variants"
                >

                    <AutoComplete name="store-currencies"
                                  data-testid="store-currencies"
                                  v-model="store.item.default_currency"
                                  option-label="name"
                                  :complete-on-focus="true"
                                  :suggestions="store.currency_suggestion_list"
                                  dropdown
                                  @complete="store.searchCurrencies($event)"
                                  class="w-full"
                    />

                    <label for="articles-name">Select Default Currency<span class="text-red-500">*</span></label>
                </FloatLabel>

            </div>

            <div class="flex items-center mb-4">
                <FloatLabel class="my-1 w-full !rounded-r-none" :variant="store.float_label_variants" >

                    <AutoComplete name="store-languages"
                                  data-testid="store-languages"
                                  v-model="store.item.default_language"
                                  option-label="name"
                                  dropdown
                                  :complete-on-focus="true"
                                  :suggestions="store.language_suggestion_list"
                                  @complete="store.searchLanguages($event)"
                                  class="w-full"
                    />

                    <label for="articles-name">Select Default Language<span class="text-red-500">*</span></label>
                </FloatLabel>

            </div>



            <VhField label="Is Multi Currency">

                <div class="">
                    <div class="col-3">
                        <ToggleSwitch v-bind:false-value="0"
                                      v-bind:true-value="1"
                                      class="p-inputswitch"
                                      name="stores-multi-currency"
                                      data-testid="stores-multi-currency"
                                      @change="store.item.currencies = null;"
                                      v-model="store.item.is_multi_currency"/>
                    </div>


                </div>
            </VhField>

            <FloatLabel class="my-3" :variant="store.float_label_variants" v-show="store.item.is_multi_currency == 1">
                <AutoComplete name="store-currencies"
                              data-testid="store-currencies"
                              v-model="store.item.currencies"
                              option-label="name"
                              multiple
                              :complete-on-focus="true"
                              :suggestions="store.currency_suggestion_list"

                              @complete="store.searchCurrencies($event)"
                              class="w-full"
                />
                <label for="articles-name">Select Currencies<span class="text-red-500">*</span></label>
            </FloatLabel>






            <VhField label="Is Multi Lingual">

                    <div class="col-3">
                        <ToggleSwitch v-bind:false-value="0"
                                      v-bind:true-value="1"
                                      class="p-inputswitch"
                                      name="stores-multi-currency"
                                      data-testid="stores-multi-currency"
                                      @change="store.item.languages = null;"
                                      v-model="store.item.is_multi_lingual"/>
                    </div>



            </VhField>



            <FloatLabel class="my-3" :variant="store.float_label_variants" v-show="store.item.is_multi_lingual == 1">
                <AutoComplete name="store-languages"
                              data-testid="store-languages"
                              v-model="store.item.languages"
                              option-label="name"
                              multiple
                              :complete-on-focus="true"
                              :suggestions="store.language_suggestion_list"
                              @change="store.addLanguages()"
                              @complete="store.searchLanguages($event)"
                              class="w-full"
                />

                <label for="currencies-name">Select Languages<span class="text-red-500">*</span></label>
            </FloatLabel>




            <VhField label="Is Multi Vendor">
                <div class="flex flex-row">
                    <div class="col-4">
                        <ToggleSwitch v-bind:false-value="0"
                                      v-bind:true-value="1"
                                      class="p-inputswitch"
                                      name="stores-multi-vendor"
                                      data-testid="stores-multi-vendor"
                                      v-model="store.item.is_multi_vendor"/>
                    </div>

                </div>
            </VhField>
            <FloatLabel class="my-3" :variant="store.float_label_variants">
                <Chips class="w-full"
                       v-model="store.item.allowed_ips"
                       data-testid="store-allowed-ips"
                       type="number"
                       separator=","/>

                <label for="allowed-ips">Allowed IPs<span class="text-red-500">*</span>-e.g. 192.168.1.1</label>
            </FloatLabel>
            <FloatLabel class="my-3" :variant="store.float_label_variants">
                <AutoComplete v-model="store.item.status"
                              @change="store.setStatus($event)"
                              value="id"
                              class="w-full"
                              data-testid="store-taxonomy_status"
                              :suggestions="store.status_suggestion_list"
                              @complete="store.searchStatus($event)"
                              :dropdown="true"
                              optionLabel="name"
                              forceSelection/>
                <label for="stauts">Select Status<span class="text-red-500">*</span></label>
            </FloatLabel>
            <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea
                        v-model="store.item.status_notes"
                        data-testid="store-taxonomy_status_notes"
                        :autoResize="true" rows="3" class="w-full"/>
                <label for="stauts-notes">Enter Status Note</label>
            </FloatLabel>

            <div class="flex items-center gap-2 my-3">
                <ToggleSwitch v-bind:false-value="0"
                              v-bind:true-value="1"
                              class="p-inputswitch"
                              name="stores-is_default"
                              data-testid="store-is_default"
                              v-model="store.item.is_default"/>
                <label for="stores-default">Is Default</label>
            </div>


            <div class="flex items-center gap-2 my-3">
                <ToggleSwitch
                    v-bind:false-value="0"
                    v-bind:true-value="1"
                    class="p-inputswitch"
                    name="stores-active"
                    data-testid="stores-active"
                    v-model="store.item.is_active"
                />
                <label for="stores-active">Is Active</label>
            </div>

        </div>
    </Panel>


</template>

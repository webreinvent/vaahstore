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
                            <SelectButton v-model="store.item.is_multi_currency" :options="store.on_off_options" aria-labelledby="single" />
                        </div>

                        <div v-if="store.item.is_multi_currency == 'yes'" class="pl-5 col-8">
                            <MultiSelect placeholder="Select Currencys" />
                        </div>
                    </div>
                </VhField>

                <VhField label="Is Multi Lingual">
                    <div class="flex flex-row">
                        <div class="col-4">
                            <SelectButton v-model="store.item.is_multi_lingual" :options="store.on_off_options" aria-labelledby="single" />
                        </div>

                        <div v-if="store.item.is_multi_lingual == 'yes'" class="pl-5 col-8">
                            <MultiSelect placeholder="Select Languages" />
                        </div>
                    </div>
                </VhField>

                <VhField label="Is Multi Vendor">
                    <div class="flex flex-row">
                        <div class="col-4">
                            <SelectButton v-model="store.item.is_multi_vendor" :options="store.on_off_options" aria-labelledby="single" />
                        </div>

                        <div v-if="store.item.is_multi_vendor == 'yes'" class="pl-5 col-8">
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
</style>

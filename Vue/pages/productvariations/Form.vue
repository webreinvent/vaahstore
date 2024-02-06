<script setup>
import {onMounted, ref, watch} from "vue";
import { useProductVariationStore } from '../../stores/store-productvariations'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductVariationStore();
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

const permissions=store.assets.permissions;
</script>
<template>

    <div class="col-6" >

        <Panel class="is-small">

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title"
                         :disabled="!store.assets.permissions.includes('can-update-module')">
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
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="productvariations-save"
                            @click="store.itemAction('save')"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="productvariations-create-and-new"

                            icon="pi pi-save"/>

                    <Button data-testid="productvariations-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="productvariations-form-menu"
                        icon="pi pi-angle-down"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="productvariations-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="pt-2">

                <VhField label="Product*">

                    <AutoComplete
                        value="id"
                        v-model="store.item.product"
                        @change="store.setProduct($event)"
                        class="w-full"
                        :suggestions="store.filtered_products"
                        @complete="store.searchProduct($event)"
                        placeholder="Select Product"
                        data-testid="productvariations-product"
                        name="productvariations-product"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>


                <VhField label="Name*">
                    <InputText class="w-full"
                               name="productvariations-name"
                               data-testid="productvariations-name"
                               placeholder="Enter Name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug*">
                    <InputText class="w-full"
                               name="productvariations-slug"
                               data-testid="productvariations-slug"
                               placeholder="Enter Slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="SKU*">
                    <InputText class="w-full"
                               name="productvariations-sku"
                               data-testid="productvariations-sku"
                               placeholder="Enter SKU"
                               v-model="store.item.sku"/>
                </VhField>

                <VhField label="Price">
                    <InputNumber
                        class="w-full"
                        v-model="store.item.price"
                        placeholder="Enter Price"
                        @input = "store.checkPrice($event)"
                        :min = 1
                        name="productvariations-price"
                        data-testid="productvariations-price"/>
                </VhField>

                <VhField label="Status*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        name="productvariations-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="productvariations-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Meta Title">
                    <InputText class="w-full"
                               name="productvariations-meta_title"
                               data-testid="productvariations-meta_title"
                               placeholder="Enter Meta Title"
                               v-model="store.item.meta_title"/>
                </VhField>

                <VhField label="Meta Description">
                    <Textarea rows="3" class="w-full"
                              name="productvariations-meta_description"
                              data-testid="productvariations-meta_description"
                              :autoResize="true"
                              placeholder="Enter Meta Description"
                              v-model="store.item.meta_description"/>
                </VhField>

                <VhField label="Meta Keyword" >
                    <Chips class="w-full"
                           v-model="store.item.meta_keywords"
                           placeholder="Enter Meta keyword"
                           separator=","  />
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="productvariations-status_notes"
                              data-testid="productvariations-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Description">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Description Here"
                              name="productvariations-description"
                              data-testid="productvariations-description"
                              v-model="store.item.description"/>
                </VhField>

                <VhField label="Is Default">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="vendors-default"
                                 data-testid="vendors-default"
                                 v-model="store.item.is_default"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="productvariations-active"
                                 data-testid="productvariations-active"
                                 v-model="store.item.is_active"
                                 :pt="{
        slider: ({ props }) => ({
            class: props.modelValue ? 'bg-green-400' : ''
        })
    }"
                    />
                </VhField>
            </div>
        </Panel>

    </div>

</template>


<style scoped>

.quantity-class{
    height:35px;
}

</style>

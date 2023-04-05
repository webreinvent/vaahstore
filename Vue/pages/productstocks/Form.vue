<script setup>
import {onMounted, ref, watch} from "vue";
import { useProductStockStore } from '../../stores/store-productstocks'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductStockStore();
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
                            data-testid="productstocks-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="productstocks-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="productstocks-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="productstocks-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="productstocks-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="productstocks-name"
                               data-testid="productstocks-name"
                               placeholder="Enter Name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="productstocks-slug"
                               data-testid="productstocks-slug"
                               placeholder="Enetr Slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Vendor">
                    <AutoComplete v-model="store.item.vh_st_vendor_id"
                                  class="w-full"
                                  data-testid="productstocks-vh_st_vendor_id"
                                  :suggestions="store.vendors_suggestion_list"
                                  @complete="store.searchVendor($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Vendor"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}<span v-if="slotProps.option.is_default == 1"> (Default) </span></div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Product">
                    <AutoComplete v-model="store.item.vh_st_product_id"
                                  class="w-full"
                                  data-testid="productstocks-vh_st_product_id"
                                  :suggestions="store.products_suggestion_list"
                                  @complete="store.searchProduct($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Product"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}<span v-if="slotProps.option.is_default == 1"> (Default) </span></div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Product Variation">
                    <AutoComplete v-model="store.item.vh_st_product_variation_id"
                                  class="w-full"
                                  data-testid="productstocks-vh_st_product_variation_id"
                                  :suggestions="store.product_variations_suggestion_list"
                                  @complete="store.searchProductVariation($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Product Variation"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}<span v-if="slotProps.option.is_default == 1"> (Default) </span></div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Warehouse">
                    <AutoComplete v-model="store.item.vh_st_warehouse_id"
                                  class="w-full"
                                  data-testid="productstocks-vh_st_warehouse_id"
                                  :suggestions="store.warehouses_suggestion_list"
                                  @complete="store.searchWarehouse($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Warehouse"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}<span v-if="slotProps.option.is_default == 1"> (Default) </span></div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Quantity">
                    <InputNumber
                        name="productstocks-quantity"
                        v-model="store.item.quantity"
                        mode="decimal" showButtons
                        placeholder="Enter Quantity"
                        data-testid="productstocks-quantity"
                        :min="1"/>
                </VhField>

                <VhField label="Status">
                    <AutoComplete v-model="store.item.taxonomy_id_product_stock_status"
                                  class="w-full"
                                  data-testid="productstocks-taxonomy_id_product_stock_status"
                                  :suggestions="store.status_suggestion_list"
                                  @complete="store.searchStatus($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Status"
                                  forceSelection />
                </VhField>

                <VhField label="Status Notes">
                    <Textarea placeholder="Enter Status Note" v-model="store.item.status_notes" data-testid="productstocks-taxonomy_status_notes" :autoResize="true" rows="5" cols="30" />
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="productstocks-active"
                                 data-testid="productstocks-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

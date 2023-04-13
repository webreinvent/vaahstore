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
                            data-testid="productvendors-save"
                            @click="store.itemAction('save-productprice')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="productvendors-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="productvendors-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="productvendors-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="productvendors-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Product Variation">

                    <AutoComplete
                        v-model="store.item.product_variation"
                        class="w-full"
                        :suggestions="store.product_variation_suggestion"
                        @complete="store.searchProductVariation($event)"
                        placeholder="Select Product Variation"
                        data-testid="productprices-product_variation"
                        name="productprices-product_variation"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Amount">

                    <InputNumber
                        placeholder="Enter a Amount"
                        inputId="minmax-buttons"
                        name="productprices-amount"
                        v-model="store.item.amount"
                        mode="decimal" showButtons
                        data-testid="productprices-amount"/>

                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="productprices-active"
                                 data-testid="productprices-active"
                                 v-model="store.item.is_active_product_price"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

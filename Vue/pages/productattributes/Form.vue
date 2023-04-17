<script setup>
import {onMounted, ref, watch} from "vue";
import { useProductAttributeStore } from '../../stores/store-productattributes'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductAttributeStore();
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
                            data-testid="productattributes-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="productattributes-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="productattributes-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="productattributes-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="productattributes-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Product variation">
                    <AutoComplete v-model="store.item.vh_st_product_variation_id"
                                  value="id"
                                  class="w-full"
                                  data-testid="productattributes-vh_st_product_variation_id"
                                  :suggestions="store.product_variation_suggestion_list"
                                  @complete="store.searchProductVariation($event)"
                                  :dropdown="true"
                                  optionLabel="name"
                                  placeholder="Select Product variation"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}<span v-if="slotProps.option.is_default == 1"> (Default) </span></div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Attributes">
                    <AutoComplete v-model="store.item.vh_st_attribute_id"
                                  value="id"
                                  class="w-full"
                                  placeholder="Select Attributes"
                                  data-testid="productattributes-vh_st_attribute_id"
                                  :suggestions="store.attribute_suggestion_list"
                                  @complete="store.searchAttribute($event)"
                                  @change="store.getAttributeValue()"
                                  :dropdown="true"
                                  optionLabel="name"
                                  forceSelection >
                        <template #option="slotProps">
                            <div class="flex align-options-center">
                                <div>{{ slotProps.option.name }}<b>(slotProps.option.type)</b></div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <vhField label="Attribute Values" v-if="store.item.attribute_values">
                    <div v-for="item in store.item.attribute_values">
                        <div class="p-inputgroup flex-1 p-1">
                            <span class="p-inputgroup-addon" v-tooltip="item.default_value" >{{ item.default_value.substring(0, 4) + '...' }}</span>
                            <InputText v-model="item.new_value" :placeholder="item.default_value" />
                        </div>
                    </div>
                </vhField>

<!--                <VhField label="Is Active">-->
<!--                    <InputSwitch v-bind:false-value="0"-->
<!--                                 v-bind:true-value="1"-->
<!--                                 name="productattributes-active"-->
<!--                                 data-testid="productattributes-active"-->
<!--                                 v-model="store.item.is_active"/>-->
<!--                </VhField>-->

            </div>
        </Panel>

    </div>

</template>

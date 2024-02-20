<script setup>
import {onMounted, ref, watch} from "vue";
import { useProductStore } from '../../stores/store-products'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductStore();
const route = useRoute();

onMounted(async () => {
    if(route.params && route.params.id)
    {
        // store.item = store.
        await store.getItem(route.params.id);

        store.getAttributeList();
    }

    await store.watchItem();
    await store.watchAttributes();
    await store.watchVariations();

});

//--------selected_menu_state
const selected_menu_state = ref();
const toggleSelectedMenuState = (event) => {
    selected_menu_state.value.toggle(event);
};

const selected_attribute_menu_state = ref();
const toggleSelectedAttributeMenuState = (event) => {
    selected_attribute_menu_state.value.toggle(event);
};
//--------/selected_menu_state


</script>
<template>

    <div class="col-8" >

        <Panel >

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <b>Add Product Variations</b>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">
                    <Button label="Save"
                            v-if="store.item && store.item.id"
                            class="p-button-sm"
                            data-testid="products-save"
                            @click="store.saveVariation()"
                            icon="pi pi-save"/>

                    <Button data-testid="products-document" icon="pi pi-info-circle"
                            class="p-button-sm"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <Button class="p-button-sm"
                            icon="pi pi-times"
                            data-testid="products-to-list"
                            @click="store.toList()">
                    </Button>
                </div>

            </template>

            <div v-if="store.item">

<!--                user error message-->
                <div v-if="store.user_error_message && store.user_error_message.length > 0">
                    <Message severity="error" v-for="(item) in store.user_error_message">{{item}}</Message>
                </div>

<!--                Radio button for attribute and attribute group-->
                <div class="flex flex-wrap gap-3 pb-2 p-2">
                    <div class="flex align-items-center p-1">
                        <RadioButton v-model="store.variation_item.attribute_option_type" inputId="attribute" :value="0" />
                        <label for="attribute" class="ml-2">Attribute</label>
                    </div>
                    <div class="flex align-items-center p-1">
                        <RadioButton v-model="store.variation_item.attribute_option_type" inputId="attribute-group" :value="1" />
                        <label for="attribute-group" class="ml-2">Attribute Group</label>
                    </div>
                </div>

<!--                Dropdown for attribute selection-->
                <div class="flex flex-wrap gap-3 pb-2 p-1">
                    <div class="col-10">
                        <Dropdown v-model="store.variation_item.selected_attribute"
                                  :options="store.variation_item.attribute_options"
                                  optionLabel="name"
                                  filter
                                  placeholder="Select a Attribute or Attribute group"
                                  class="w-full"
                                    style="height:35px;">
                        </Dropdown>
                    </div>

                    <div class="p-2">
                        <Button v-if="store.variation_item.selected_attribute"
                                type="button" label="Add"
                                @click="store.addNewProductAttribute()"
                                style="height:35px;width:50px;"
                        />
                    </div>
                </div>

                <div class="p-1 pl-2 flex flex-wrap col-12"
                     v-if="store.variation_item.product_attributes && store.variation_item.product_attributes.length > 0">
                    <div class="col-10">
                        <!--selected_menu-->
                        <Button
                            type="button"
                            @click="toggleSelectedAttributeMenuState"
                            data-testid="products-actions-menu"
                            aria-haspopup="true"
                            aria-controls="overlay_menu">
                            <i class="pi pi-angle-down"></i>
                            <Badge v-if="store.action.items.length > 0"
                                   :value="store.action.items.length" />
                        </Button>
                        <Menu ref="selected_attribute_menu_state"
                              :model="store.attribute_selected_menu"
                              :popup="true" />
                        <!--/selected_menu-->
                    </div>

                    <!--                Product Attributes-->
                    <div class="col-12 ">
                        <div class="container col-12">
                            <table class="table col-12 table-scroll table-striped" v-if="store.variation_item.product_attributes && store.variation_item.product_attributes.length > 0">
                                <thead>
                                <tr>
                                    <th class="col-1">
                                        <Checkbox v-model="store.select_all_attribute"
                                                  :binary="true" @click="store.selectAllAttribute()" />
                                    </th>
                                    <th>Attribute name</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody id="scroll-horizontal-variation" class="pt-1">
                                <tr v-for="attribute in store.variation_item.product_attributes">
                                    <th class="col-1"><Checkbox v-model="attribute['is_selected']" :binary="true" /></th>
                                    <td>
                                        <InputText v-model="attribute.name" class="w-full" disabled/>
                                    </td>
                                    <td class="flex justify-content-center">
                                        <Button label="Remove"
                                                size="small"
                                                @click="store.removeProductAttribute(attribute)" />
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

<!--                Product Variations-->
                <div class="flex flex-wrap col-12"
                     v-if="store.variation_item.product_attributes && store.variation_item.product_attributes.length > 0">
                    <div class="flex col-3">
                        <div class="pr-1">
                            <Button label="Create" @click="store.generateProductVariation()" severity="primary" size="small" />
                        </div>

                    </div>
                </div>


<!--                Bulk action -->
                <div class="p-1 pl-2 flex flex-wrap col-12"
                     v-if="store.item.all_variation && store.item.all_variation.structured_variation && store.item.all_variation.structured_variation.length > 0 && Object.keys(store.item.all_variation).length > 0">
                    <div class="col-10">
                        <!--selected_menu-->
                        <Button
                            type="button"
                            @click="toggleSelectedMenuState"
                            data-testid="products-actions-menu"
                            aria-haspopup="true"
                            aria-controls="overlay_menu">
                            <i class="pi pi-angle-down"></i>
                            <Badge v-if="store.action.items.length > 0"
                                   :value="store.action.items.length" />
                        </Button>
                        <Menu ref="selected_menu_state"
                              :model="store.variation_selected_menu"
                              :popup="true" />
                        <!--/selected_menu-->
                    </div>

                </div>

<!--                variation table-->
                <div class="container col-12"
                     v-if="store.item.all_variation && store.item.all_variation.structured_variation && store.item.all_variation.structured_variation.length > 0 &&  Object.keys(store.item.all_variation).length > 0">
                    <table class="table col-12 table-scroll table-striped">
                        <thead>
                        <tr>
                            <th class="col">
                                <Checkbox v-model="store.variation_item.select_all_variation"
                                          :binary="true" @click="store.selectAllVariation()" />
                            </th>
                            <th scope="col">Variation name</th>
                            <th scope="col"
                                v-for="(item, index) in store.item.all_variation.all_attribute_name">
                                {{ item }}
                            </th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody id="scroll-horizontal" class="pt-1">
                            <tr v-for="(item, index) in store.item.all_variation.structured_variation">
                                <th class="col-1"><Checkbox v-model="item['is_selected']" :binary="true" /></th>
                                <td>
                                    <InputText v-model="item['variation_name']" class="w-full " />
                                </td>
                                <td v-for="(i) in store.item.all_variation.all_attribute_name">
                                    <div class="text-center">
                                        <InputText v-model="item[i]['value']" class="w-full md:w-5rem" disabled="true"/>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                    <Button label="Remove"
                                            size="small"
                                            @click="store.removeProductVariation(item)" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>

            </div>
        </Panel>

    </div>

</template>


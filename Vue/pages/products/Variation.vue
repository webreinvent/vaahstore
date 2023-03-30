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
        await store.getItem(route.params.id);

        store.getAttributeList();
    }

    await store.watchVariationItem();
});

//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};
//--------/form_menu

</script>
<template>

    <div class="col-8" >

        <Panel >

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <b>Add Product Attributes</b>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">
                    <Button label="Save"
                            v-if="store.item && store.item.id"
                            data-testid="products-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="products-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="products-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="products-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

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
                                  placeholder="Select a Attribute or Attribute group"
                                  class="w-full">
                        </Dropdown>
                    </div>

                    <div class="p-1">
                        <Button type="button" label="Add" :loading="loading" @click="store.addNewProductAttribute()" />
                    </div>
                </div>

<!--                Product Attributes-->
                <div class="col-12">
                    <div>
                        <span class="p-1">
                            <b>Product Attributes</b>
                        </span>
                    </div>

                    <div class="container col-12">
                        <div v-if="store.variation_item.product_attributes && store.variation_item.product_attributes.length > 0" v-for="attribute in store.variation_item.product_attributes" class="pb-1 flex flex-wrap">
                            <InputText :placeholder="attribute.name" v-model="attribute.name" disabled="true" class="col-10" />
                            <div class="pl-1">
                                <Button label="Remove" severity="danger" style="background-color: red;" @click="store.removeProductAttribute(attribute)" />
                            </div>
                        </div>
                        <div v-else>
                            <small>no <b>Attribute</b> or <b>Attribute Group</b> is added</small>
                        </div>
                    </div>
                </div>

<!--                Product Variations-->
                <div class="flex flex-wrap col-12">
                    <div class="col-9">
                        <span>
                            <b>Product Variations</b>
                        </span>
                    </div>
                    <div class="flex col-3">
                        <div class="pr-1">
                            <Button label="Create" severity="danger" size="small" />
                        </div>
                        <div class="">
                            <Button label="Generate" severity="danger" size="small" />
                        </div>
                    </div>
                </div>

<!--                Bulk action -->
                <div class="p-1 pl-2">
                    <div class="">
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
                              :model="store.list_selected_menu"
                              :popup="true" />
                        <!--/selected_menu-->
                    </div>
                </div>

                <div class="col-12">
                    <table class="table col-12 table-scroll table-striped">
                        <thead>
                        <tr>
                            <th class="col-1"><Checkbox v-model="checked" :binary="true" /></th>
                            <th scope="col">Variation name</th>
                            <th scope="col">Color</th>
                            <th scope="col">Size</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Media</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody id="scroll-horizontal" class="pt-1">
                            <tr>
                                <th class="col-1"><Checkbox v-model="checked" :binary="true" /></th>
                                <td>Variation name</td>
                                <td>Red</td>
                                <td>SM</td>
                                <td>2</td>
                                <td>1</td>
                                <td><Button label="Remove" severity="danger" size="small" /></td>
                            </tr>
                            <tr>
                                <th class="col-1"><Checkbox v-model="checked" :binary="true" /></th>
                                <td>Variation name</td>
                                <td>Red</td>
                                <td>LG</td>
                                <td>2</td>
                                <td>1</td>
                                <td><Button label="Remove" severity="danger" size="small" /></td>
                            </tr>
                            <tr>
                                <th class="col-1"><Checkbox v-model="checked" :binary="true" /></th>
                                <td>Variation name</td>
                                <td>Black</td>
                                <td>SM</td>
                                <td>2</td>
                                <td>1</td>
                                <td><Button label="remove" severity="danger" size="small" /></td>
                            </tr>
                            <tr>
                                <th class="col-1"><Checkbox v-model="checked" :binary="true" /></th>
                                <td>Variation name</td>
                                <td>Red</td>
                                <td>SM</td>
                                <td>2</td>
                                <td>1</td>
                                <td><Button label="remove" severity="danger" size="small" /></td>
                            </tr>
                            <tr>
                                <th class="col-1"><Checkbox v-model="checked" :binary="true" /></th>
                                <td>Variation name</td>
                                <td>Red</td>
                                <td>LG</td>
                                <td>2</td>
                                <td>1</td>
                                <td><Button label="remove" severity="danger" size="small" /></td>
                            </tr>
                            <tr>
                                <th class="col-1"><Checkbox v-model="checked" :binary="true" /></th>
                                <td>Variation name</td>
                                <td>Black</td>
                                <td>SM</td>
                                <td>2</td>
                                <td>1</td>
                                <td><Button label="remove" severity="danger" size="small" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </Panel>

    </div>

</template>

<style scoped>
.table-scroll tbody {
    position: sticky;
    overflow-y: scroll;
    height: 250px;
}

.table-scroll tr {
    width: 100%;
    table-layout: fixed;
    display: inline-table;
}

.table-scroll thead > tr > th {
    border: none;
}
</style>

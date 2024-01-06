<script setup>
import {onMounted, ref, watch} from "vue";
import {useProductStore} from '../../stores/store-products'
import Editor from 'primevue/editor';
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductStore();
const route = useRoute();

onMounted(async () => {
    if (route.params && route.params.id) {
        await store.getItem(route.params.id);
    }

    await store.getFormMenu();
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

    <div class="col-6">

        <Panel class="is-small">

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

                    <Button class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="products-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="products-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="products-create-and-new"
                            icon="pi pi-save"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="products-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true"/>
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="products-to-list"
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


                <VhField label="Name*">
                    <InputText class="w-full"
                               name="products-name"
                               data-testid="products-name"
                               @update:modelValue="store.watchItem"
                               placeholder="Enter Name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug*">
                    <InputText class="w-full"
                               name="products-slug"
                               data-testid="products-slug"
                               placeholder="Enter Slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Summary">

                    <Editor editor-style="height:50px"
                            name="products-summary"
                            data-testid="products-summary"
                            placeholder="Enter Product Summary"
                            v-model="store.item.summary">

                        <template v-slot:toolbar>
                            <span class="ql-formats">
                                <button v-tooltip.bottom="'Bold'" class="ql-bold"></button>
                                <button v-tooltip.bottom="'Italic'" class="ql-italic"></button>
                                <button v-tooltip.bottom="'Underline'" class="ql-underline"></button>
                            </span>
                        </template>
                    </Editor>
                </VhField>

                <VhField label="Store*">

                    <AutoComplete
                        value="id"
                        v-model="store.item.store"
                        @change="store.setStore($event)"
                        class="w-full"
                        :suggestions="store.filtered_stores"
                        @complete="store.searchStore"
                        placeholder="Select Store"
                        data-testid="products-store"
                        name="products-store"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Brand*">

                    <AutoComplete
                        value="id"
                        v-model="store.item.brand"
                        @change="store.setBrand($event)"
                        class="w-full"
                        :suggestions="store.filtered_brands"
                        @complete="store.searchBrand"
                        placeholder="Select Brand"
                        data-testid="products-brand"
                        name="products-brand"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Type*">

                    <AutoComplete
                        value="id"
                        v-model="store.item.type"
                        @change="store.setType($event)"
                        class="w-full"
                        data-testid="products-type"
                        name="products-type"
                        :suggestions="store.type_suggestion"
                        @complete="store.searchTaxonomyProduct($event)"
                        placeholder="Select Type"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Quantity">
                    <InputNumber
                        placeholder="Enter a Quantity"
                        class="w-full"
                        inputId="minmax-buttons"
                        name="products-quantity"
                        v-model="store.item.quantity"
                        @input="store.checkQuantity($event)"
                        showButtons
                        :min="0"
                        data-testid="products-quantity"
                        style="height:40px;"/>
                </VhField>


                <VhField label="In Stock">
                    <InputSwitch
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        @change="store.checkInStock()"
                        name="products-in_stock"
                        data-testid="products-in_stock"
                        v-model="store.item.in_stock"/>
                </VhField>

                <VhField label="Availablity Date">
                    <Calendar tabindex="0"
                              :showIcon="true"
                              class="w-full"
                              name="brands-registered_at"
                              id="registered_at"
                              value="registered_at"
                              data-testid="brands-registered_at"
                              dateFormat="yy-mm-dd"
                              :showTime="true" :showSeconds="true"
                              placeholder="Select date"
                              v-model="store.item.available_at"></Calendar>
                </VhField>

                <VhField label="Launch Date">
                    <Calendar tabindex="0"
                              :showIcon="true"
                              class="w-full"
                              name="brands-registered_at"
                              id="registered_at"
                              value="registered_at"
                              data-testid="brands-registered_at"
                              dateFormat="yy-mm-dd"
                              :showTime="true" :showSeconds="true"
                              placeholder="Select date"
                              v-model="store.item.launch_at"></Calendar>
                </VhField>


                <VhField label="Featured on Home page">
                    <InputSwitch
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        name="products-is-home-featured"
                        data-testid="products-is-home-featured"
                        v-model="store.item.is_featured_on_home_page"/>
                </VhField>

                <VhField label="Featured on Category page">
                    <InputSwitch
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        name="products-is-category-featured"
                        data-testid="products-is-category-featured"
                        v-model="store.item.is_featured_on_category_page"/>
                </VhField>

                <VhField label="Product Details">
                    <Editor v-model="store.item.details"
                            class="w-full"
                            name="products-details"
                            data-testid="products-details"
                            placeholder="Enter Product Details"
                            editorStyle="height:80px"
                             >
                        <template v-slot:toolbar>
                            <span class="ql-formats">
                                <select class="ql-header">
                                    <option value="1">Heading</option>
                                    <option value="2">Heading 2</option>
                                    <option value="3">Heading 3</option>
                                    <option value="4">Heading 4</option>
                                    <option value="5">Heading 5</option>
                                    <option value="6">Heading 6</option>
                                </select>
                                <select class="ql-font" v-model="store.item.font">
                                    <option value="sans-serif">Sans Serif</option>
                                    <option value="Arial, Helvetica, sans-serif">Arial</option>
                                    <option value="Verdana, Geneva, sans-serif">Verdana</option>
                                    <option value="Tahoma, Geneva, sans-serif">Tahoma</option>
                                </select>
                            </span>
                            <span class="ql-formats">
                                <button v-tooltip.bottom="'Bold'" class="ql-bold"></button>
                                <button v-tooltip.bottom="'Italic'" class="ql-italic"></button>
                                <button v-tooltip.bottom="'Underline'" class="ql-underline"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered"></button>
                                <button class="ql-list" value="bullet"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-link" type="button"></button>
                                <button class="ql-code-block" type="button"></button>
                            </span>

                        </template>
                    </Editor>
                </VhField>
                <VhField label="Product Details">
                    <Editor v-model="store.item.details"
                            class="w-full"
                            name="products-details"
                            data-testid="products-details"
                            placeholder="Enter Product Details"
                            editorStyle="height:80px"
                    >
                    </Editor>
                </VhField>

                <VhField label="Status*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.status"
                        @change="store.setProductStatus($event)"
                        class="w-full"
                        name="products-status"
                        :suggestions="store.filtered_status"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="products-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="products-status_notes"
                              data-testid="products-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="products-active"
                                 data-testid="products-active"
                                 v-model="store.item.is_active"/>
                </VhField>


            </div>
        </Panel>

    </div>

</template>

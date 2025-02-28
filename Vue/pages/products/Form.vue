<script setup>
import {onMounted, ref, watch} from "vue";
import {useProductStore} from '../../stores/store-products'
import Editor from 'primevue/editor';
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';
import { useRootStore } from '@/stores/root.js'

const store = useProductStore();
const root = useRootStore();
const route = useRoute();

onMounted(async () => {
    if (route.params && route.params.id) {
        await store.getItem(route.params.id);
    }
    await store.getCategories();
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

                    <Button class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="products-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="products-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="products-create-and-new"
                            icon="pi pi-save"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
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


                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="products-name"
                               data-testid="products-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                    <label for="products-name">Name <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="products-slug"
                               data-testid="products-slug"
                               v-model="store.item.slug"/>
                    <label for="products-slug">Slug <span class="text-red-500">*</span></label>

                </FloatLabel>


                <FloatLabel class="my-3" :variant="store.float_label_variants">

                    <AutoComplete
                        value="id"
                        v-model="store.item.store"
                        @change="store.setStore($event)"
                        class="w-full"
                        :suggestions="store.filtered_stores"
                        @complete="store.searchStore"
                        data-testid="products-store"
                        name="products-store"
                        :dropdown="true"
                        optionLabel="name"
                        forceSelection
                        :pt="{
                                              token: {
                        class: 'max-w-full'
                    },
                    removeTokenIcon: {
                    class: 'min-w-max'
                    },
                    item: { style: {
                    textWrap: 'wrap'
                    }  },
                    panel: { class: 'w-16rem ' }
                                                }">
                    </AutoComplete>

                    <label for="products-store">Store <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">

                    <AutoComplete
                        value="id"
                        v-model="store.item.brand"
                        @change="store.setBrand($event)"
                        class="w-full"
                        :suggestions="store.filtered_brands"
                        @complete="store.searchBrand"
                        data-testid="products-brand"
                        name="products-brand"
                        :dropdown="true"
                        optionLabel="name"
                        forceSelection
                        :pt="{
                                              token: {
                        class: 'max-w-full'
                    },
                    removeTokenIcon: {
                    class: 'min-w-max'
                    },
                    item: { style: {
                    textWrap: 'wrap'
                    }  },
                    panel: { class: 'w-16rem ' }
                                                }"
                    >
                    </AutoComplete>

                    <label for="products-brand">Select Brand</label>

                </FloatLabel>
                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <TreeSelect
                        v-model="store.item.categories"
                        :options="store.categories_dropdown_data"
                        selectionMode="multiple"
                        display="chip"
                        :show-count="true"
                        data-testid="product-categories"
                        @change="store.setParentId()"
                        class=" w-full" />

                    <label for="products-categories">Select Category</label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">

                    <AutoComplete
                        value="id"
                        v-model="store.item.type"
                        @change="store.setType($event)"
                        class="w-full"
                        data-testid="products-type"
                        name="products-type"
                        :suggestions="store.type_suggestion"
                        @complete="store.searchTaxonomyProduct($event)"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                    <label for="products-type">Select Type <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <DatePicker tabindex="0"
                              :showIcon="true"
                              name="brands-registered_at"
                              id="registered_at"
                              value="registered_at"
                              data-testid="brands-registered_at"
                              dateFormat="yy-mm-dd"
                              v-model="store.item.launch_at"
                    />
                    <label for="brands-registered_at">Select Date </label>

                </FloatLabel>

                <VhField label="Availablity Date">
                    <DatePicker tabindex="0"
                              :showIcon="true"
                              name="brands-registered_at"
                              id="registered_at"
                              value="registered_at"
                              data-testid="brands-registered_at"
                              dateFormat="yy-mm-dd"
                              placeholder="Select date"
                              v-model="store.item.available_at"
                              @date-select="store.checkDate()"
                    ></DatePicker>
                </VhField>


                <div class="flex items-center gap-2 my-3" >
                    <ToggleSwitch
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        name="products-is-home-featured"
                        data-testid="products-is-home-featured"
                        v-model="store.item.is_featured_on_home_page"/>
                    <label for="products-is-home-featured">Featured on Home page</label>
                </div>

                <div class="flex items-center gap-2 my-3" >
                    <ToggleSwitch
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        name="products-is-category-featured"
                        data-testid="products-is-category-featured"
                        v-model="store.item.is_featured_on_category_page"/>
                    <label for="products-is-category-featured">Featured on Category page</label>
                </div>



                <VhField label="Product Details">

                    <Editor
                        v-model="store.item.details"
                        class="w-full"
                        name="products-details"
                        data-testid="products-details"
                        placeholder="Enter Product Details"
                        editorStyle="height: 10vh"
                        :pt="{
                            toolbar: {
                                class: 'hidden'
                            }
                        }"
                        formats=""
                        :modules="{
                        toolbar: [
                            ['bold', 'italic', 'underline','strike'],
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                             [{ 'font': [] }],
                            [{ 'align': '' }, {'align': 'center'}, {'align': 'right'}],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                           ]
                        }"

                    >
                    </Editor>
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

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="products-seo-title"
                               data-testid="products-seo-title"
                               v-model="store.item.seo_title"/>
                    <label for="products-seo_title">Enter Seo Title</label>
                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea class="w-full"
                              name="products-seo-description"
                              data-testid="products-seo-description"
                              rows="3" cols="30"
                              v-model="store.item.seo_meta_description"
                    />
                    <label for="products-seo-description">Enter Seo Description</label>
                </FloatLabel>


                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Chips class="w-full"
                           style="display:flex;flex-wrap:wrap;width:100%"
                           name="products-seo-meta-keywords"
                           data-testid="products-seo-meta-keywords"
                           v-model="store.item.seo_meta_keyword"
                           separator=","
                            />

                    <label for="products-seo-meta-keywords">Enter Seo Meta keywords</label>
                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <AutoComplete
                        value="id"
                        v-model="store.item.status"
                        @change="store.setProductStatus($event)"
                        class="w-full"
                        name="products-status"
                        :suggestions="store.filtered_status"
                        @complete="store.searchStatus($event)"

                        :dropdown="true" optionLabel="name"
                        data-testid="products-status"
                        forceSelection>
                    </AutoComplete>
                    <label for="products-status">Select Status <span class="text-red-500">*</span></label>
                </FloatLabel>
                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea  rows="5" cols="30" class="w-full"
                              name="products-status_notes"
                              data-testid="products-status_notes"
                              v-model="store.item.status_notes"/>
                    <label for="products-status_notes">Enter a Status Note</label>
                </FloatLabel>

                <VhField label="Is Active">
                    <ToggleSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="products-active"
                                 data-testid="products-active"
                                 v-model="store.item.is_active"/>
                </VhField>


            </div>
        </Panel>


</template>

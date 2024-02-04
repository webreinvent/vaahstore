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

                <VhField label="Availablity Date">
                    <Calendar tabindex="0"
                              :showIcon="true"
                              class="w-full"
                              name="brands-registered_at"
                              id="registered_at"
                              value="registered_at"
                              data-testid="brands-registered_at"
                              dateFormat="yy-mm-dd"
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
                             [{ 'color': [] }, { 'background': [] }],
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

                <VhField label="Seo Title">
                    <InputText class="w-full"
                               name="products-seo-title"
                               data-testid="products-seo-title"
                               placeholder="Enter Seo Title"
                               v-model="store.item.seo_title"/>
                </VhField>

                <VhField label="Seo Description">
                    <Textarea class="w-full"
                              name="products-seo-description"
                              data-testid="products-seo-description"
                              placeholder="Enter Seo Description"
                              rows="3" cols="30"
                              v-model="store.item.seo_meta_description"
                    />
                </VhField>


                <VhField label="Seo Keywords">
                    <Chips class="w-full"
                           style="display:flex;flex-wrap:wrap;width:100%"
                           name="products-seo-meta-keywords"
                           data-testid="products-seo-meta-keywords"
                           placeholder="Enter Seo Meta keywords"
                           v-model="store.item.seo_meta_keyword"
                           separator=","
                            />

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

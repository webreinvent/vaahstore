<script setup>
import {computed, onMounted, ref, watch} from "vue";
import { useProductMediaStore } from '../../stores/store-productmedias'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductMediaStore();
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


</script>
<template>

    <div class="col-6" >

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
                            data-testid="productmedias-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="productmedias-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            class="p-button-sm"
                            data-testid="productmedias-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="productmedias-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            class="p-button-sm"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        class="p-button-sm"
                        data-testid="productmedias-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="productmedias-to-list"
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
                            Trashed {{store.item.deleted_at}}
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




                <VhField label="Product*">
                    <AutoComplete
                                            value="id"
                                            v-model="store.item.product"
                                            @change="store.addProduct($event)"
                                            class="w-full relative"
                                            :suggestions="store.product_suggestion"
                                            @complete="store.searchProduct($event)"
                                            :pt="{
                                                panel: { class: 'w-16rem' },
                                                item: { style: {
                                                    textWrap: 'wrap'
                                                }  }
                                                }"
                                            placeholder="Select Product"
                                            append-to="self"
                                            data-testid="productmedias-product"
                                            name="productmedias-product"
                                            :dropdown="true" optionLabel="name" forceSelection>
                                        </AutoComplete>

                </VhField>

                <VhField label="Product Variations" >
                    <AutoComplete
                        data-testid="productmedias-product_variation"
                        v-model="store.item.product_variation"
                        optionLabel="name"
                        multiple
                        :complete-on-focus = "true"
                        :suggestions="store.product_variation_list"
                        @complete="store.searchVariationOfProduct($event)"
                        placeholder="Select Product Variations"
                        class="w-full "

                    />
                </VhField>


                <VhField label="Media*">

                    <FileUpload customUpload
                                name="demo[]"
                                ref="upload_refs"
                                @upload="store.onImageUpload($event)"
                                @select="store.onImageUpload"
                                :multiple="!route.params.id"
                                accept="image/*"
                                :pt="{
                                    root: {style: {maxHeight: '300px', overflowY: 'scroll'} }
                                }"
                                :maxFileSize="1000000"
                                @remove="store.removeUploadedFile"
                    >
                        <template #empty>


                            <div v-if="store.item.images && store.item.images.length > 0">
                                <div class="flex flex-wrap sm:p-5 gap-5">
                                    <div v-for="(file, index) of store.item.images" :key="file.name + file.type + file.size" class="card m-0 px-6 flex flex-column align-items-center gap-3">
                                        <div>
                                            <Image role="presentation"
                                                 :alt="file.name"
                                                 :src="file.url"
                                                 width="150"
                                                 preview
                                                 height="150"
                                                 class="shadow-2" />
                                        </div>
                                        <span severity="success">{{ file.name }}</span>

                                        <Button icon="pi pi-times"
                                                @click="store.onRemoveTemplatingFile(store.item.images,index)"
                                                outlined
                                                rounded
                                                severity="danger" />
                                    </div>
                                </div>
                            </div>
                            <div  v-else>
                                <p>Drag and drop files to here to upload.</p>
                            </div>
                        </template>
                    </FileUpload>
                </VhField>
                <VhField label="Alt Text">
                    <InputText class="w-full" v-model="store.item.name" data-testid="media_name" />
                </VhField>
                <div v-if="store.item.images && store.item.images.length > 0">
                    <VhField label="Media Type" >
                    <InputText class="w-full"
                               name="productmedias-type"
                               data-testid="productmedias-type"
                               disabled
                               v-model="store.item.type"/>
                </VhField>
                </div>


                <VhField label="Status*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        name="productmedias-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="productmedias-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="productmedias-status_notes"
                              data-testid="productmedias-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="productmedias-active"
                                 data-testid="productmedias-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

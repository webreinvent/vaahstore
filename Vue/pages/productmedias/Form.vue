<script setup>
import {onMounted, ref, watch} from "vue";
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
                            data-testid="productmedias-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
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


                <VhField label="Product">

                    <AutoComplete
                        value="id"
                        v-model="store.item.product"
                        @change="store.setProduct($event)"
                        class="w-full"
                        :suggestions="store.product_suggestion"
                        @complete="store.searchProduct($event)"
                        placeholder="Select Product"
                        data-testid="productmedias-product"
                        name="productmedias-product"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Product Variation">

                    <AutoComplete
                        value="id"
                        v-model="store.item.product_variation"
                        @change="store.setProductVariation($event)"
                        class="w-full"
                        :suggestions="store.product_variation_suggestion"
                        @complete="store.searchProductVariation($event)"
                        placeholder="Select Product Variation"
                        data-testid="productmedias-product_variation"
                        name="productmedias-product_variation"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Image">
                    <FileUpload customUpload
                                name="demo[]"
                                @upload="store.onImageUpload($event)"
                                @select="store.onImageUpload"
                                :multiple="!route.params.id"
                                accept="image/*"
                                :maxFileSize="1000000">
                        <template #empty>
                            <Image v-if="store.item.product_media_images && store.item.product_media_images.length > 0"
                                   class="p-1"
                                   v-for="(item) in store.item.product_media_images"
                                   :src="store.item.base_path+'/'+item['url']"
                                   preview
                                   alt="Image"
                                   width="150" />
                            <Image v-else-if="store.item.url"
                                    :src="store.item.base_path+'/'+store.item.url"
                                   preview
                                   alt="Image"
                                   width="150" />
                            <template v-else>
                                <p>Drag and drop files to here to upload.</p>
                            </template>
                        </template>
                    </FileUpload>
                </VhField>


                <VhField label="Status">
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
                                 class="p-inputswitch-sm"
                                 name="productmedias-active"
                                 data-testid="productmedias-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

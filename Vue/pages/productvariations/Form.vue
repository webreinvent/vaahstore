<script setup>
import {onMounted, ref, watch} from "vue";
import { useProductVariationStore } from '../../stores/store-productvariations'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductVariationStore();
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
                            data-testid="productvariations-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="productvariations-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="productvariations-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="productvariations-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="productvariations-name"
                               data-testid="productvariations-name"
                               placeholder="Enter Name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="productvariations-slug"
                               data-testid="productvariations-slug"
                               placeholder="Enter Slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Product">

                    <AutoComplete
                        v-model="store.item.product"
                        class="w-full"
                        :suggestions="store.suggestion"
                        @complete="store.searchProduct($event)"
                        placeholder="Select Product"
                        data-testid="productvariations-product"
                        name="productvariations-product"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="SKU">
                    <InputText class="w-full"
                               name="productvariations-sku"
                               data-testid="productvariations-sku"
                               placeholder="Enter SKU"
                               v-model="store.item.sku"/>
                </VhField>

                <VhField label="Status">
                    <AutoComplete
                        v-model="store.item.status"
                        class="w-full"
                        name="productvariations-status"
                        :suggestions="store.suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="productvariations-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="5" cols="30"
                              placeholder="Enter a Status Note"
                              name="productvariations-status_notes"
                              data-testid="productvariations-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="productvariations-active"
                                 data-testid="productvariations-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

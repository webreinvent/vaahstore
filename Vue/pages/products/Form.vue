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
                            data-testid="products-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="products-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="products-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="products-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="products-name"
                               data-testid="products-name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="products-slug"
                               data-testid="products-slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="products-active"
                                 data-testid="products-active"
                                 v-model="store.item.is_active"/>
                </VhField>

                <VhField label="Status">
                    <Dropdown
                        v-model="store.item.status"
                        :options="store.status"
                        name="products-status"
                        placeholder="Select a Status"
                        data-testid="products-status"
                        :editable="true"/>
                </VhField>

                <VhField label="Status Notes">
                    <InputText class="w-full"
                               placeholder="Enter a Status Note"
                               name="products-status_notes"
                               data-testid="products-status_notes"
                               v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="In Stock">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="products-in_stock"
                                 data-testid="products-in_stock"
                                 v-model="store.item.in_stock"/>
                </VhField>

                <VhField label="Quantity" v-if="store.item.in_stock">
                    <InputNumber
                        inputId="minmax-buttons"
                        v-model="store.item.quantity"
                        mode="decimal" showButtons
                        :min="1"/>
                </VhField>

<!--                <VhField label="Registered By ">-->

<!--                    <AutoComplete-->
<!--                        v-model="store.item.store"-->
<!--                        class="w-full"-->
<!--                        :suggestions="store.suggestion"-->
<!--                        @complete="store.searchStore($event)"-->
<!--                        placeholder="Select User"-->
<!--                        :dropdown="true" optionLabel="first_name" forceSelection>-->
<!--                    </AutoComplete>-->

<!--                </VhField>-->

            </div>
        </Panel>

    </div>

</template>

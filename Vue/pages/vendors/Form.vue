<script setup>
import {onMounted, ref, watch} from "vue";
import { useVendorStore } from '../../stores/store-vendors'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useVendorStore();
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
                            data-testid="vendors-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="vendors-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="vendors-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="vendors-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="vendors-name"
                               data-testid="vendors-name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="vendors-slug"
                               data-testid="vendors-slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Store">
                    <AutoComplete v-model="store.item.vh_st_store_id" class="w-full" :suggestions="store.store_suggestion_list" @complete="store.searchStore($event)" :dropdown="true" optionLabel="name" forceSelection>
                        <template>
                            <div class="store-item">
                                <div class="ml-2">{{store.all_store_list}}</div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Approve By">
                    <AutoComplete v-model="store.item.approved_by" class="w-full" :suggestions="store.user_suggestion_list" @complete="store.searchUser($event)" :dropdown="true" optionLabel="first_name" forceSelection>
                        <template>
                            <div class="store-item">
                                <div class="ml-2">{{store.all_user_list}}</div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>

                <VhField label="Status">
                    <AutoComplete v-model="store.item.taxonomy_id_vendor_status" class="w-full" :suggestions="store.status_suggestion_list" @complete="store.searchStatus($event)" :dropdown="true" optionLabel="name" forceSelection>
                        <template>
                            <div class="store-item">
                                <div class="ml-2">{{store.status_option}}</div>
                            </div>
                        </template>
                    </AutoComplete>

                </VhField>

                <VhField label="Status Notes">
                    <Textarea v-model="store.item.status_notes" rows="5" cols="30" />
                </VhField>

                <VhField label="Owned By">
                    <AutoComplete v-model="store.item.owned_by" class="w-full" :suggestions="store.owned_by_suggestion_list" @complete="store.searchOwnedBy($event)" :dropdown="true" optionLabel="name" forceSelection>
                        <template>
                            <div class="store-item">
                                <div class="ml-2">{{store.all_user_list}}</div>
                            </div>
                        </template>
                    </AutoComplete>
                </VhField>


                <VhField label="Is Default">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="vendors-default"
                                 data-testid="vendors-default"
                                 v-model="store.item.is_default"/>
                </VhField>

                <VhField label="Auto Approve Products">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="vendors-auto-approve-products"
                                 data-testid="vendors-auto-approve-products"
                                 v-model="store.item.auto_approve_products"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="vendors-active"
                                 data-testid="vendors-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

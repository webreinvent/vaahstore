<script setup>
import {onMounted, ref, watch} from "vue";
import { useAttributeGroupStore } from '../../stores/store-attributegroups'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useAttributeGroupStore();
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

    <div>

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
                            data-testid="attributegroups-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="attributegroups-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            class="p-button-sm"
                            data-testid="attributegroups-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="attributegroups-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            class="p-button-sm"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        data-testid="attributegroups-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="attributegroups-to-list"
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


                <VhField label="Name*">
                    <InputText class="w-full"
                               placeholder="Enter Name"
                               name="attributegroups-name"
                               data-testid="attributegroups-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug*">
                    <InputText class="w-full"
                               placeholder="Enter Slug"
                               name="attributegroups-slug"
                               data-testid="attributegroups-slug"
                               v-model="store.item.slug"/>
                </VhField>

                <vhField label="Attributes*">



                    <AutoComplete
                                  data-testid="attributegroups-attributes"
                                  v-model="store.item.active_attributes"
                                  @change="store.setAttribute($event)"
                                  optionLabel="name"
                                  multiple
                                  :dropdown="true"
                                  :complete-on-focus = "true"
                                  :suggestions="store.active_attributes_name"
                                  @complete="store.searchActiveAttributes"
                                  placeholder="Select Attributes"
                                  class="w-full "
                                  :pt="{
                                      token: {
                                        class: 'max-w-full'
                                      },
                                      removeTokenIcon: {
                                          class: 'min-w-max'
                                      }
                                  }"
                    >
<!--                        <template #option="slotProps">-->
<!--                                                      <div class="flex align-items-center">-->
<!--                                                         <span>{{slotProps.option.name}}</span>-->
<!--                                                       (<b>{{slotProps.option.type}}</b>)-->
<!--                                                   </div>-->
<!--                                            </template>-->
                                                </AutoComplete>

                                            </vhField>

                                            <VhField label="Description">
                                                <Textarea v-model="store.item.description"
                                                          rows="3" class="w-full"
                                                          placeholder="Enter Description"
                                                          data-testid="attributegroups-description"
                                                          :autoResize="true"/>
                                            </VhField>

                                            <VhField label="Is Active">
                                                <ToggleSwitch v-bind:false-value="0"
                                                             v-bind:true-value="1"
                                                             class="p-inputswitch"
                                                             name="attributegroups-active"
                                                             data-testid="attributegroups-active"
                                                             v-model="store.item.is_active"/>
                                            </VhField>

                                        </div>
                                    </Panel>

                                </div>

                            </template>

<script setup>
import {onMounted, ref, watch} from "vue";
import { useCustomerGroupStore } from '../../stores/store-customergroups'
import { useRootStore } from '@/stores/root.js'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useCustomerGroupStore();
const root = useRootStore();
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
                            data-testid="customergroups-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>
                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="customergroups-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="customergroups-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="customergroups-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>



                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="customergroups-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="customergroups-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="pt-2">

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

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="customergroups-name"
                               data-testid="customergroups-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                    <label for="customergroups-name">Name <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="customergroups-slug"
                               data-testid="customergroups-slug"
                               v-model="store.item.slug"/>
                    <label for="customergroups-slug">Slug <span class="text-red-500">*</span></label>

                </FloatLabel>


                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <AutoComplete
                        name="customergroups-customer-filter"
                        data-testid="customergroups-customer-filter"
                        v-model="store.item.customers"
                        @change="store.updateCustomerList()"
                        option-label = "display_name"
                        multiple
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
                        :complete-on-focus = "true"
                        :suggestions="store.customer_suggestions"
                        @complete="store.searchCustomers($event)"
                        class="w-full">
                    </AutoComplete>
                    <label for="customergroups-customer">Select Customers <span class="text-red-500">*</span></label>

                </FloatLabel>


                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <AutoComplete
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        value="id"
                        name="customergroups-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        :dropdown="true" optionLabel="name"
                        data-testid="customergroups-status"
                        forceSelection>
                    </AutoComplete>
                    <label for="customergroups-status">Select Status <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea rows="3" class="w-full"
                              name="customergroups-status_notes"
                              data-testid="customergroups-status_notes"
                              v-model="store.item.status_notes"/>
                    <label for="customergroups-status_notes">Enter a Status Note  <span class="text-red-500">*</span></label>

                </FloatLabel>

            </div>
        </Panel>


</template>

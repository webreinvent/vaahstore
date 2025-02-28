<script setup>
import {onMounted, ref, watch} from "vue";
import { useAttributeStore } from '../../stores/store-attributes'
import { useRootStore } from '@/stores/root.js'
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useAttributeStore();
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
                            data-testid="attributes-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="attributes-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            class="p-button-sm"
                            data-testid="attributes-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="attributes-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        data-testid="attributes-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="attributes-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div class="mt-2" v-if="store.item">

                <Message severity="error"
                         class="p-container-message"
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
                                    data-testid="attributes-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>


                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="attributes-name"
                               data-testid="attributes-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                    <label for="attributes-name">Name <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="attributes-slug"
                               data-testid="attributes-slug"
                               v-model="store.item.slug"/>
                    <label for="attributes-slug">Slug <span class="text-red-500">*</span></label>

                </FloatLabel>


                    <div class="p-inputgroup flex-1">
                        <FloatLabel :variant="store.float_label_variants">
                        <InputText v-model="store.attribute_new_value" v-on:keyup.enter="store.addAttributeNewValue()"/>
                        <Button severity="Primary" @click="store.addAttributeNewValue()">Add</Button>
                            <label for="attributes-value">Enter Attribute Value <span class="text-red-500">*</span></label>

                        </FloatLabel>
                    </div>
                    <div v-for="item in store.item.value">
                        <div class="p-inputgroup flex-1 p-1" v-if="item.is_active == 1">
                            <InputText disabled="" :value="item.value"/>
                            <Button class="danger" @click="store.deleteAttributeValue(item.value)">Remove</Button>
                        </div>
                    </div>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="attributes-type"
                               data-testid="attributes-type"
                               v-model="store.item.type"/>
                    <label for="attributes-type">Enter Type <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <Textarea v-model="store.item.description"
                              rows="3" class="w-full"
                              data-testid="attributes-description"
                              :autoResize="true"/>
                    <label for="attributes-value">Enter Description <span class="text-red-500">*</span></label>

                </FloatLabel>


                <VhField label="Is Active">
                    <ToggleSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="attributes-active"
                                 data-testid="attributes-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>


</template>

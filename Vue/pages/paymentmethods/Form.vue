<script setup>
import {onMounted, ref, watch} from "vue";
import { usePaymentMethodStore } from '../../stores/store-paymentmethods'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import { useRootStore } from '@/stores/root.js'
import {useRoute} from 'vue-router';


const store = usePaymentMethodStore();
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
                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="paymentmethods-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="paymentmethods-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="paymentmethods-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            class="p-button-sm"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="paymentmethods-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="paymentmethods-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="pt-2">

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="paymentmethods-name"
                               data-testid="paymentmethods-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                    <label for="paymentmethods-name">Name <span class="text-red-500">*</span></label>

                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="paymentmethods-slug"
                               data-testid="paymentmethods-slug"
                               v-model="store.item.slug"/>
                    <label for="paymentmethods-slug">Slug</label>

                </FloatLabel>

                <div class="flex items-center gap-2 my-3" >
                    <ToggleSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="paymentmethods-active"
                                 data-testid="paymentmethods-active"
                                 v-model="store.item.is_active"/>
                    <label for="paymentmethods-active">Is Active</label>
                </div>

            </div>
        </Panel>


</template>

<script setup>
import {onMounted, ref, watch} from "vue";
import { useAttributeStore } from '../../stores/store-attributes'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useAttributeStore();
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
                            data-testid="attributes-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
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
                        data-testid="attributes-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="attributes-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="attributes-name"
                               data-testid="attributes-name"
                               placeholder="Enter Name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="attributes-slug"
                               data-testid="attributes-slug"
                               placeholder="Enter Slug"
                               v-model="store.item.slug"/>
                </VhField>

                <vhField label="value">
                    <div class="p-inputgroup flex-1">
                        <InputText placeholder="Enter Attribute Value" v-model="store.attribute_new_value" v-on:keyup.enter="store.addAttributeNewValue()"/>
                        <Button severity="Primary" @click="store.addAttributeNewValue()">Add</Button>
                    </div>
                    <div v-for="item in store.item.value">
                        <div class="p-inputgroup flex-1 p-1" v-if="item.is_active == 1">
                            <InputText placeholder="Enter Attribute Value" disabled="" :value="item.value"/>
                            <Button class="danger" @click="store.deleteAttributeValue(item.value)">Remove</Button>
                        </div>
                    </div>
                </vhField>

<!--                <VhField label="Value">-->
<!--                    <InputText class="w-full"-->
<!--                               name="attributes-value"-->
<!--                               data-testid="attributes-value"-->
<!--                               v-model="store.item.value"/>-->
<!--                </VhField>-->

                <VhField label="Type">
                    <InputText class="w-full"
                               name="attributes-type"
                               data-testid="attributes-type"
                               placeholder="Enter Type"
                               v-model="store.item.type"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="attributes-active"
                                 data-testid="attributes-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

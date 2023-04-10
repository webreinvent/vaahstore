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
                            data-testid="attributegroups-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="attributegroups-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="attributegroups-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="attributegroups-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="attributegroups-name"
                               data-testid="attributegroups-name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="attributegroups-slug"
                               data-testid="attributegroups-slug"
                               v-model="store.item.slug"/>
                </VhField>

                <vhField label="Attributes">
                    <MultiSelect v-model="store.item.active_attributes"
                                 filter
                                 :options="store.attribute_list"
                                 data-testid="attributegroups-attributes"
                                 optionLabel="name"
                                 placeholder="Select attributes"
                                 display="chip"
                                 class="w-full">
                        <template #option="slotProps">
                            <div class="flex align-items-center">
                                <span>{{slotProps.option.name}}</span>
                                (<b>{{slotProps.option.type}}</b>)
                            </div>
                        </template>
                        <template #footer>
                            <div class="py-2 px-3">
                                <b>{{ store.item.attributes ? store.item.attributes.length : 0 }}</b>
                                item{{ (store.item.attributes ? store.item.attributes.length : 0) > 1 ? 's' : '' }} selected.
                            </div>
                        </template>
                    </MultiSelect>

                </vhField>

                <VhField label="Description">
                    <Textarea v-model="store.item.description" data-testid="attributegroups-description" :autoResize="true" rows="5" cols="30" />
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="attributegroups-active"
                                 data-testid="attributegroups-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

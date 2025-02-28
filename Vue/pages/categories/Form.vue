<script setup>
import {onMounted, ref, watch} from "vue";
import {useCategoryStore} from '../../stores/store-categories'
import { useRootStore } from '@/stores/root.js'
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useCategoryStore();
const route = useRoute();
const root = useRootStore();
onMounted(async () => {
    /**
     * Fetch the record from the database
     */
    if ((!store.item || Object.keys(store.item).length < 1)
        && route.params && route.params.id) {
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

                    <Button
                            v-tooltip.left="'View'"
                            v-if="store.item && store.item.id"
                            data-testid="categories-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="categories-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="categories-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="categories-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true"/>
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="categories-to-list"
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
                            Deleted {{ store.item.deleted_at }}
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

                        <TreeSelect

                            v-model="store.item.parent_category"
                            :options="store.categories_dropdown_data"
                            :show-count="true"
                            data-testid="categories-parent_category"
                            @change="store.setParentId()"
                            class="w-full"
                        />



                    <label for="articles-name">Parent Category</label>
                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                    <InputText class="w-full"
                               name="categories-name"
                               data-testid="categories-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name" required/>
                    <label for="categories-name">Enter Name<span class="text-red-500">*</span></label>
                </FloatLabel>

                <FloatLabel class="my-3" :variant="store.float_label_variants">
                        <InputText class="w-full"
                                   name="categories-slug"
                                   data-testid="categories-slug"
                                   v-model="store.item.slug" required/>
                        <div class="required-field hidden"></div>
                    <label for="categories-slug">Enter Slug<span class="text-red-500">*</span></label>
                </FloatLabel>

                <div class="flex items-center gap-2 my-3" >
                    <ToggleSwitch
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        class="p-inputswitch"
                        name="stores-active"
                        data-testid="stores-active"
                        v-model="store.item.is_active"
                    />
                    <label for="stores-active">Is Active</label>
                </div>

            </div>
        </Panel>


</template>

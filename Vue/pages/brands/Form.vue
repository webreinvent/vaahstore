<script setup>
import {onMounted, ref, watch} from "vue";
import {useBrandStore} from '../../stores/store-brands'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useBrandStore();
const route = useRoute();

onMounted(async () => {
    if (route.params && route.params.id) {
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

watch(() => store && store.item && store.item.name, (item_name) => {
    try {
        if (item_name.length === 0) {
            store.item.slug = '';
        }
    } catch (error) {
    }
});

</script>
<template>

    <div class="col-6">

        <Panel class="is-small">

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title"
                         :disabled="!store.assets.permissions.includes('can-update-module')">
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
                            data-testid="brands-save"
                            @click="store.itemAction('save')"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="brands-create-and-new"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            icon="pi pi-save"/>

                    <Button data-testid="brands-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            class="p-button-sm"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="brands-form-menu"
                        icon="pi pi-angle-down"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true"/>
                    <!--/form_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="brands-to-list"
                            @click="store.toList()">
                    </Button>
                </div>


            </template>

            <div v-if="store.item" class="pt-2">

                <VhField label="Name*">
                    <InputText class="w-full"
                               name="brands-name"
                               data-testid="brands-name"
                               placeholder="Enter Name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug*">
                    <InputText class="w-full"
                               name="brands-slug"
                               data-testid="brands-slug"
                               placeholder="Enter Slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="" class="mb-0">
                    <Message v-if="!store.item.image" class="mt-2" severity="warn" :closable="false">Type: jpeg, jpg, png & Size: less than 1mb</Message>
                </VhField>


                <VhField label="Brand Logo">
                    <FileUpload
                        name="image"
                        accept=".jpeg,.jpg,.png,"
                        :maxFileSize="1024*1024"
                        :custom-upload="true"
                        mode="advanced"
                        :multiple="false"
                        @uploader="store.upload" >
                        <template #empty class="flex">
                            <p v-if="!store.item.image">Drag and drop files here to upload.</p>
                            <p v-if="store.item.image">
                                <img class="w-5 h-4rem" :src="`image/uploads/brands/`+store.item.image"/>
                                <i class="pi pi-times text-2xl font-bold cursor-pointer text-red-500 ml-2" @click="store.clearimage"></i>

                            </p>


                        </template>

                    </FileUpload>
                </VhField>


                <VhField label="Meta Title">
                    <InputText class="w-full"
                               name="brands-slug"
                               data-testid="brands-meta_title"
                               placeholder="Enter Meta Title"
                               v-model="store.item.meta_title"/>
                </VhField>

                <VhField label="Meta Description">
                    <Textarea rows="3" class="w-full"
                              name="brands-meta_description"
                              data-testid="brands-meta_description"
                              :autoResize="true"
                              placeholder="Enter Meta Description"
                              v-model="store.item.meta_description"/>
                </VhField>

                <VhField label="Meta Keyword" >
                    <Chips class="w-full"
                           v-model="store.item.meta_keyword"
                           placeholder="Enter Meta keyword"
                           separator=","  />
                </VhField>

                <VhField label="Registered By">

                    <AutoComplete
                        value="id"
                        v-model="store.item.registered_by_user"
                        @change="store.setRegisteredBy($event)"
                        class="w-full"
                        name="brands-registered_by"
                        id="registered_by"
                        data-testid="brands-registered_by"
                        :suggestions="store.registered_by_suggestion"
                        @complete="store.searchRegisteredBy($event)"
                        placeholder="Select Registered By"
                        :dropdown="true" optionLabel="first_name" forceSelection>
                    </AutoComplete>

                </VhField>
{{store.item.registered_at}}
                <VhField label="Registered at">
                    <Calendar tabindex="0"
                              :showIcon="true"
                              class="w-full"
                              name="brands-registered_at"
                              id="registered_at"
                              data-testid="brands-registered_at"
                              dateFormat="yy-mm-dd"
                              :showTime="true" :showSeconds="true"
                              placeholder="Select Date and Time"
                              v-model="store.item.registered_at"></Calendar>
                </VhField>

                <VhField label="Approved By">
                    <AutoComplete
                        v-model="store.item.approved_by_user"
                        @change="store.setApprovedBy($event)"
                        class="w-full"
                        name="brands-approved_by"
                        id="approved_by"
                        value="id"
                        data-testid="brands-approved_by"
                        :suggestions="store.approved_by_suggestion"
                        @complete="store.searchApprovedBy($event)"
                        placeholder="Select Approved by"
                        :dropdown="true" optionLabel="first_name" forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Approved at">
                    <Calendar tabindex="0"
                              :showIcon="true"
                              class="w-full"
                              name="brands-approved_at"
                              id="approved_at"
                              data-testid="brands-approved_at"
                              dateFormat="yy-mm-dd"
                              :showTime="true" :showSeconds="true"
                              placeholder="Select Date and Time"
                              v-model="store.item.approved_at"></Calendar>
                </VhField>


                <VhField label="Status*">
                    <AutoComplete
                        v-model="store.item.status"
                        class="w-full"
                        value="id"
                        name="brands-status"
                        data-testid="brands-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatusBrands($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              name="brands-status_notes"
                              data-testid="brands-status_notes"
                              :autoResize="true"
                              placeholder="Enter Status Note"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="brands-active"
                                 data-testid="brands-active"
                                 v-model="store.item.is_active"
                                 :pt="{
                                        slider: ({ props }) => ({
                                            class: props.modelValue ? 'bg-green-600' : ' '
                                        })
                                 }"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                    />
                </VhField>





            </div>
        </Panel>

    </div>

</template>
<style>
#pv_id_5_panel{
    width: 400px;
}
.p-datepicker{
    width: 400px;
}
</style>

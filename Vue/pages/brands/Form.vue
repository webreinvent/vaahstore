<script setup>
import {onMounted, ref, watch} from "vue";
import { useBrandStore } from '../../stores/store-brands'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useBrandStore();
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
                            data-testid="brands-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="brands-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="brands-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="brands-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="brands-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <VhField label="Name">
                    <InputText class="w-full"
                               name="brands-name"
                               data-testid="brands-name"
                               placeholder="Enter a Name"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug">
                    <InputText class="w-full"
                               name="brands-slug"
                               data-testid="brands-slug"
                               placeholder="Enter a Slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Registered By ">

                    <AutoComplete
                        v-model="store.item.registered_by"
                        class="w-full"
                        name="brands-registered_by"
                        id="registered_by"
                        value="registered_by"
                        data-testid="brands-registered_by"
                        :suggestions="store.suggestion"
                        @complete="store.searchRegisteredBy($event)"
                        placeholder="Select Registered By"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>

                </VhField>

                <VhField label="Registered at" >
                    <Calendar tabindex="0"
                              :showIcon="true"
                              name="brands-registered_at"
                              id="registered_at"
                              value="registered_at"
                              data-testid="brands-registered_at"
                              dateFormat="yy-mm-dd"
                              :showTime="true" :showSeconds="true"
                              placeholder="Select date"
                              v-model="store.item.registered_at"></Calendar>
                </VhField>
<!--                :disabled="store.disable_approved_by"-->
                <VhField label="Approved By">
                    <AutoComplete
                        v-model="store.item.approved_by"
                        class="w-full"
                        name="brands-approved_by"
                        id="approved_by"
                        value="approved_by"
                        data-testid="brands-approved_by"
                        :suggestions="store.suggestion"
                        @complete="store.searchApprovedBy($event)"
                        placeholder="Select Approved by"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Approved at" >
                    <Calendar tabindex="0"
                              :showIcon="true"
                              name="brands-approved_at"
                              id="approved_at"
                              value="approved_at"
                              data-testid="brands-approved_at"
                              dateFormat="yy-mm-dd"
                              :showTime="true" :showSeconds="true"
                              placeholder="Select date"
                              v-model="store.item.approved_at"></Calendar>
                </VhField>

                <VhField label="Status">
                    <AutoComplete
                        v-model="store.item.status"
                        class="w-full"
                        name="brands-status"
                        data-testid="brands-status"
                        :suggestions="store.suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="5" cols="30"
                               name="brands-status_notes"
                               data-testid="brands-status_notes"
                              :autoResize="true"
                              placeholder="Enter Status Note"
                               v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="brands-active"
                                 data-testid="brands-active"
                                 v-model="store.item.is_active"/>
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

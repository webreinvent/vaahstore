<script setup>
import {onMounted, ref, watch} from "vue";
import { useWhishlistStore } from '../../stores/store-whishlists'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useWhishlistStore();
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
                            data-testid="whishlists-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="whishlists-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        data-testid="whishlists-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary"
                            icon="pi pi-times"
                            data-testid="whishlists-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">
                <VhField label="User">
                    <AutoComplete
                        v-model="store.item.vh_user_id"
                        class="w-full"
                        :suggestions="store.user_suggestion"
                        @complete="store.searchUser($event)"
                        placeholder="Select User"
                        data-testid="whishlists-user"
                        name="whishlists-user"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Type">
                    <AutoComplete
                        v-model="store.item.taxonomy_id_whishlists_types"
                        class="w-full"
                        :suggestions="store.type_suggestion"
                        @complete="store.searchType($event)"
                        placeholder="Select Type"
                        data-testid="whishlists-type"
                        name="whishlists-type"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status">
                    <AutoComplete
                        v-model="store.item.taxonomy_id_whishlists_status"
                        class="w-full"
                        name="whishlists-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="whishlists-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="whishlists-status_notes"
                              data-testid="whishlists-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Default">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="whishlists-is_default"
                                 data-testid="whishlists-is_default"
                                 v-model="store.item.is_default"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

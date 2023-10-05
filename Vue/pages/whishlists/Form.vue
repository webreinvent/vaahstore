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

    <div class="col-6" >

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
                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="whishlists-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="whishlists-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="whishlists-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            class="p-button-sm"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="whishlists-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="whishlists-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="pt-2">

                <VhField label="User">
                    <AutoComplete
                        value="id"
                        v-model="store.item.user"
                        @change="store.setUser($event)"
                        class="w-full"
                        :suggestions="store.user_suggestion"
                        @complete="store.searchUsers($event)"
                        placeholder="Select User"
                        data-testid="whishlists-user"
                        name="whishlists-user"
                        :dropdown="true" optionLabel="name" forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Type">
                    <AutoComplete
                        value="id"
                        v-model="store.item.whishlist_type"
                        @change="store.setWhishlistsType($event)"
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
                        value="id"
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
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

<!--                <VhField label="Is Active">-->
<!--                    <InputSwitch v-bind:false-value="0"-->
<!--                                 v-bind:true-value="1"-->
<!--                                 class="p-inputswitch-sm"-->
<!--                                 name="whishlists-active"-->
<!--                                 data-testid="whishlists-active"-->
<!--                                 v-model="store.item.is_active"/>-->
<!--                </VhField>-->

            </div>
        </Panel>

    </div>

</template>

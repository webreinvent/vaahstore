<script setup>
import {onMounted, ref, watch} from "vue";
import { useWishlistStore } from '../../stores/store-wishlists'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';

const store = useWishlistStore();
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
                    <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                            label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="wishlists-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                            label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="wishlists-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="wishlists-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            class="p-button-sm"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <!--form_menu-->
                    <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="wishlists-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="wishlists-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="pt-2">

                <VhField label="User*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.user"
                        @change="store.setUser($event)"
                        class="w-full"
                        :suggestions="store.user_suggestion"
                        @complete="store.searchUsers($event)"
                        placeholder="Select User"
                        data-testid="wishlists-user"
                        name="wishlists-user"
                        :dropdown="true"
                        optionLabel="first_name"
                        forceSelection
                        :pt="{
                          token: {
                                    class: 'max-w-full'
                                  },
                          removeTokenIcon: {
                                    class: 'min-w-max'
                          },
                          item: { style:
                                {
                                textWrap: 'wrap'
                                }  },
                          panel: { class: 'w-16rem ' }
                            }"
                        >
                    </AutoComplete>
                </VhField>

                <VhField label="Name*">
                    <InputText class="w-full"
                               placeholder="Enter Name"
                               name="stores-name"
                               data-testid="stores-name"
                               @update:modelValue="store.watchItem"
                               v-model="store.item.name"/>
                </VhField>

                <VhField label="Slug*">
                    <InputText class="w-full"
                               placeholder="Enter Slug"
                               name="stores-slug"
                               data-testid="stores-slug"
                               v-model="store.item.slug"/>
                </VhField>

                <VhField label="Is Shareable">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="wishlists-is_default"
                                 data-testid="wishlists-is_default"
                                 v-model="store.item.type"/>
                </VhField>

                <VhField label="Status*">
                    <AutoComplete
                        value="id"
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        name="wishlists-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="wishlists-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>


                <VhField label="Status Notes">
                    <Textarea rows="3" class="w-full"
                              placeholder="Enter a Status Note"
                              name="wishlists-status_notes"
                              data-testid="wishlists-status_notes"
                              v-model="store.item.status_notes"/>
                </VhField>

                <VhField label="Is Default">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch"
                                 name="wishlists-is_default"
                                 data-testid="wishlists-is_default"
                                 v-model="store.item.is_default"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>

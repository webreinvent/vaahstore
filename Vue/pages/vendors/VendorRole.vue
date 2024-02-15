<script setup>
import {onMounted, ref, watch} from "vue";

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';
import {useVendorStore} from "../../stores/store-vendors";


const store = useVendorStore();
const route = useRoute();

onMounted(async () => {
    if(route.params && route.params.id)
    {
        await store.getItem(route.params.id);

    }
});

//--------selected_menu_state
const selected_menu_state = ref();
const toggleSelectedMenuState = (event) => {
    selected_menu_state.value.toggle(event);
};
//--------/selected_menu_state


</script>
<template>

    <div class="col-8" >

        <Panel >

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <b>Add Role For Vendor</b>
                    </div>

                </div>


            </template>

            <template #icons>

                <div class="p-inputgroup">
                    <Button label="Save"
                            v-if="store.item && store.item.id"
                            data-testid="products-save"
                            class="p-button-sm"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            @click="store.saveProduct()"
                            icon="pi pi-save"/>

                    <Button data-testid="products-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            class="p-button-sm"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="products-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item">

                <!--                user error message-->
                <div v-if="store.user_error_message && store.user_error_message.length > 0">
                    <Message severity="error" v-for="(item) in store.user_error_message">{{item}}</Message>
                </div>

                <!--                dropdown to select Product -->
                <div class="flex flex-wrap gap-3 pb-2 p-1">
                    <div class="col-10">
                        <AutoComplete v-model="store.selected_user"
                                      value="id"
                                      data-testid="vendors-taxonomy_id_vendor_status"
                                      name="-products"
                                      class="w-full"
                                      placeholder="Search User"
                                      :suggestions="store.user_data"
                                      @complete="store.searchUser($event)"
                                      :dropdown="true"
                                      optionLabel="name"
                                      :pt="{
                                      token: {
                                        class: 'max-w-full'
                                      },
                                      removeTokenIcon: {
                                          class: 'min-w-max'
                                      },
                                      item: { style: {
                                                    textWrap: 'wrap'
                                                }  },
                                       panel: { class: 'w-16rem ' }
                                  }"
                                      forceSelection
                                      style="height:35px;">

                            <template #option="slotProps">
                                <div>{{ slotProps.option.name }}</div>
                            </template>
                        </AutoComplete>



                    </div>

                    <div class="p-1">
                        <Button type="button"
                                label="Add"
                                :disabled="!store.assets.permissions.includes('can-update-module')"
                                style="height:35px;margin-top:5px;"
                                @click="store.addUser()"/>
                    </div>
                </div>

                <!--                Bulk action -->
                <div class="p-1 pl-2 flex flex-wrap col-12"
                     v-if="store.user_details  && store.user_details.length > 0">
                    <div class="col-10">
                        <!--selected_menu-->
                        <Button
                            type="button"
                            @click="toggleSelectedMenuState"
                            data-testid="products-actions-menu"
                            aria-haspopup="true"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            aria-controls="overlay_menu">
                            <i class="pi pi-angle-down"></i>
                            <Badge v-if="store.action.items.length > 0"
                                   :value="store.action.items.length" />
                        </Button>
                        <Menu ref="selected_menu_state"
                              :model="store.user_selected_menu"
                              :popup="true" />
                        <!--/selected_menu-->
                    </div>

                </div>

                <!--                added vendor's roles list-->
                <div class="col-12"
                     v-if="store.user_details && store.user_details.length > 0">
                    <table class="table col-12 table-scroll table-striped">
                        <thead>
                        <tr>
                            <th class="col-1">
                                <Checkbox v-model="store.select_all_user"
                                          :disabled="!store.assets.permissions.includes('can-update-module')"
                                          :binary="true" @click="store.selectAllUser()" />
                            </th>
                            <th scope="col">User Name</th>
                            <th scope="col">Roles</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody id="scroll-horizontal" class="pt-1">
                        <tr v-for="(item, index) in store.user_details">
                            <th class="col-1">
                                <Checkbox v-model="item['is_selected']" :binary="true" />
                            </th>
                            <td>
                                <InputText v-model="item['user']['name']" style="height:35px;" class="w-full" disabled="" />
                            </td>
                            <td>
                                <Dropdown v-model="item['roles']"
                                          :options="store.vendor_roles"
                                          optionLabel="name"
                                          placeholder="Select a role"
                                          class="w-full"
                                          style="height:35px;"/>
                            </td>
                            <td>
                                <Button label="Remove"
                                        class="p-button-sm"
                                        size="small"
                                        style="height:35px;"
                                        :disabled="!store.assets.permissions.includes('can-update-module')"
                                        @click="store.removeUser(item)" />
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>

            </div>
        </Panel>

    </div>

</template>

<style scoped>
.btn-danger{
    background-color: red !important;
}
</style>

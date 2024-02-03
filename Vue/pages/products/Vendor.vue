<script setup>
import {onMounted, ref, watch} from "vue";
import { useProductStore } from '../../stores/store-products'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useProductStore();
const route = useRoute();

onMounted(async () => {
    if(route.params && route.params.id)
    {
        // store.item = store.
        await store.getItem(route.params.id);

    }

    await store.watchItem();
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
                        <b>Add Vendor</b>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">
                    <Button label="Save"
                            v-if="store.item && store.item.id"
                            class="p-button-sm"
                            data-testid="products-save"
                            @click="store.saveVendor()"
                            icon="pi pi-save"/>

                    <Button data-testid="products-document" icon="pi pi-info-circle"
                            class="p-button-sm"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <Button class="p-button-sm"
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

<!--                dropdown to select vendor -->
                <div class="flex flex-wrap gap-3 pb-2 p-1">
                    <div class="col-10">
<!--                        <Dropdown v-model="store.selected_vendor"-->
<!--                                  :options="store.active_vendors"-->
<!--                                  optionLabel="name"-->
<!--                                  placeholder="Select a Vendor"-->
<!--                                  class="w-full"-->
<!--                                  style="height:40px;">-->
<!--                        </Dropdown>-->

                        <AutoComplete
                            name="products-vendor-search"
                            data-testid="products-vendor-search"
                            v-model="store.selected_vendor"
                            option-label = "name"
                            :complete-on-focus = "true"
                            :suggestions="store.vendor_suggestion"
                            @complete="store.searchProductVendor($event)"
                            placeholder="Search Vendor"
                            class="w-full"
                            style="height:40px;">
                        </AutoComplete>

                    </div>

                    <div class="p-2">
                        <Button v-if="store.selected_vendor"
                                type="button" label="Add"
                                @click="store.addVendor()"
                                style="height:40px;width:50px;" />
                    </div>
                </div>

                <!--                Bulk action -->
                <div class="p-1 pl-2 flex flex-wrap col-12"
                     v-if="store.item.vendors  && store.item.vendors.length > 0">
                    <div class="col-10">
                        <!--selected_menu-->
                        <Button
                            type="button"
                            @click="toggleSelectedMenuState"
                            data-testid="products-actions-menu"
                            aria-haspopup="true"
                            aria-controls="overlay_menu">
                            <i class="pi pi-angle-down"></i>
                        </Button>
                        <Menu ref="selected_menu_state"
                              :model="store.vendor_selected_menu"
                              :popup="true" />
                        <!--/selected_menu-->
                    </div>
                </div>

                <!--added vendor's list-->
                <div class="col-12"
                     v-if="store.item.vendors && store.item.vendors.length > 0">
                    <table class="table col-12 table-scroll table-striped">
                        <thead>
                        <tr>
                            <th class="col-1">
                                <Checkbox v-model="store.select_all_vendor"
                                          :binary="true" @click="store.selectAllVendor()" />
                            </th>
                            <th scope="col">Vendor name</th>
                            <th scope="col">Can update</th>
                            <th scope="col">Status*</th>
                            <th scope="col">Status notes</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody id="scroll-horizontal" class="pt-1">
                        <tr v-for="(item, index) in store.item.vendors">
                            <th class="col-1"><Checkbox v-model="item['is_selected']" :binary="true" /></th>
                            <td>
                                <InputText v-model="item['vendor']['name']"
                                           class="w-full"
                                           style="height:35px;"
                                           disabled/>
                            </td>
                            <td >
                                <InputSwitch v-model="item['can_update']" style="margin-left:10px;height:30px;"/>
                            </td>
                            <td>
                                <Dropdown v-model="item['status']"
                                          option-label="name"
                                          placeholder="Select a status"
                                          class="w-full"
                                          disabled
                                          :options="store.product_vendor_status"
                                          style="height:35px;"
                                />
                            </td>
                            <td>
                                <InputText v-model="item['status_notes']"
                                           class="w-full"
                                            style="height:35px;"/>
                            </td>
                            <td>
                                <Button label="Remove"
                                        size="small"
                                        style="height:35px;margin-left:10px;"
                                        @click="store.removeVendor(item)" />
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

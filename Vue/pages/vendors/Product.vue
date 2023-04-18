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
                        <b>Add Product</b>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">
                    <Button label="Save"
                            v-if="store.item && store.item.id"
                            data-testid="products-save"
                            @click="store.itemAction('save-product')"
                            icon="pi pi-save"/>

                    <Button data-testid="products-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>

                    <Button class="p-button-primary"
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
                        <Dropdown v-model="store.selected_product"
                                  :options="store.active_products"
                                  optionLabel="name"
                                  placeholder="Select a Product"
                                  class="w-full">
                            <template #optiongroup="slotProps">
                                <div class="flex align-items-center">
                                    <div>{{ slotProps.option }} <span v-if="slotProps.option.is_default == 1">(Default)</span></div>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <div class="p-1">
                        <Button type="button" label="Add" @click="store.addProduct()" />
                    </div>
                </div>

                <!--                Bulk action -->
                <div class="p-1 pl-2 flex flex-wrap col-12"
                     v-if="store.item.products  && store.item.products.length > 0">
                    <div class="col-10">
                        <!--selected_menu-->
                        <Button
                            type="button"
                            @click="toggleSelectedMenuState"
                            data-testid="products-actions-menu"
                            aria-haspopup="true"
                            aria-controls="overlay_menu">
                            <i class="pi pi-angle-down"></i>
                            <Badge v-if="store.action.items.length > 0"
                                   :value="store.action.items.length" />
                        </Button>
                        <Menu ref="selected_menu_state"
                              :model="store.product_selected_menu"
                              :popup="true" />
                        <!--/selected_menu-->
                    </div>
                    <div class="pr-1">
                        <Button label="Remove All" @click="store.bulkRemoveProduct(true)" class="btn-danger" size="small" />
                    </div>
                </div>

                <!--                added vendor's list-->
                <div class="col-12"
                     v-if="store.item.products && store.item.products.length > 0">
                    <table class="table col-12 table-scroll table-striped">
                        <thead>
                        <tr>
                            <th class="col-1">
                                <Checkbox v-model="store.select_all_product"
                                          :binary="true" @click="store.selectAllProduct()" />
                            </th>
                            <th scope="col">Product name</th>
                            <th scope="col">Can update</th>
                            <th scope="col">Status</th>
                            <th scope="col">Status notes</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody id="scroll-horizontal" class="pt-1">
                        <tr v-for="(item, index) in store.item.products">
                            <th class="col-1"><Checkbox v-model="item['is_selected']" :binary="true" /></th>
                            <td>
                                <InputText v-model="item['product']['name']" class="w-full" />
                            </td>
                            <td>
                                <InputSwitch v-model="item['can_update']" />
                            </td>
                            <td>
                                <Dropdown v-model="item['status']"
                                          :options="store.product_vendor_status"
                                          optionLabel="name"
                                          placeholder="Select a status"
                                          class="w-full" />
                            </td>
                            <td>
                                <InputText v-model="item['status_notes']" class="w-full" />
                            </td>
                            <td>
                                <Button label="Remove"
                                        class="btn-danger"
                                        size="small"
                                        @click="store.removeProduct(item)" />
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

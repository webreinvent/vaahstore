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
        // store.item = store.
        await store.getItem(route.params.id);

    }

    await store.watchItem();
    await store.watchProducts();
});

//--------selected_menu_state
const selected_menu_state = ref();
const toggleSelectedMenuState = (event) => {
    selected_menu_state.value.toggle(event);
};
//--------/selected_menu_state


</script>
<template>

    <div class="col-6" >

        <Panel >

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <b>Products</b>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">
                    <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                            label="Save"
                            v-if="store.item && store.item.id"
                            class="p-button-sm"
                            data-testid="products-save"
                            @click="store.itemAction('save')"
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
                <div class="flex flex-wrap gap-4 pb-2 p-1">
                    <div class="col-6">
                        <AutoComplete
                            name="addresses-user-filter"
                            data-testid="addresses-user-filter"
                            v-model="store.selected_product"
                            option-label = "name"
                            :complete-on-focus = "true"
                            :suggestions="store.product_suggestion"
                            @complete="store.searchProduct($event)"
                            placeholder="Search Product"
                            class="w-full"
                            style="height:35px;"
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
                            }">
                        </AutoComplete>
                    </div>

                    <div class="p-2">
                        <Button v-if="store.selected_product"
                                :disabled="!store.assets.permissions.includes('can-update-module')"
                                type="button"
                                label="Add"
                                style="height:35px;"
                                @click="store.addProduct()" />
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
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="products-actions-menu"
                            aria-haspopup="true"
                            aria-controls="overlay_menu">
                            <i class="pi pi-angle-down"></i>
                        </Button>
                        <Menu ref="selected_menu_state"
                              :model="store.product_selected_menu"
                              :popup="true" />
                        <!--/selected_menu-->
                    </div>
                </div>

                <!--added vendor's list-->
                <div class="col-12"
                     v-if="store.item.products && store.item.products.length > 0">
                    <table class="table col-12 table-scroll table-striped">
                        <thead>
                        <tr>
                            <th class="col-1">
                                <Checkbox v-model="store.select_all_product"
                                          :binary="true" @click="store.selectAllProduct()" />
                            </th>
                            <th scope="col">Product Name</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody id="scroll-horizontal" class="pt-1">
                        <tr v-for="(item, index) in store.item.products">
                            <th class="col-1"><Checkbox v-model="item['is_selected']" :binary="true" /></th>
                            <td >
                                <InputText v-model="item['product']['name']" class="w-full" style="height:30px;" disabled/>
                            </td>
                            <td style="display:flex;justify-content:center;">
                                <Button label="Remove"
                                        :disabled="!store.assets.permissions.includes('can-update-module')"
                                        style="height:30px;"
                                        size="medium"
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


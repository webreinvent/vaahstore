<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';
import { useRootStore } from '@/stores/root'

import { useProductVariationStore } from '../../stores/store-productvariations'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useProductVariationStore();
const root = useRootStore();
const route = useRoute();

onMounted(async () => {

    /**
     * If record id is not set in url then
     * redirect user to list view
     */
    if(route.params && !route.params.id)
    {
        store.toList();
        return false;
    }

    /**
     * Fetch the record from the database
     */
    if(!store.item || Object.keys(store.item).length < 1)
    {
        await store.getItem(route.params.id);
    }

    /**
     * Watch if url record id is changed, if changed
     * then fetch the new records from database
     */


});

//--------toggle item menu
const item_menu_state = ref();
const toggleItemMenu = (event) => {
    item_menu_state.value.toggle(event);
};
//--------/toggle item menu

const permissions=store.assets.permissions;


</script>
<template>

    <Panel :pt="root.panel_pt" v-if="store && store.item">

            <template class="p-1" #header>

                <div class="flex flex-row">

                    <div class="p-panel-title">
                        #{{store.item.id}}
                    </div>

                </div>

            </template>

            <template #icons>


                <div class="p-inputgroup">
                    <Button label="Edit"
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="productvariations-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="productvariations-item-menu"
                        icon="pi pi-angle-down"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="productvariations-item-to-list"
                            @click="store.toList()"/>

                </div>



            </template>


            <div class="mt-2" v-if="store.item">

                <Message severity="info" :closable="false" v-if="store.item.status_notes">
                    <div style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                        <pre v-html="store.item.status_notes"></pre>
                    </div>
                </Message>


                <Message severity="error"
                         class="p-container-message"
                         :closable="false"
                         icon="pi pi-trash"
                         v-if="store.item.deleted_at">

                    <div class="flex align-items-center justify-content-between">

                        <div class="">
                            Deleted {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="productvariations-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by' || column === 'updated_by' || column === 'deleted_by' || column === 'description'
                        || column === 'status'|| column === 'product' || column === 'status_notes' || column === 'meta' || column === 'quantity' || column === 'sku'
                        || column === 'price' || column === 'has_media' || column === 'taxonomy_id_variation_status' || column === 'is_default'
                        || column === 'is_active' || column === 'meta_keywords' || column === 'meta_description' || column === 'meta_title'  || column === 'is_mail_sent'
                        || column === 'is_quantity_low'
">
                        </template>

                        <template v-else-if="column === 'id' || column === 'uuid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       :can_copy="true"
                            />
                        </template>

                        <template v-else-if="column === 'name'">
                            <tr>
                                <td><b>Name</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="word-break: break-word;">
                                        {{store.item.name}}</div>
                                </td>
                            </tr>
                        </template>
                        <template v-else-if="column === 'slug'">
                            <tr>
                                <td><b>Slug</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="word-break: break-word;">
                                        {{store.item.slug}}</div>
                                </td>
                            </tr>
                            <tr>
                                <td><b>SKU</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="word-break: break-word;">
                                        {{store.item.sku}}</div>
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'  || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="user"
                            />
                        </template>

                        <template v-else-if="column === 'is_active'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'in_stock'">
                            <tr>
                                <td><b>Quantity</b></td>
                                <td  colspan="2" >
                                    <badge>{{store.item.quantity}}</badge>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Price</b></td>
                                <td  colspan="2">
                                    <badge v-if="store.item.price >= 1"><span v-html="store.item.product?.store.default_currency.symbol"></span>{{store.item.price}}</badge>

                                </td>
                            </tr>

                            <tr>
                                <td><b>Low Stock Alert Notification</b></td>
                                <td colspan="2">
                                    <Badge v-if="store.item.is_mail_sent === 1" severity="success">
                                        Yes
                                    </Badge>
                                    <Badge v-else severity="success">
                                        No
                                    </Badge>
                                </td>
                            </tr>


                            <VhViewRow label="Stock Status"
                                       :value="value"
                                       type="quantity"
                            />
                            <VhViewRow label="Status"
                                       :value="store.item.status"
                                       type="status"
                            />
                            <VhViewRow label="Description"
                                       :value="store.item.description"
                                       type="description"

                            />
                            <tr>
                                <td><b>Is Default</b></td>
                                <td colspan="2">
                                    <Badge value="Yes" v-if="store.item.is_default===1 || value=='yes'" severity="success"></Badge>
                                    <Badge v-else value="No" severity="danger"></Badge>
                                </td>
                            </tr>

                            <tr>
                                <td><b>Meta</b></td>
                                <td>
                                    <Button icon="pi pi-eye"
                                            label="view"
                                            class="p-button-outlined p-button-secondary p-button-rounded p-button-sm"
                                            @click="store.openMetaModal()"
                                            :disabled="!store.item.meta_title &&
                                            !store.item.meta_description &&
                                            !(store.item && store.item.meta_keywords && Array.isArray(store.item.meta_keywords) &&
                                             store.item.meta_keywords.length > 0)"

                                            data-testid="meta-open_modal"
                                    />
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'has_media'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>




                        <template v-else-if="column === 'vh_st_product_id'">
                            <VhViewRow label="Product"
                                       :value="store.item.product"
                                       type="user"
                            />
                        </template>

                        <template v-else-if="column === 'is_active'">
                            <VhViewRow label="Active"
                                       :value="store.item.is_active"
                                       type="user"
                            />
                        </template>

                        <template v-else>
                            <VhViewRow :label="column"
                                       :value="value"
                                       />
                        </template>


                    </template>
                    </tbody>

                </table>

                </div>
            </div>
        </Panel>

    <Dialog header="Meta Fields"
            v-model:visible="store.meta_dialog"
            :breakpoints="{'960px': '75vw', '640px': '90vw'}"
            :style="{width: '50vw'}" :modal="true"
    >
        <div class="mb-4 flex"><span class="font-bold mr-2">Meta Title: </span><p>{{store.item.meta_title}}</p></div>
        <div class="mb-4 flex">
            <span class="font-bold mr-2" style="margin-top: 0.8rem;">Meta Description:</span>
            <pre style="font-family: Inter,ui-sans-serif">{{store.item.meta_description}}</pre>
        </div>
        <div class="flex"><span class="font-bold mr-2">Meta Keyword: </span> <p>{{store.item.meta_keywords}}</p></div>
    </Dialog>

</template>


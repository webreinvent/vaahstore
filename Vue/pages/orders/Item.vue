<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useOrderStore } from '../../stores/store-orders'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useOrderStore();
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
    /*watch(route, async (newVal,oldVal) =>
        {
            if(newVal.params && !newVal.params.id
                && newVal.name === 'articles.view')
            {
                store.toList();

            }
            await store.getItem(route.params.id);
        }, { deep: true }
    )*/

});

//--------toggle item menu
const item_menu_state = ref();
const toggleItemMenu = (event) => {
    item_menu_state.value.toggle(event);
};
//--------/toggle item menu

</script>
<template>

    <div class="col-6" >

        <Panel class="is-small" v-if="store && store.item">

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
                            data-testid="orders-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="orders-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="orders-item-to-list"
                            @click="store.toList()"/>

                </div>



            </template>


            <div class="mt-2" v-if="store.item">

                <Message severity="info" :closable="false" v-if="store.item.status_notes">
                    <tr>
                        <td  colspan="2" >
                            <div  style="width:300px;word-break: break-word;">
                                {{store.item.status_notes}}</div>
                        </td>
                    </tr>
                </Message>

                <Message severity="error"
                         class="p-container-message"
                         :closable="false"
                         icon="pi pi-trash"
                         v-if="store.item.deleted_at">

                    <div class="flex align-items-center justify-content-between">

                        <div class="">
                            Trashed {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="orders-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by' || column === 'vh_st_product_id'||
                            column === 'taxonomy_id_order_items_status' || column === 'updated_by'||
                            column === 'vh_st_product_variation_id'|| column === 'vh_st_vendor_id' ||
                            column === 'order_item'|| column === 'taxonomy_id_order_items_types'||
                            column === 'vh_st_customer_group_id' || column === 'user'|| column === 'payment_method'||
                            column === 'status'|| column === 'status_order'|| column === 'items'||
                            column === 'is_active_order_item' || column == 'is_invoice_available'
                            || column == 'meta' || column == 'deleted_by' || column == 'status_notes'
                             || column == 'slug' || column == 'status_notes_order_item'">
                        </template>

                        <template v-else-if="column === 'id' || column === 'uuid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       :can_copy="true"
                            />
                        </template>

                        <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'
                         || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="user"
                            />
                        </template>

                        <template v-else-if="column === 'types'">
                            <tr>
                                <td><b>Types</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.types.name}}</div>
                                </td>
                            </tr>
                        </template>
                        <template v-else-if="column === 'product'">
                            <tr>
                                <td><b>Product</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.product.name}}</div>
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'product_variation'">
                            <tr>
                                <td><b>Product Variation</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.product_variation.name}}</div>
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'vendor'">
                            <tr>
                                <td><b>Vendor</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.vendor.name}}</div>
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'customer_group'">
                            <tr>
                                <td><b>Customer Group</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.customer_group.name}}</div>
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'customer_group'">
                            <tr>
                                <td><b>Customer Group</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.customer_group.name}}</div>
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'status_order_items'">
                            <tr>
                                <td><b>Order Status</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.status_order_items.name}}</div>
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'status_notes_order_item'">
                            <tr>
                                <td><b>Order Status</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.status_order_items.name}}</div>
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'taxonomy_id_order_status'">
                            <VhViewRow label="Status"
                                       :value="store.item.status"
                                       type="status"
                            />
                        </template>

                        <template v-else-if="column === 'vh_st_payment_method_id'">
                            <VhViewRow label="Payment Method"
                                       :value="store.item.payment_method.name"
                            />
                        </template>

                        <template v-else-if="column === 'is_paid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'vh_user_id'">
                            <VhViewRow label="User"
                                       :value="store.item.user.first_name"
                            />
                        </template>

                        <template v-else-if="column === 'is_active'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
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

    </div>

</template>

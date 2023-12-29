<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useProductVariationStore } from '../../stores/store-productvariations'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useProductVariationStore();
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
                            data-testid="productvariations-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="productvariations-item-menu"
                        icon="pi pi-angle-down"
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
                    <tr>
                        <td  colspan="2" >
                            <div  style="width:300px;word-break: break-word;">
                                <pre>{{store.item.status_notes}}</pre></div>
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
                        || column === 'per_unit_price' || column === 'has_media' || column === 'taxonomy_id_variation_status' || column === 'is_default'
                        || column === 'is_active'
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
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.name}}</div>
                                </td>
                            </tr>
                        </template>
                        <template v-else-if="column === 'slug'">
                            <tr>
                                <td><b>Slug</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.slug}}</div>
                                </td>
                            </tr>
                            <tr>
                                <td><b>SKU</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.sku}}</div>
                                </td>
                            </tr>
<!--                            <tr>
                                <td><b>Description</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        <pre>{{store.item.description}}</pre>
                                    </div>
                                </td>
                            </tr>-->
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
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                            <VhViewRow label="Status"
                                       :value="store.item.status"
                                       type="status"
                            />
                            <VhViewRow label="Description"
                                       :value="store.item.description"

                            />
                        </template>

                        <template v-else-if="column === 'has_media'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'is_default'">
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

<!--                        <template v-else-if="column === 'taxonomy_id_variation_status'">
                            <VhViewRow label="Status"
                                       :value="store.item.status"
                                       type="status"
                            />
                        </template>-->

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


<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useProductMediaStore } from '../../stores/store-productmedias'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useProductMediaStore();
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
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="productmedias-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        data-testid="productmedias-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="productmedias-item-to-list"
                            @click="store.toList()"/>

                </div>

            </template>
            <div class="mt-2" v-if="store.item">

                <Message severity="info" :closable="false" v-if="store.item.status_notes">
                    <div style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                        {{store.item.status_notes}}</div>
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
                                    data-testid="productmedias-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by' ||column === 'product_variation' || column === 'status_notes'
                        || column === 'updated_by'|| column === 'product' ||column === 'name'|| column === 'slug' ||
                        column === 'path'|| column === 'mime_type' ||column === 'url_thumbnail'|| column === 'thumbnail_size'
                        || column === 'base_path'|| column === 'images'||column === 'url'|| column === 'status'||
                        column === 'size'||column === 'meta'|| column === 'type'|| column === 'extension'||
                        column === 'product_media_images' || column === 'deleted_by' || column==='product_variation_media'|| column==='listed_variation' || column==='taxonomy_id_product_media_status' ">
                        </template>

                        <template v-else-if="column === 'id' || column === 'uuid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       :can_copy="true"
                            />
                        </template>

                        <template v-else-if="column === 'vh_st_product_id'">
                            <tr>
                                <td>
                                    <b>Product</b>
                                </td>
                                <td colspan="2" >
                                    {{store.item.product?.name}}
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'vh_st_product_variation_id'">

                            <tr>
                                <td>
                                    <b>Product Variations</b>
                                </td>
                                <td colspan="2">
                                    <Tag style="border-radius:20px;padding:5px 10px; margin-right: 10px; margin-bottom: 10px;" v-for="product in store.item.product_variation_media" :key="product.id">
                                        {{ product.name }}
                                    </Tag>

                                </td>
                            </tr>
                        </template>

                        <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'
                        || column === 'deleted_by_user'|| column === 'product_media_images' ) && (typeof value === 'object' && value !== null)">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="user"
                            />
                        </template>

                        <template v-else-if="column === 'is_active'">


                                <tr>
                                    <td>
                                        <b>Product Media</b>
                                    </td>
                                    <td colspan="2" >
                                        <Image v-if="store.item.images && store.item.images.length > 0"
                                               class="pt-2"
                                               v-for="(item) in store.item.images"
                                               :src="store.item.base_path+'/'+item['url']"
                                               preview
                                               alt="Image"
                                               width="150"
                                               v-tooltip.top="store.item.name"/>
                                    </td>
                                </tr>
                            <tr>
                                <td>
                                    <b>Media Type</b>
                                </td>
                                <td colspan="2" >
                                    {{store.item.type}}
                                </td>
                            </tr>

                            <tr >
                                <td>
                                    <b>Status</b>
                                </td>
                                <td colspan="2">
                                    <span v-if="store.item.status?.name === 'Approved'" class="p-badge p-component p-badge-success" data-pc-name="badge" data-pc-section="root">{{store.item.status?.name}}</span>
                                    <span v-else-if="store.item.status?.name === 'Pending'" class="p-badge p-component" data-pc-name="badge" data-pc-section="root">{{store.item.status?.name}}</span>
                                    <span v-else class="p-badge p-component p-badge-danger" data-pc-name="badge" data-pc-section="root">{{store.item.status?.name}}</span>
                                </td>
                            </tr>
                            <VhViewRow label="Is Active"
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

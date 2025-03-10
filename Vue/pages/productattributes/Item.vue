<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useProductAttributeStore } from '../../stores/store-productattributes'
import { useRootStore } from '@/stores/root'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useProductAttributeStore();
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
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            data-testid="productattributes-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="productattributes-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="productattributes-item-to-list"
                            @click="store.toList()"/>

                </div>



            </template>


            <div class="mt-2" v-if="store.item">

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
                                    data-testid="productattributes-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by' || column === 'updated_by' || column === 'deleted_by' ||
                        column === 'product_variation' || column === 'attribute' || column === 'meta' || column === 'attribute_values'">
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

                        <template v-else-if="column === 'is_active'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'vh_st_product_variation_id'">
                            <tr>
                                <td><b>Product Variation</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.product_variation.name}}</div>
                                </td>
                            </tr>
                        </template>


                        <template v-else-if="column === 'vh_st_attribute_id'">
                            <tr>
                                <td><b>Attribute</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        <Tag>{{store.item.attribute.name}}</Tag></div>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Attribute Values</b></td>
                                <td colspan="2">
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        <template v-for="value in store.item.attribute_values">
                                            <Tag class="mr-0.2 rounded-sm" severity="info">{{value.new_value}}</Tag>
                                        </template>
                                    </div>
                                </td>
                            </tr>
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

</template>

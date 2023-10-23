<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useAttributeStore } from '../../stores/store-attributes'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useAttributeStore();
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
                            data-testid="attributes-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="attributes-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Button
                        type="button"
                        data-testid="attributes-form-menu"
                        icon="pi pi-angle-left"
                        @click="store.getPreviousItem(store.item)"
                        aria-haspopup="true"/>

                    <Button
                        type="button"
                        data-testid="attributes-form-menu"
                        icon="pi pi-angle-right"
                        @click="store.getNextItem(store.item)"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="attributes-item-to-list"
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
                                    data-testid="attributes-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by' || column === 'updated_by' || column === 'product_variation'
                         || column === 'attribute_value' || column === 'status' || column === 'meta' || column === 'deleted_by'
                            || column === 'deleted_at' || column === 'value'">
                        </template>

                        <template v-else-if="column === 'id' || column === 'uuid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       :can_copy="true"
                            />
                        </template>

                        <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'  ||
                        column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="user"
                            />
                        </template>

                        <template v-else-if="column === 'name'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Name</b>
                                </td>
                                <td colspan="2" >
                                    <div style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.name}}</div>
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'slug'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Slug</b>
                                </td>
                                <td colspan="2" >
                                    <div style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.slug}}</div>
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'type'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Type</b>
                                </td>
                                <td colspan="2" >
                                    <div style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.type}}</div>
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'is_active'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                            <VhViewRow label="value"
                                       :value="store.item.value"
                                       type="multipleValues"
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

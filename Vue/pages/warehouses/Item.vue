<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useWarehouseStore } from '../../stores/store-warehouses'
import { useRootStore } from '@/stores/root'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useWarehouseStore();
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
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            data-testid="warehouses-item-to-edit"
                            icon="pi pi-save"
                            :disabled="store.assets.is_guest_impersonating"
                    />

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="warehouses-item-menu"
                        icon="pi pi-angle-down"
                        :disabled="store.assets.is_guest_impersonating"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="warehouses-item-to-list"
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
                            Trashed {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="warehouses-item-restore"
                                    :disabled="store.assets.is_guest_impersonating"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <Message severity="info" :closable="false" v-if="store.item.status_notes">
                    <div>
                        <pre style="font-family: Inter, ui-sans-serif, system-ui; width:350px;overflow-wrap: break-word;word-wrap:break-word;" v-html="store.item.status_notes"></pre>
                    </div>
                </Message>


                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by' || column === 'updated_by' || column === 'status' || column === 'status_notes' ||
                                    column === 'vendor' || column === 'meta' || column === 'deleted_by'|| column === 'city'|| column === 'address_1'|| column === 'address_2'|| column === 'postal_code'
                                    || column === 'state'|| column === 'country'|| column === 'taxonomy_id_warehouse_status'|| column === 'vh_st_vendor_id'">
                        </template>

                        <template v-else-if="column === 'id' || column === 'uuid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       :can_copy="true"
                            />
                        </template>

                        <template v-else-if="column === 'name'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Name</b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.name}}</div>
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'slug'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Slug</b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.slug}}</div>
                                </td>
                            </tr>
                            <VhViewRow label="Vendor"
                                       :value="store.item.vendor"
                                       type="vendor"
                            />
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Country</b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.country}}</div>
                                </td>
                            </tr>

                            <tr>
                                <td :style="{width: label_width}">
                                    <b>State</b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.state}}</div>
                                </td>
                            </tr>
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>City </b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.city}}</div>
                                </td>
                            </tr>
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Address 1</b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.address_1}}</div>
                                </td>
                            </tr><tr>
                                <td :style="{width: label_width}">
                                    <b>Address 2</b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.address_2}}</div>
                                </td>
                            </tr>
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Postal Code</b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.postal_code}}</div>
                                </td>
                            </tr>


                        </template>


                        <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'
                        || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="user"
                            />
                        </template>



                        <template v-else-if="column === 'is_active'">
                            <VhViewRow label="Status"
                                       :value="store.item.status"
                                       type="status"
                            />
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

</template>



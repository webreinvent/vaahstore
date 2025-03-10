<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useAddressStore } from '../../stores/store-addresses'
import { useRootStore } from '@/stores/root'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useAddressStore();
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
            <Message severity="info" :closable="false" v-if="store.item.status_notes">
                <pre style="word-break:break-word;overflow-wrap:break-word;word-wrap:break-word;white-space:pre-wrap;">{{store.item.status_notes}}</pre>

            </Message>
            <template class="p-1" #header>

                <div class="flex flex-row">

                    <div class="p-panel-title">
                        #{{store.item.id}}
                    </div>

                </div>

            </template>

            <template #icons>


                <div class="p-inputgroup">
                    <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                            label="Edit"
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            data-testid="addresses-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="addresses-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="addresses-item-to-list"
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
                                    data-testid="addresses-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by' || column === 'updated_by'|| column === 'user'||
                        column === 'status'|| column === 'address_type' || column === 'address_line_1' || column === 'address_line_2' ||
                        column === 'meta' || column === 'status_notes' || column === 'deleted_by' || column === 'address'">
                        </template>
                        <template v-else-if="column === 'id' || column === 'uuid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       :can_copy="true"
                            />
                        </template>

                        <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'  || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="user"
                            />
                        </template>

                            <template v-else-if="column === 'vh_user_id'">
                                <VhViewRow label="User"
                                           :value="store.item.user"
                                           type="user"
                                />
                                <tr>
                                    <td :style="{width: label_width}">
                                        <b>Address</b>
                                    </td>
                                    <td  colspan="2" >
                                        <div class="word-overflow" style="overflow-wrap: break-word;word-wrap:break-word;">
                                            {{store.item.address}}</div>
                                    </td>
                                </tr>
                            </template>

                            <template v-else-if="column === 'taxonomy_id_address_types'">
                                <VhViewRow label="Type"
                                           :value="store.item.address_type"
                                           type="user"
                                />
                            </template>

                            <template v-else-if="column === 'taxonomy_id_address_status'">
                                <VhViewRow label="Status"
                                           :value="store.item.status"
                                           type="status"
                                />
                            </template>


                        <template v-else-if="column === 'is_default'">
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

</template>

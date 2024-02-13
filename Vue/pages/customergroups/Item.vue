<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useCustomerGroupStore } from '../../stores/store-customergroups'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useCustomerGroupStore();
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
                            data-testid="customergroups-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        data-testid="customergroups-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="customergroups-item-to-list"
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
                                    data-testid="customergroups-item-restore"
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

                            <template v-if="column === 'created_by' || column === 'updated_by' ||
                             column === 'deleted_by' ||  column === 'status'||column === 'customer_count'
                             || column === 'order_count' ||  column === 'taxonomy_id_customer_groups_status'||   column === 'status_notes' || column === 'meta' || column === 'customers'|| column === 'order_items'">
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
                                    <td colspan="2" >
                                        <div style="overflow-wrap: break-word;word-wrap:break-word;">
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
                                        <div style="overflow-wrap: break-word;word-wrap:break-word;">
                                            {{store.item.slug}}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td :style="{width: label_width}">
                                        <b>Customer Count</b>
                                    </td>
                                    <td  colspan="2" >
                                        <Badge severity="primary" v-if="store.item.customers">{{store.item.customers.length}}</Badge>
                                    </td>
                                </tr>

                                <tr>
                                    <td :style="{width: label_width}">
                                        <b>Order Count</b>
                                    </td>
                                    <td  colspan="2" >
                                        <Badge severity="primary" v-if="store.item.order_items">{{store.item.order_items.length}}</Badge>
                                    </td>
                                </tr>

                                <tr >
                                    <td>
                                        <b>Status</b>
                                    </td>
                                    <td colspan="2">
                                        <span v-if="store.item.status?.name === 'Approved'" class="p-badge p-component p-badge-success" data-pc-name="badge" data-pc-section="root">{{store.item.status?.name}}</span>
                                        <span v-else-if="store.item.status?.name === 'Pending'" class="p-badge p-component p-badge-warning" data-pc-name="badge" data-pc-section="root">{{store.item.status?.name}}</span>
                                        <span v-else class="p-badge p-component p-badge-danger" data-pc-name="badge" data-pc-section="root">{{store.item.status?.name}}</span>
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

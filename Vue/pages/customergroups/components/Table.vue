<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useCustomerGroupStore } from '../../../stores/store-customergroups'

const store = useCustomerGroupStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list">
        <!--table-->
        <DataTable :value="store.list.data"
                   dataKey="id"
                   :rowClass="(rowData) => rowData.id === store.item?.id ?'bg-yellow-100' : ''"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

            <Column field="name" header="Name"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <div style="word-break: break-word;">{{ prop.data.name }}</div>
                </template>

            </Column>

            <Column field="customer_count" header="Customer Count"
            >

                <template #body="prop">
                    <div class="p-inputgroup">
                        <span v-if="prop.data.customers && prop.data.customers.length"
                              class="p-inputgroup-addon cursor-pointer"  @click="store.toViewCustomers(prop.data)" v-tooltip.top="'View Customers'">
                            <Badge severity="success">{{prop.data.customers.length}}</Badge>

                        </span>
                        <span class="p-inputgroup-addon" v-else>
                             <Badge severity="success">0</Badge>
                         </span>
                    </div>
                </template>

            </Column>

            <Column field="order_count" header="Order Count"
            >

                <template #body="prop">
                    <div class="p-inputgroup">
                        <span v-if="prop.data.order_items && prop.data.order_items.length"
                              class="p-inputgroup-addon">
                              <Badge severity="success">{{prop.data.order_items.length}}</Badge>
                        </span>
                        <span class="p-inputgroup-addon" v-else>
                             <Badge severity="success">0</Badge>
                         </span>
                    </div>
                </template>

            </Column>

            <Column field="status" header="Status"
                    v-if="store.isListView()">
                <template #body="prop">
                    <Badge v-if="prop.data && prop.data.status &&  prop.data.status.slug === 'approved'"
                           severity="success"> {{prop.data.status.name}} </Badge>
                    <Badge v-else-if="prop.data && prop.data.status &&  prop.data.status.slug === 'rejected'"
                           severity="danger"> {{prop.data.status.name}} </Badge>
                    <Badge v-else-if="prop.data && prop.data.status &&  prop.data.status.slug === 'pending'"
                           severity="warn"> {{prop.data.status.name}} </Badge>
                </template>
            </Column>

            <Column field="updated_at" header="Updated"
                    v-if="store.isListView()"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}
                </template>

            </Column>

            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="customergroups-table-to-view"
                                v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                            class="p-button-tiny p-button-text"
                                data-testid="customergroups-table-to-edit"
                                v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="customergroups-table-action-trash"
                                v-if="store.isListView() && !prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="customergroups-table-action-restore"
                                v-if="store.isListView() && prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />


                    </div>

                </template>


            </Column>
            <template #empty="prop">
                <div  style="text-align: center;font-size: 12px; color: #888;">No records found.</div>
            </template>


        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="(store.query.page-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>
<style scoped>

</style>

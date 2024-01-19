<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useAddressStore } from '../../../stores/store-addresses'
const store = useAddressStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list" style=" display: flex;flex-direction: column;justify-content: center; height: 100%;">
        <!--table-->
         <DataTable :value="store.list.data"
                       dataKey="id"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                    :rowClass="(rowData) => rowData.id == store.item.id ?'bg-yellow-100' : ''"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

             <Column field="user.first_name" header="User"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                         {{prop.data.user.first_name}}
                 </template>

             </Column>

             <Column field="address" header="Address"
                     :sortable="true">

                 <template #body="prop">

                     <template v-if="prop.data.is_default == 1">
                            <Badge severity="primary">Default</Badge>
                            <div style="word-break: break-word;">{{ prop.data.address }}</div>
                     </template>
                     <template v-else>
                         <div style="word-break: break-word;">{{ prop.data.address }}</div>
                     </template>
                 </template>

             </Column>

             <Column field="status" header="Status">

                 <template #body="prop">

                     <Badge v-if="prop.data.status.slug == 'approved'"
                            severity="success"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="prop.data.status.slug == 'rejected'"
                            severity="danger"> {{prop.data.status.name}} </Badge>
                     <Badge v-else
                            severity="warning"> {{prop.data.status.name}} </Badge>
                 </template>

             </Column>

                <Column field="updated_at" header="Updated"
                        v-if="store.isViewLarge()"
                        style="width:150px;"
                        :sortable="true">

                    <template #body="prop">
                        {{useVaah.ago(prop.data.updated_at)}}
                    </template>

                </Column>

             <Column field="is_default" v-if="store.isViewLarge()"
                     style="width:100px;"
                     header="Is Default">

                 <template #body="prop">
                     <InputSwitch v-model.bool="prop.data.is_default"
                                  data-testid="addresses-table-is-active"
                                  v-bind:false-value="0"  v-bind:true-value="1"
                                  :disabled="!store.assets.permissions.includes('can-update-module')"
                                  class="p-inputswitch-sm"
                                  @input="store.toggleIsDefault(prop.data)"
                                    >
                     </InputSwitch>
                 </template>

             </Column>


            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="addresses-table-to-view"
                                v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item.id"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if="store.assets.permissions.includes('can-update-module')"
                                class="p-button-tiny p-button-text"
                                data-testid="addresses-table-to-edit"
                                v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item.id "
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button  v-if="store.isViewLarge() && !prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                class="p-button-tiny p-button-danger p-button-text"
                                data-testid="addresses-table-action-trash"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button v-if="store.isViewLarge() && prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                class="p-button-tiny p-button-success p-button-text"
                                data-testid="addresses-table-action-restore"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />

                    </div>

                </template>


            </Column>

             <template #empty="prop">

                 <div class="no-record-message" style="text-align: center;font-size: 12px; color: #888;">No records found.</div>

             </template>

        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"

                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>

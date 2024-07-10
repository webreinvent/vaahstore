<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { usePaymentStore } from '../../../stores/store-payments'

const store = usePaymentStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list">
        <!--table-->
         <DataTable :value="store.list.data"
                   dataKey="id"
                   :rowClass="store.setRowClass"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :nullSortOrder="-1"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

             <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true">
             </Column>

             <Column field="transaction_id"
                     header="Transaction Id"
                     v-if="store.isViewLarge()"
                     :sortable="true">

             </Column>
             <Column field="amount" header="Amount" :sortable="true" :style="{width: '80px'}">
                 <template #body="prop">
                     <div class="justify-content-end flex">
                         {{prop.data.amount}}
                     </div>
                 </template>
             </Column>



             <Column  header="Payment Method" style="width: 150px"
                      :sortable="true">

                 <template #body="prop">
                     <div class="justify-content-center flex">
                     <span >
                         {{prop.data.payment_method?.name}}
                     </span>
                     </div>
                 </template>

             </Column>
             <Column header="Orders Count"
                     class="overflow-wrap-anywhere justify-content-center flex"
                     :sortable="true">

                 <template #body="prop">
<!--                     <div class="justify-content-center flex">-->
                         <Tag severity="info" class="p-inputgroup-addon cursor-pointer" @click="store.toView(prop.data)" >{{prop.data.orders_count}}</Tag>
<!--                     </div>-->
                 </template>

             </Column>

             <Column  header="Payment Status"  v-if="store.isViewLarge()"  class="overflow-wrap-anywhere "
                      :sortable="true">
                 <template #body="prop">

                     <template v-if="prop.data.status">
                         <Badge v-if="prop.data.status.slug == 'success'" severity="success">
                             {{prop.data.status.name}}
                         </Badge>
                         <Badge v-else severity="primary">
                             {{prop.data.status.name}}
                         </Badge>
                     </template>
                 </template>


             </Column>

             <Column field="created_at" header="Created"
                     v-if="store.isViewLarge()"
                     style="width:150px;"
                     :sortable="true">

                 <template #body="prop">
                     {{useVaah.toLocalTimeShortFormat(prop.data.created_at)}}
                 </template>

             </Column>
            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="payments-table-to-view"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="payments-table-to-edit"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="payments-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="payments-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />


                    </div>

                </template>


            </Column>

             <template #empty>
                 <div class="text-center py-3">
                     No records found.
                 </div>
             </template>

        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator v-if="store.query.rows"
                   v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="((store.query.page??1)-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>


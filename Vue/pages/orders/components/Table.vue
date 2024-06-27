    <script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useOrderStore } from '../../../stores/store-orders'

const store = useOrderStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list">
        <!--table-->
         <DataTable :value="store.list.data"
                       dataKey="id"
                    :rowClass="(rowData) => rowData.id === store.item?.id ? 'bg-yellow-100' : ''"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

            <Column field="user.display_name" header="User"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <Badge v-if="prop.data.user == null"
                           value="Trashed"
                           severity="danger"></Badge>
                    <span v-else>
                    {{prop.data.user.display_name}}<br><Button class="p-button-tiny p-button-text p-0 mr-2"
                                                             data-testid="taxonomies-table-to-edit"

                                                             @click="useVaah.copy(prop.data.user.email)"
                                                             icon="pi pi-copy"
                    > {{prop.data.user.email}}</Button>
                    </span>
                    {{prop.data.name}}
                </template>

            </Column>
             <Column field="user.phone" header="Mobile No."
                     :sortable="true">

                 <template #body="prop">
                     {{prop.data.user.phone}}

                 </template>

             </Column>
             <Column field="items" header="Order Items"
                     :sortable="false"  >

                 <template #body="prop">
                     {{prop.data.name}}
                     <div class="p-inputgroup">
                          <span class="p-inputgroup-addon cursor-pointer" v-tooltip.top="'View Order Items'" @click="store.toOrderDetails(prop.data)">
                             <b>
                                {{prop.data.items.length}}
                            </b>
                         </span>
                         <Button
                             icon="pi pi-plus"
                             severity="info"
                             size="small"
                             :disabled="prop.data.paid >= prop.data.amount"
                             v-tooltip.top="'Create Payment'"
                             @click="store.toOrderPayment(prop.data.id)"
                         />
                     </div>
                 </template>

             </Column>


             <Column field="payable" header="Payable"
                     v-if="store.isViewLarge()"
                     :sortable="true">

                 <template #body="prop">
                     <Badge severity="info">{{prop.data.payable}}</Badge>

                 </template>

             </Column>

             <Column field="paid" header="Paid"
                     v-if="store.isViewLarge()"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.paid == 0"
                            value="0"
                            severity="danger"></Badge>
                     <Badge v-else-if="prop.data.paid > 0"
                            :value="prop.data.paid"
                            severity="info"></Badge>
                 </template>

             </Column>

             <Column field="status.name" header="Order Status" :sortable="true">

                 <template #body="prop">
                     <template v-if="prop.data.status">
                         <Badge v-if="prop.data.status.slug === 'approved'" severity="success">
                             {{ prop.data.status.name }}
                         </Badge>
                         <Badge v-else-if="prop.data.status.slug === 'rejected'" severity="danger">
                             {{ prop.data.status.name }}
                         </Badge>
                         <Badge v-else severity="warning">
                             {{ prop.data.status.name }}
                         </Badge>
                     </template>
                     <template v-else>
                         <Badge severity="warning">
                             {{ prop.data.status ? prop.data.status.name : '' }}
                         </Badge>
                     </template>
                 </template>

             </Column>


             <Column header="Payment Status" :sortable="true">
                 <template #body="prop">
                     <template v-if="prop.data.order_payment_status">
                         <Badge v-if="prop.data.order_payment_status.slug === 'paid'" severity="success">
                             {{ prop.data.order_payment_status.name }}
                         </Badge>
                         <Badge v-else-if="prop.data.order_payment_status.slug === 'partially-paid'" severity="info">
                             {{ prop.data.order_payment_status.name }}
                         </Badge>
                         <Badge v-else severity="danger">
                             {{ prop.data.order_payment_status.name }}
                         </Badge>
                     </template>
                     <template v-else>
                         <Badge severity="danger">

                         </Badge>
                     </template>
                 </template>
             </Column>

             <Column  header="Shipping Status"
                     :sortable="true">
                 <template #body="prop">
                     <Badge severity="success">
                         {{'N/A' }}
                     </Badge>
                 </template>


             </Column>




            <Column field="is_active" v-if="store.isViewLarge()"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 data-testid="orders-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsActive(prop.data)">
                    </InputSwitch>
                </template>

            </Column>

            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="orders-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="orders-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="orders-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="orders-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />


                    </div>

                </template>


            </Column>


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

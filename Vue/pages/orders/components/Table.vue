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
                   class="p-datatable-sm"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

            <Column field="name"
                    header="User"
                    :sortable="true"
                    v-if="store.isViewLarge()"
            >

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <Badge v-if="prop.data.user == null"
                           value="Trashed"
                           severity="danger"></Badge>
                    <span v-else>
                     {{prop.data.user.first_name}}
                         </span>
                </template>

            </Column>

             <Column field="order_items"
                     header="Order Items"
                     :sortable="false"
                     style="width: 100px;"
             >


                 <template #body="prop">
                     <Button class="p-button-tiny p-button-text"
                             data-testid="orders-table-to-view"
                             v-tooltip.top="'Add Order Item'"
                             @click="store.toOrderItem(prop.data)"
                             icon="pi pi-eye" ><b>+</b></Button>
<!--                     <div class="p-inputgroup flex-1">-->
<!--                         <button v-tooltip.top="'Add Order Item'" @click="store.toOrderItem(prop.data)"><b>+</b></button>-->
<!--                     </div>-->
                 </template>

             </Column>

             <Column field="payment_method" header="Payment Method"
                    :sortable="true">
                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <Badge v-if="prop.data.payment_method == null"
                           value="Trashed"
                           severity="danger"></Badge>
                    <template v-else>
                        {{prop.data.payment_method.name}}
                    </template>
                </template>

            </Column>

             <Column field="amount" header="Amount"
                     v-if="store.isViewLarge()"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    {{prop.data.amount}}
                </template>

            </Column>

<!--             <Column field="delivery_fee" header="Delivery Fee"-->
<!--                     v-if="store.isViewLarge()"-->
<!--                    :sortable="true">-->

<!--                <template #body="prop">-->
<!--                    <Badge v-if="prop.data.deleted_at"-->
<!--                           value="Trashed"-->
<!--                           severity="danger"></Badge>-->
<!--                    {{prop.data.delivery_fee}}-->
<!--                </template>-->

<!--            </Column>-->

<!--             <Column field="taxes" header="Taxes"-->
<!--                     v-if="store.isViewLarge()"-->
<!--                    :sortable="true">-->

<!--                <template #body="prop">-->
<!--                    <Badge v-if="prop.data.deleted_at"-->
<!--                           value="Trashed"-->
<!--                           severity="danger"></Badge>-->
<!--                    {{prop.data.taxes}}-->
<!--                </template>-->

<!--            </Column>-->

<!--             <Column field="discount" header="Discount"-->
<!--                     v-if="store.isViewLarge()"-->
<!--                    :sortable="true">-->

<!--                <template #body="prop">-->
<!--                    <Badge v-if="prop.data.deleted_at"-->
<!--                           value="Trashed"-->
<!--                           severity="danger"></Badge>-->
<!--                    {{prop.data.discount}}-->
<!--                </template>-->

<!--            </Column>-->

<!--             <Column field="payable" header="Payable"-->
<!--                     v-if="store.isViewLarge()"-->
<!--                    :sortable="true">-->

<!--                <template #body="prop">-->
<!--                    <Badge v-if="prop.data.deleted_at"-->
<!--                           value="Trashed"-->
<!--                           severity="danger"></Badge>-->
<!--                    {{prop.data.payable}}-->
<!--                </template>-->

<!--            </Column>-->

             <Column field="paid" header="Paid"
                     v-if="store.isViewLarge()"
                    :sortable="true"
                     style="width: 65px;"
             >

                 <template #body="prop">
                     <Badge v-if="prop.data.paid == 0"
                            value="0"
                            severity="danger"></Badge>
                     <Badge v-else-if="prop.data.paid > 0"
                            :value="prop.data.paid"
                            severity="success"></Badge>
                 </template>

            </Column>


             <Column field="status" header="Status"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <Badge v-if="prop.data.status.slug == 'approved'"
                            severity="success"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="prop.data.status.slug == 'rejected'"
                            severity="danger"> {{prop.data.status.name}} </Badge>
                     <Badge v-else
                            severity="primary"> {{prop.data.status.name}} </Badge>
                 </template>

             </Column>


                <Column field="updated_at" header="Updated"
                        v-if="store.isViewLarge()"
                        style="width:120px;"
                        :sortable="true">

                    <template #body="prop">
                        {{useVaah.ago(prop.data.updated_at)}}
                    </template>

                </Column>

            <Column field="is_active" v-if="store.isViewLarge()"
                    :sortable="true"
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
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="orders-table-to-edit"
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

        <Divider />

        <!--paginator-->
        <Paginator v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page">
        </Paginator>
        <!--/paginator-->

    </div>

</template>

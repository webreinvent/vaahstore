<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductStore } from '../../../stores/store-products'
import { useVendorStore} from "../../../stores/store-vendors";

const store = useProductStore();
const vendorStore = useVendorStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list" class="data-container" style=" display: flex;flex-direction: column;justify-content: center; height: 100%;">
        <!--table-->
         <DataTable :value="store.list.data"
                       dataKey="id"
                    :rowClass="(rowData) => rowData.id === store.item.id ? 'bg-yellow-100' : ''"
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

            <Column field="name" header="Name"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <span v-if="prop.data.is_default">
                        <Badge severity="info">Default</Badge>
                        <div style="word-break: break-word;">{{ prop.data.name }}</div>
                         </span>
                    <span v-else>
                        <div style="word-break: break-word;">{{ prop.data.name }}</div>
                    </span>
                </template>

            </Column>

             <Column field="in_stock" header="In Stock"
                     style="width:60px;"
                     v-if="store.isViewLarge()">

                 <template #body="prop">
                     <Badge v-if="prop.data.in_stock == 0"
                            value="No"
                            severity="danger"></Badge>
                     <Badge v-else-if="prop.data.in_stock == 1"
                            value="yes"
                            severity="success"></Badge>
                 </template>

             </Column>

             <Column field="quantity" header="Quantity"
                     v-if="store.isViewLarge()"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.quantity == 0"
                            value="0"
                            severity="danger"></Badge>
                     <Badge v-else-if="prop.data.quantity > 0"
                            :value="prop.data.quantity"
                            severity="info"></Badge>
                 </template>
             </Column>

             <Column field="variations" header="Variations"
                     :sortable="false">

                 <template #body="prop">
                     <div class="p-inputgroup">
                         <span class="p-inputgroup-addon">
                             <b v-if="prop.data.product_variations && prop.data.product_variations.length">
                                 {{prop.data.product_variations.length}}
                            </b>
                              <b v-else>0</b>
                         </span>
                         <Button icon="pi pi-plus" severity="info" v-if="!prop.data.deleted_at"
                                 size="small"
                                 v-tooltip.top="'Add Variations'"
                                 @click="store.toVariation(prop.data)" />
                     </div>
                 </template>
             </Column>

             <Column field="vendors" header="Vendors"
                     :sortable="false">

<!--                 <template #body="prop">-->
<!--                     <div class="p-inputgroup flex-1">-->
<!--                        <span class="p-inputgroup-addon">-->
<!--                            <b v-if="prop.data.product_vendors && prop.data.product_vendors.length">-->
<!--                                {{prop.data.product_vendors.length}}</b>-->
<!--                            <b v-else>0</b>-->
<!--                        </span>-->
<!--                         <button @click="store.toVendor(prop.data)"><b>+</b></button>-->
<!--                     </div>-->
<!--                 </template>-->

                 <template #body="prop">
                     <div class="p-inputgroup">
                         <span class="p-inputgroup-addon cursor-pointer"
                          v-tooltip.top="'View Vendors'"
                          @click="vendorStore.toViewVendors(prop.data)">
                             <b v-if="prop.data.product_vendors && prop.data.product_vendors.length">
                                 {{prop.data.product_vendors.length}}
                            </b>
                             <b v-else>0</b>
                         </span>
                         <Button icon="pi pi-plus" severity="info" v-if="!prop.data.deleted_at"
                                 size="small"
                                 v-tooltip.top="'Add Vendors'"
                                 @click="store.toVendor(prop.data)" />
                     </div>
                 </template>


             </Column>

             <Column field="status.name" header="Status"
                     :sortable="true">

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
                        :sortable="true">

                    <template #body="prop">
                        {{useVaah.ago(prop.data.updated_at)}}
                    </template>

                </Column>

            <Column field="is_active" v-if="store.isViewLarge()"
                    style="width:80px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 data-testid="products-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsActive(prop.data)">
                    </InputSwitch>
                </template>

            </Column>

            <Column field="actions"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="products-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="products-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="products-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="products-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
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
                   :first="(store.query.page-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>

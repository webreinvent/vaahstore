<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductStockStore } from '../../../stores/store-productstocks'

const store = useProductStockStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store?.list">
        <!--table-->
         <DataTable :value="store.list.data"
                   dataKey="id"
                   :rowClass="(rowData) => rowData.id === store.item?.id ? 'bg-blue-100' : ''"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   v-model:selection="store.action.items"
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
                    headerStyle="width: 3em">
            </Column>
             <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

             <Column field="vendor.name" header="Vendor"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.vendor && prop.data.vendor.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <div style="word-break: break-word;" v-if="prop.data.vendor && prop.data.vendor.name">{{ prop.data.vendor.name }}</div>
                 </template>

             </Column>

             <Column field="product.name" header="Product"
                     :sortable="true">

                 <template #body="prop" >
                     <Badge v-if="prop.data.product && prop.data.product.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                        <div style="word-break: break-word;" v-if="prop.data.product && prop.data.product.name">
                            {{ prop.data.product.name }}
                        </div>
                 </template>

             </Column>

             <Column field="product_variation.name" header="Product Variation"
                     :sortable="true">

                 <template #body="prop" >
                     <Badge v-if="prop.data.product_variation && prop.data.product_variation.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <div style="word-break: break-word;" v-if="prop.data.product_variation && prop.data.product_variation.name">
                         {{ prop.data.product_variation.name }}
                     </div>
                 </template>

             </Column>

             <Column field="quantity" header="Quantity"
                     :sortable="true">

                 <template #body="prop">
                     <p class="text-center" v-if="prop.data.quantity == 0"
                            >0</p>
                     <p class="text-center" v-else-if="prop.data.quantity > 0"
                            >{{prop.data.quantity}}</p>
                 </template>

             </Column>



             <Column field="status" header="Status">
                 <template #body="prop">
                     <Badge unstyled="true" class="!text-green-500 bg-[#0E9F6E1A]" v-if="prop.data.status && prop.data.status.slug == 'approved'"
                            severity="success"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="!prop.data.status"
                            unstyled="true"> null </Badge>
                     <Badge class="!text-red-500 bg-[#E02424]/10 " v-else-if="prop.data.status && prop.data.status.slug == 'rejected'"
                            unstyled="true"> {{prop.data.status.name}} </Badge>
                     <Badge v-else class="!text-yellow-500 bg-[#E3A0081A]/10"
                            unstyled="true"> {{prop.data.status.name}} </Badge>
                 </template>
             </Column>

            <Column field="is_active" v-if="store.isListView()"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <ToggleSwitch v-model.bool="prop.data.is_active"
                                 data-testid="productstocks-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                  size="small"
                                  variant="success"
                                 @input="store.toggleIsActive(prop.data)">
                    </ToggleSwitch>
                </template>

            </Column>

            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup gap-2 ">

                        <Button class="p-button-tiny icon-button p-button-text"
                                data-testid="productstocks-table-to-view"
                                v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if="store.assets.permissions.includes('can-update-module')"
                                class="p-button-tiny icon-button p-button-text"
                                data-testid="productstocks-table-to-edit"
                                v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button
                                class="text-red-500 p-button-tiny icon-button p-button-text"
                                data-testid="productstocks-table-action-trash"
                                v-if="store.isListView() && !prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button
                                class="p-button-tiny p-button-success p-button-text"
                                data-testid="productstocks-table-action-restore"
                                v-if="store.isListView() && prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
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

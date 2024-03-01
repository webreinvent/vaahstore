<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductVendorStore } from '../../../stores/store-productvendors'

const store = useProductVendorStore();
const useVaah = vaah();
</script>

<template>

    <div v-if="store.list">
        <!--table-->
         <DataTable :value="store.list.data"
                       dataKey="id"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :rowClass="(rowData) =>rowData.id === store.item?.id ? 'bg-yellow-100':''"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

             <Column field="vendor.name" header="Vendor"
                     :sortable="true">

                 <template #body="prop" >
                     <Badge v-if="prop.data.vendor && prop.data.vendor.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <div style="word-break: break-word;" v-if="prop.data.vendor && prop.data.vendor.name">
                         {{ prop.data.vendor.name }}
                     </div>
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

             <Column field="add_price" header="Add Price"
                     :sortable="false"  >

                 <template #body="prop">
                     <div class="p-inputgroup flex-1">
                        <span class="p-inputgroup-addon"
                              :class="{ 'cursor-pointer': prop.data.product.product_variations_for_vendor_product.length > 0 }"
                              v-tooltip.top="'Total Variations'" @click="store.toViewProductVariations(prop.data.product)">
                              <b>{{ prop.data.product.product_variations_for_vendor_product ? prop.data.product.product_variations_for_vendor_product.length : 0 }}</b>
                        </span>
                         <button class="p-button-tiny"
                                 v-tooltip.top="'Add Price Item'"
                                 icon="pi pi-plus" severity="info"
                                 style="border-width : 0; background: #4f46e5;cursor: pointer;"
                                 @click="store.toProductPrice(prop.data)"
                                 :disabled="$route.path.includes('price') && prop.data.id===store.item?.id">
                             <i class="pi pi-plus" style="color: white"></i>
                         </button>
                     </div>
                 </template>

             </Column>


             <Column header="Product Price Range(min-max)" :sortable="false">
                 <template #body="prop">
                     <div class="p-inputgroup flex-1">
            <div  v-tooltip.top="'Variations Price Range'">
                <div v-if="prop.data.product_variation_prices ">
                    {{ store.calculatePriceRange(prop.data.product, prop.data.product_variation_prices) }}
                </div>
                <div v-else>
                    No Price Available
                </div>
            </div>
                     </div>
                 </template>
             </Column>

             <Column field="added_by" header="Added By"
                     v-if="store.isViewLarge()"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.added_by == null"
                            value="Trashed"
                            severity="danger"></Badge>
                     <span v-else>
                     {{prop.data.added_by_user.first_name}}
                         </span>
                 </template>

             </Column>

             <Column field="status.name" header="Status"
                     v-if="store.isViewLarge()"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.status && prop.data.status.slug == 'approved'"
                            severity="success"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="prop.data.status && prop.data.status.slug == 'rejected'"
                            severity="danger"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="prop.data.status && prop.data.status.name == 'Pending'"
                            severity="warning"> {{prop.data.status.name}}</Badge>
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
                    :sortable="false"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 data-testid="productvendors-table-is-active"
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
                                data-testid="productvendors-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                                class="p-button-tiny p-button-text"
                                data-testid="productvendors-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="productvendors-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at  && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="productvendors-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at  && store.assets.permissions.includes('can-update-module')"
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

<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductVendorStore } from '../../../stores/store-productvendors'

const store = useProductVendorStore();
const useVaah = vaah();
</script>

<template>

    <div v-if="store?.list">
        <!--table-->
         <DataTable :value="store.list.data"
                       dataKey="id"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :rowClass="(rowData) =>rowData.id === store.item?.id ? 'bg-yellow-100':''"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
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

             <Column field="add_price" header="Add Price" :sortable="false">
                 <template #body="prop">
                     <div class="p-inputgroup  justify-between !items-center border py-1 px-2 rounded-lg !gap-6">
            <span class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
                  v-tooltip.top="'Total Variations'"
                  @click="prop.data.product?.product_variations_for_vendor_product?.length && store.toViewProductVariations(prop.data.product)"
                  :class="{ 'cursor-pointer': prop.data.product?.product_variations_for_vendor_product?.length }">
                <b>{{ prop.data.product?.product_variations_for_vendor_product?.length || 0 }}</b>
            </span>
                         <Button :pt="{ icon: { class: '!text-[8px]' } }"
                                 icon="pi pi-plus" class="quantity-button !rounded"
                                 v-tooltip.top="'Add Price Item'"
                                 severity="info"
                                 @click="store.toProductPrice(prop.data)"
                                 :disabled="$route.path.includes('price') && prop.data.id===store.item?.id" />
                     </div>
                 </template>
             </Column>

             <Column header="Product Price Range(min-max)" :sortable="false">
                 <template #body="prop">
                     <div class="p-inputgroup flex-1">
                         <div v-tooltip.top="'Variations Price Range'">
                             <Badge severity="info" v-if="prop.data && Array.isArray(prop.data.product_price_range)&& prop.data.product_price_range.length > 0">
                                 <span v-html="prop.data.product?.store.default_currency.symbol"></span>{{ prop.data.product_price_range.join(' - ') }}
                             </Badge>
                             <Badge severity="danger" v-else>
                                 Not Available
                             </Badge>
                         </div>
                     </div>
                 </template>
             </Column>






             <Column field="added_by" header="Added By"
                     v-if="store.isListView()"
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
                     v-if="store.isListView()"
                     :sortable="true">

                 <template #body="prop">
                     <Badge unstyled="true" class="!text-green-500 bg-[#0E9F6E1A]" v-if="prop.data.status && prop.data.status.slug == 'approved'"
                            severity="success"> {{prop.data.status.name}} </Badge>
                     <Badge unstyled="true"  class="!text-red-500 bg-[#E02424]/10 " v-else-if="prop.data.status && prop.data.status.slug == 'rejected'"
                            severity="danger"> {{prop.data.status.name}} </Badge>
                     <Badge  unstyled="true" class="!text-yellow-500 bg-[##E3A0081A]/10" v-else-if="prop.data.status && prop.data.status.name == 'Pending'"
                            severity="warn"> {{prop.data.status.name}}</Badge>
                 </template>

             </Column>

             <Column field="updated_at" header="Updated"
                        v-if="store.isListView()"

                        :sortable="true">

                    <template #body="prop">
                        {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}
                    </template>

                </Column>

            <Column field="is_active" v-if="store.isListView()"
                    :sortable="false"
                    header="Is Active">

                <template #body="prop">
                    <ToggleSwitch  v-model.bool="prop.data.is_active"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 data-testid="productvendors-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                   size="small"
                                   variant="success"
                                 @input="store.toggleIsActive(prop.data)">
                    </ToggleSwitch >
                </template>

            </Column>

            <Column field="actions"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup gap-2">

                        <Button class="p-button-tiny p-button-text icon-button"
                                data-testid="productvendors-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                                class="p-button-tiny p-button-text icon-button"
                                data-testid="productvendors-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text icon-button text-red-500"
                                data-testid="productvendors-table-action-trash"
                                v-if="store.isListView() && !prop.data.deleted_at  && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text icon-button"
                                data-testid="productvendors-table-action-restore"
                                v-if="store.isListView() && prop.data.deleted_at  && store.assets.permissions.includes('can-update-module')"
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

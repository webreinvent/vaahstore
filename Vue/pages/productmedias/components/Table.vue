<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductMediaStore } from '../../../stores/store-productmedias'

const store = useProductMediaStore();
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
                    v-if="store.isListView()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID"  :sortable="true">
            </Column>

            <Column field="product.name" header="Product"
                    :sortable="true" >

                <template #body="prop" >
                    <Badge v-if="prop.data.product && prop.data.product.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <div style="word-break: break-word;" v-if="prop.data.product && prop.data.product.name">
                        {{ prop.data.product.name }}
                    </div>
                </template>

            </Column>

             <Column field="product_variation.name" header="Product Variations" >
                 <template #body="prop">
                     <div class="flex flex-wrap gap-2" v-if="prop.data.product_variation_media && prop.data.product_variation_media.length > 0">
                         <template v-if="prop.data.product_variation_media.some(variation => variation.deleted_at === null)">
                             <!-- Display variation names -->
                             <template v-for="(variation, index) in prop.data.product_variation_media" :key="index">
                                 <Badge class="h-max max-w-full" v-if="variation.deleted_at === null">
                                     {{ variation.name }}
                                 </Badge>
                             </template>
                             <Badge v-if="prop.data.product_variation_media.some(variation => variation.deleted_at !== null)" value="Trashed" severity="danger"></Badge>
                         </template>
                         <template v-else>
                             <Badge value="Trashed" severity="danger"></Badge>
                         </template>
                     </div>
                 </template>
             </Column>








             <Column field="status" header="Status">
                 <template #body="prop">
                     <Badge v-if="prop.data.status && prop.data.status.slug == 'approved'"
                            severity="success"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="!prop.data.status"
                            severity="primary"> null </Badge>
                     <Badge v-else-if="prop.data.status && prop.data.status.slug == 'rejected'"
                            severity="danger"> {{prop.data.status.name}} </Badge>
                     <Badge v-else
                            severity="warn"> {{prop.data.status.name}} </Badge>
                 </template>
             </Column>



            <Column field="is_active" v-if="store.isListView()"
                    :sortable="true"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <ToggleSwitch v-model.bool="prop.data.is_active"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 data-testid="productmedias-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsActive(prop.data)">
                    </ToggleSwitch>
                </template>

            </Column>

            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="productmedias-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                            class="p-button-tiny p-button-text"
                                data-testid="productmedias-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="productmedias-table-action-trash"
                                v-if="store.isListView() && !prop.data.deleted_at  && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="productmedias-table-action-restore"
                                v-if="store.isListView() && prop.data.deleted_at  && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />


                    </div>

                </template>


            </Column>
<!--For empty table-->
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

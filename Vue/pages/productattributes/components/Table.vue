<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductAttributeStore } from '../../../stores/store-productattributes'

const store = useProductAttributeStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list">
        <!--table-->
         <DataTable :value="store.list.data"
                       dataKey="id"
                    :rowClass="(rowData) => rowData.id === store.item.id ? 'bg-yellow-200' : ''"
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

             <Column field="product_variation.name" header="Product Variation"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <Badge v-if="prop.data.product_variation.is_default">Default</Badge>
                     <div style="word-break: break-word;" v-if="prop.data.product_variation && prop.data.product_variation.name">
                         {{prop.data.product_variation.name}}</div>
                 </template>

             </Column>

             <Column field="attribute.name" header="Attribute"
                     :sortable="true">

                 <template #body="prop">
                     <div style="word-break: break-word;" v-if="prop.data.attribute && prop.data.attribute.name">
                         {{prop.data.attribute.name}}</div>
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

            <Column field="actions"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="productattributes-table-to-view"
                                v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item.id"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="productattributes-table-to-edit"
                                v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item.id"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="productattributes-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="productattributes-table-action-restore"
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

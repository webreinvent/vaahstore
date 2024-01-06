<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductVariationStore } from '../../../stores/store-productvariations'

const store = useProductVariationStore();
const useVaah = vaah();

const permission=store.assets.permission;

</script>

<template>

    <div v-if="store.list">
        <!--table-->
        <DataTable
            :value="store.list.data"
            dataKey="id"
            :rowClass="(rowData) => rowData && rowData.id === store.item && store.item.id ? 'bg-yellow-200' : ''"
            class="p-datatable-sm p-datatable-hoverable-rows"
            v-model:selection="store.action.items"
            stripedRows
            responsiveLayout="scroll"
        >

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

            <Column field="name" header="Name" :sortable="true">

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

             <Column field="product.name" header="Product"
                     :sortable="true">

                 <template #body="prop">
                     {{store.shortCharacter(prop.data.product.name)}}
                 </template>

             </Column>

             <Column field="quantity" header="Quantity"
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

             <Column field="status.name" header="Status">

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

            <Column
                field="is_active"
                v-if="store.isViewLarge()"
                :sortable="true"
                style="width:100px;"
                header="Is Active"
            >
                <template #body="prop">
                    <InputSwitch
                        v-model.bool="prop.data.is_active"
                        data-testid="productvariations-table-is-active"
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        class="p-inputswitch-sm"
                        @input="store.toggleIsActive(prop.data)"
                        :pt="{
        slider: ({ props }) => ({
          class: props.modelValue ? 'bg-green-400' : '',
        }),
      }"
                        :disabled="!store.assets.permission.includes('can-update-module')"
                    ></InputSwitch>
                </template>
            </Column>


            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="productvariations-table-to-view"
                                v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item && store.item.id"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permission.includes('can-update-module') "
                                class="p-button-tiny p-button-text"
                                data-testid="productvariations-table-to-edit"
                                v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item.id"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button
                                class="p-button-tiny p-button-danger p-button-text"
                                data-testid="productvariations-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at &&  store.assets.permission.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="productvariations-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />


                    </div>

                </template>


            </Column>

             <template #empty>
                 <tr>
                     <td>


                         <h1 style="font-family: Inter,ui-sans-serif,system-ui,
                         -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,
                         Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,
                         Noto Color Emoji;
                         font-size: .8rem;
                         margin-left: 19rem;
                         font-weight: 400;">No Record found.</h1>

                     </td>
                 </tr>
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

<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useVendorStore } from '../../../stores/store-vendors'

const store = useVendorStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list">
        <!--table-->
        <DataTable :value="store.list.data"
                   dataKey="id"
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
                    <div style=" width:150px; overflow-wrap: break-word; word-wrap:break-word;">
                        {{prop.data.name}}
                    </div>
                </template>


            </Column>

            <Column field="store.name" header="Store"
                    v-if="store.isViewLarge()"
                    :sortable="true">

                <template #body="prop">
                    {{prop.data.store.name}}
                    <span v-if="prop.data.store.is_default == 1">
                         <badge>&nbsp;(Default)</badge>
                     </span>
                </template>

            </Column>

            <Column field="product" header="Product"
                    :sortable="false">

                <template #body="prop">
                    <div class="p-inputgroup flex-1">
                        <span class="p-inputgroup-addon">
                            <b v-if="prop.data.vendor_products && prop.data.vendor_products.length">{{prop.data.vendor_products.length}}</b>
                            <b v-else>0</b>
                        </span>
                        <button @click="store.toProduct(prop.data)"><b>+</b></button>
                    </div>
                </template>

            </Column>

            <Column field="status" header="Status"
                    v-if="store.isViewLarge()"
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

            <Column field="owned_by_user.name" header="Owned By"
                    v-if="store.isViewLarge()"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.owned_by_user == null"
                           value="Trashed"
                           severity="danger"></Badge>
                    <span v-else>
                     {{prop.data.owned_by_user.name}}
                         </span>
                </template>
            </Column>


            <Column field="updated_at" header="Updated"
                    v-if="store.isViewLarge()"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}
                </template>

            </Column>

            <Column field="is_active" v-if="store.isViewLarge()"
                    :sortable="true"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 data-testid="vendors-table-is-active"
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
                                data-testid="vendors-table-to-view"
                                :disabled="store.item && store.item.id === prop.data.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="vendors-table-to-edit"
                                :disabled="store.item && store.item.id === prop.data.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="vendors-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="vendors-table-action-restore"
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
                   :first="(store.query.page-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>

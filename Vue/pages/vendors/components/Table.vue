<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useVendorStore } from '../../../stores/store-vendors'

const store = useVendorStore();
const useVaah = vaah();


const permissions=store.assets.permissions;

</script>

<template>

    <div v-if="store.list" style=" display: flex;flex-direction: column;justify-content: center; height: 100%;">
        <Message v-if="!store.list.data || store.default_vendor_message" severity="warn" class="mt-1" :closable="false">
            {{store.default_vendor_message}}
        </Message>
        <!--table-->
        <DataTable :value="store.list.data"
                   dataKey="id"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :rowClass="(rowData) =>rowData.id === store && store.item && store.item.id ? 'bg-yellow-100':''"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
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
                        <span v-if="prop.data.is_default == 1">
                         <Badge severity="info">&nbsp;(Default)</Badge>
                     </span>
                    </div>
                </template>


            </Column>

            <Column field="store.name" header="Store"
                    :sortable="true">


                <template #body="prop" >
                    <Badge v-if="prop.data.store && prop.data.store.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <div style="word-break: break-word;" v-if="prop.data.store && prop.data.store.name">
                        {{ prop.data.store.name }}
                    </div>
                </template>

            </Column>

            <Column field="product" header="Product"
                    :sortable="false">

                <template #body="prop">
                    <div class="p-inputgroup  justify-between !items-center border py-1 px-2 rounded-lg !gap-6">
                        <span class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
                              v-tooltip.top="'View Products'"
                              v-if="(prop.data.vendor_products && prop.data.vendor_products.length)&& !prop.data.is_default"
                            @click="store.toViewProducts(prop.data)">
                               <b>{{prop.data.vendor_products.length}}</b>
                        </span>
                        <span class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
                              v-tooltip.top="'All Products'"
                              v-else-if="prop.data.is_default "
                              @click="store.toViewAllProduct()">
                             <b>{{store.total_product_count}}</b>

                         </span>
                        <span class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
                              v-else-if="prop.data.is_default">
                             <b>{{store.assets.total_product}}</b>
                         </span>

                        <span class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
                              v-else>
                             <b>{{ prop.data.product_vendors ? prop.data.product_vendors.length : 0 }}</b>
                         </span>
                        <Button @click="store.toProduct(prop.data)"
                                icon="pi pi-plus" class="quantity-button !rounded"
                                severity="info"
                                data-testid="vendors-table-product"
                                :disabled="$route.path.includes('product') && prop.data.id===store.item?.id"
                                 :pt="{ icon: { class: '!text-[8px]' } }"
                                v-tooltip.top="'Add Products'" />

                    </div>
                </template>

            </Column>




            <Column field="vendor" header="Vendor User"
                    :sortable="false">
                <template #body="prop">
                    <div class="p-inputgroup  justify-between !items-center border py-1 px-2 rounded-lg !gap-0  ">
                        <span class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
                              v-if="prop.data.users && prop.data.users.length"
                              >
                               <b>{{prop.data.users.length}}</b>
                        </span>
                        <span class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
                              v-else>
                             <b class="border-none">{{ prop.data.users ? prop.data.users.length : 0 }}</b>

                         </span>
                        <Button @click="store.toVendorRole(prop.data)"
                                data-testid="vendors-table-vendor-role"
                                 icon="pi pi-plus" class="quantity-button !rounded"
                                severity="info"
                                :pt="{ icon: { class: '!text-[8px]' } }"
                                :disabled="$route.path.includes('role') && prop.data.id===store.item?.id"
                                :class="{ 'blurred': $route.path.includes('role') && prop.data.id===store.item?.id }"
                                v-tooltip.top="'Add Role'" />

                    </div>
                </template>

            </Column>

            <Column field="status.name" header="Status"
                    v-if="store.isListView()"
                    :sortable="true">
                <template #body="prop">
                    <Badge unstyled="true" class="!text-green-500 bg-[#0E9F6E1A]" v-if="prop.data.status && prop.data.status.name == 'Approved'"
                          > {{prop.data.status.name}} </Badge>
                    <Badge unstyled="true"  class="!text-red-500 bg-[#E02424]/10 " v-else-if="prop.data.status && prop.data.status.name == 'Rejected'"
                           severity="danger"> {{prop.data.status.name}} </Badge>
                    <Badge v-else unstyled="true" class="!text-yellow-500 bg-[##E3A0081A]/10"
                           severity="warn"> {{prop.data.status.name}} </Badge>
                </template>

            </Column>

            <Column field="owned_by_user.name" header="Owned By"
                    v-if="store.isListView()"
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
                    v-if="store.isListView()"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}
                </template>

            </Column>

            <Column field="is_active" v-if="store.isListView()"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <ToggleSwitch v-model.bool="prop.data.is_active"
                                 data-testid="vendors-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 @input="store.toggleIsActive(prop.data)"/>

                </template>

            </Column>

            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup gap-2 ">

                        <Button class="p-button-tiny p-button-text icon-button"
                                data-testid="vendors-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                            class="p-button-tiny p-button-text icon-button"
                                data-testid="vendors-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text icon-button text-red-500"
                                data-testid="vendors-table-action-trash"
                                v-if="store.isListView() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                :disabled="!store.assets.permissions.includes('can-update-module')"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text icon-button"
                                data-testid="vendors-table-action-restore"
                                v-if="store.isListView() && prop.data.deleted_at"
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

<style scoped>
.blurred {
    filter: blur(1px); /* Adjust the blur amount as needed */
}
</style>

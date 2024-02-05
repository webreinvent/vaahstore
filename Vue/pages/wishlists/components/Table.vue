<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useWishlistStore } from '../../../stores/store-wishlists'
const store = useWishlistStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list" style=" display: flex;flex-direction: column;justify-content: center; height: 100%;">
        <!--table-->
         <DataTable :value="store.list.data"
                   dataKey="id"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   v-model:selection="store.action.items"
                   :rowClass="(rowData) =>rowData.id === store.item?.id ? 'bg-yellow-100':''"
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
                     <template v-if="prop.data.is_default == 1">
                         <Badge v-if="prop.data.deleted_at" value="Trashed" severity="danger"></Badge>
                         <Badge severity="primary" >Default</Badge>
                         <div style="word-break: break-word;">{{ prop.data.name }}</div>
                     </template>
                     <template v-else>
                         <Badge v-if="prop.data.deleted_at" value="Trashed" severity="danger"></Badge>
                         <div style="word-break: break-word;">{{ prop.data.name }}</div>
                     </template>
                 </template>

             </Column>

             <Column field="product" header="Product">

                 <template #body="prop">
                     <div class="p-inputgroup">
                         <span
                             v-if="prop.data.products && prop.data.products.length"
                             class="p-inputgroup-addon cursor-pointer"
                             v-tooltip.top="'View Products'"
                               @click="store.toProduct(prop.data)">
                             <b>{{prop.data.products.length}}</b>
                         </span>
                         <span class="p-inputgroup-addon" v-else>
                             <b>{{prop.data.products.length}}</b>
                         </span>
                         <Button :disabled="!store.assets.permissions.includes('can-update-module')"
                                icon="pi pi-plus" severity="info" v-if="!prop.data.deleted_at"
                                 size="small"
                                 v-tooltip.top="'Add Products'"
                                 @click="store.toProduct(prop.data)" />
                     </div>
                 </template>

             </Column>

             <Column field="user.name" header="User"
                     :sortable="true">

                 <template #body="prop">{{prop.data.user.name}}</template>

             </Column>

             <Column field="type" header="Is Sharable">

                 <template #body="prop">
                     <span v-if="prop.data.type === 0" style="padding-left: 5px;">
                       No
                    </span>
                     <span v-else="prop.data.type === 1" style="padding-left: 5px;">
                      Yes
                    </span>
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
            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="wishlists-table-to-view"
                                v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if="store.assets.permissions.includes('can-update-module')"
                                class="p-button-tiny p-button-text"
                                data-testid="wishlists-table-to-edit"
                                v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="wishlists-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="wishlists-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at && store.assets.permissions.includes('can-update-module') "
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />

                        <Button class="p-button-tiny p-button-text p-button-icon-only" data-testid="wishlists-table-action-share"
                                v-tooltip.top="'Copy link'"
                                :disabled="!prop.data.type"
                                v-if="store.assets.permissions.includes('can-update-module')"
                                @click="useVaah.copy(`https://test.dev.getdemo.dev/store-dev/suraj-k001/public/backend/store#/wishlists/${prop.data.id}/product`)"
                                icon="pi pi-copy" />

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


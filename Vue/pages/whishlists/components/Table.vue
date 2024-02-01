<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useWhishlistStore } from '../../../stores/store-whishlists'
const store = useWhishlistStore();
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
                        {{prop.data.name}}
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
                         <Button icon="pi pi-plus" severity="info" v-if="!prop.data.deleted_at"
                                 size="small"
                                 v-tooltip.top="'Add Products'"
                                 @click="store.toProduct(prop.data)" />
                     </div>
                 </template>

             </Column>

             <Column field="user.name" header="User"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <Badge v-if="prop.data.user == null"
                            value="Trashed"
                            severity="danger"></Badge>
                     <span v-else style="padding-left: 5px;">
                        {{prop.data.user.name}}
                    </span>
                 </template>

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
                        {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}
                    </template>

                </Column>
            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="whishlists-table-to-view"
                                v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="whishlists-table-to-edit"
                                v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="whishlists-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="whishlists-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />

                        <Button class="p-button-tiny p-button-text p-button-icon-only" data-testid="whishlists-table-action-share"
                                v-tooltip.top="'Copy link'"
                                v-if="prop.data.type"
                                @click="useVaah.copy(`http://localhost/suraj-k001/store-dev/public/backend/store#/whishlists/${prop.data.id}/product`)"
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
                   :first="(store.query.page-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>

<style scoped>

.record-found {
    font-family: Inter,ui-sans-serif,system-ui,
    -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,
    Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,
    Noto Color Emoji;
    font-size: .8rem;
    margin-left: 19rem;
    font-weight: 400;

}
</style>

<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useAttributeGroupStore } from '../../../stores/store-attributegroups'

const store = useAttributeGroupStore();
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

            <Column field="name" header="Name"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <div style=" width:100px; overflow-wrap: break-word; word-wrap:break-word;">
                        {{prop.data.name}}
                    </div>
                </template>

            </Column>

             <Column field="attributes_list.name" header="Attributes">
                 <template #body="prop">
                     <div class="flex flex-wrap gap-2" v-if="prop.data.attributes_list && prop.data.attributes_list.length > 0">
                         <template v-if="prop.data.attributes_list.some(attribute => attribute.deleted_at === null)">
                             <template v-for="(attribute, index) in prop.data.attributes_list" :key="index">
                                 <Badge class="h-max max-w-full" v-if="attribute.deleted_at === null">
                                     {{ attribute.name }}
                                 </Badge>
                             </template>
                             <Badge v-if="prop.data.attributes_list.some(attribute => attribute.deleted_at !== null)" value="Trashed" severity="danger"></Badge>
                         </template>
                         <template v-else>
                             <Badge value="Trashed" severity="danger"></Badge>
                         </template>
                     </div>
                 </template>
             </Column>



            <Column field="is_active" v-if="store.isViewLarge()"

                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 data-testid="attributegroups-table-is-active"
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
                                data-testid="attributegroups-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                                class="p-button-tiny p-button-text"
                                data-testid="attributegroups-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="attributegroups-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at  && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="attributegroups-table-action-restore"
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

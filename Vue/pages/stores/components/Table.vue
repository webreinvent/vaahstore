<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useStoreStore } from '../../../stores/store-stores'

const store = useStoreStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store?.list" >
        <!--table-->
        <Message v-if="!store.list.data || store.message" severity="warn" class="mt-1" :closable="false">
            There is no default store. Mark a store as <strong>default</strong>.
        </Message>
        <DataTable :value="store.list.data"
                       dataKey="id"
                    :rowClass="(rowData) =>rowData.id === store && store.item && store.item.id ? 'bg-yellow-100':''"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true" class="font-bold text-xs text-gray-400">
            </Column>

            <Column field="name" header="Name" class="font-bold text-xs text-gray-400"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <Badge v-if="prop.data.is_default" severity="info">Default</Badge><div style="word-break: break-word;">{{ prop.data.name }}</div>

                </template>

            </Column>

             <Column field="status.name" header="Status" class="font-bold text-xs text-gray-400"
                     :sortable="true">
                 <template #body="prop">
                     <Badge v-if="prop.data.status && prop.data.status.name == 'Approved'"
                            severity="success" class="!text-[#0E9F6E] !rounded-full !bg-[#0E9F6E1A] px-2 py-1"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="prop.data.status && prop.data.status.name == 'Rejected'"
                            severity="danger" class="!text-[#E02424] !rounded-full !bg-[#E024241A] px-2 py-1"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="prop.data.status && prop.data.status.name == 'Pending'"
                            severity="warn" class="!text-[#FF9800] !rounded-full !bg-yellow-100 px-2 py-1"> {{prop.data.status.name}} </Badge>
                 </template>
             </Column>

             <Column field="updated_at" header="Updated" class="font-bold text-xs text-gray-400"
                        v-if="store.isListView()"
                        style="width:150px;"
                        :sortable="true">

                    <template #body="prop">
                        {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}
                    </template>

                </Column>

            <Column field="is_active" v-if="store.isListView()" class="font-bold text-xs text-gray-400"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <ToggleSwitch v-model.bool="prop.data.is_active"
                                 data-testid="stores-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                  size="small"
                                  variant="success"
                                 @input="store.toggleIsActive(prop.data)">
                    </ToggleSwitch>
                </template>

            </Column>

            <Column field="actions" style="width:150px;" class="font-bold text-xs text-gray-400"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup flex gap-3">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="stores-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                :pt="{ root: { class: '!bg-none !p-0' } }"
                                >
                                <Icon
                                icon="tabler:eye"
                                width="20"
                                height="20"
                                class="text-gray-400"
                                />
                            </Button>

                        <Button class="p-button-tiny p-button-text"
                                data-testid="stores-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                :pt="{ root: { class: '!bg-none !p-0' } }"
                                 >
                                 <Icon
                                    icon="mdi:pencil-outline"
                                    width="20"
                                    height="20"
                                    class="text-gray-400"
                                />
                                </Button>

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="stores-table-action-trash"
                                v-if="store.isListView() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                :pt="{ root: { class: '!bg-none !p-0' } }"
                                >
                                <Icon
                                    icon="mdi:trash-can-outline"
                                    width="20"
                                    height="20"
                                    class="text-[#E02424]"
                                />
                            </Button>


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="stores-table-action-restore"
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

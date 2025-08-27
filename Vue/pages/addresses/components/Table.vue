<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useAddressStore } from '../../../stores/store-addresses'
const store = useAddressStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list" style=" display: flex;flex-direction: column;justify-content: center; height: 100%;">
        <Message v-if="!store.list.data || store.default_message" severity="warn" class="mt-1" :closable="false">
            There is no default Address. Mark an address as <strong>default</strong>.
        </Message>
        <!--table-->
         <DataTable :value="store.list.data"
                       dataKey="id"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                    :rowClass="(rowData) => store.item?.id === rowData.id ? 'bg-yellow-100' : ''"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true" class="font-bold text-xs text-gray-400">
            </Column>

             <Column field="user.first_name" header="User" class="font-bold text-xs text-gray-400"
                     :sortable="true">

                 <template #body="prop" >
                     <Badge v-if="prop.data.user && prop.data.user.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <div style="word-break: break-word;" v-if="prop.data.user && prop.data.user.first_name">
                         {{ prop.data.user.first_name }}
                     </div>
                 </template>

             </Column>

             <Column field="address" header="Address" class="font-bold text-xs text-gray-400"
                     :sortable="true">

                 <template #body="prop">

                     <template v-if="prop.data.is_default == 1">
                            <Badge severity="info">Default</Badge>
                            <div style="word-break: break-word;">{{ prop.data.address }}</div>
                     </template>
                     <template v-else>
                         <div style="word-break: break-word;">{{ prop.data.address }}</div>
                     </template>
                 </template>

             </Column>

             <Column field="status" header="Status" class="font-bold text-xs text-gray-400">

                 <template #body="prop">

                     <Badge v-if="prop.data.status.slug == 'approved'" class="!text-[#0E9F6E] !rounded-full !bg-[#0E9F6E1A] !px-2 !py-1"
                            severity="success"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="prop.data.status.slug == 'rejected'"
                            severity="danger" class="!text-red-500 !bg-red-100 !rounded-full !px-2 !py-1"> {{prop.data.status.name}} </Badge>
                     <Badge v-else
                            severity="warn" class="!text-[#E3A008] !bg-[#E3A0081A] !rounded-full !px-2 !py-1"> {{prop.data.status.name}} </Badge>
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

             <Column field="is_default" v-if="store.isListView()"
                     style="width:100px;"
                     header="Is Default">

                 <template #body="prop">
                     <ToggleSwitch v-model.bool="prop.data.is_default"
                                  data-testid="addresses-table-is-active"
                                  v-bind:false-value="0"  v-bind:true-value="1"
                                  :disabled="!store.assets.permissions.includes('can-update-module')"
                                  class="p-inputswitch-sm"
                                  @input="store.toggleIsDefault(prop.data)"
                                    >
                     </ToggleSwitch>
                 </template>

             </Column>


            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup items-center gap-2">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="addresses-table-to-view"
                                v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item.id"
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

                        <Button v-if="store.assets.permissions.includes('can-update-module')"
                                class="p-button-tiny p-button-text"
                                data-testid="addresses-table-to-edit"
                                v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item.id "
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

                        <Button  v-if="store.isListView() && !prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                class="p-button-tiny p-button-danger p-button-text"
                                data-testid="addresses-table-action-trash"
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


                        <Button v-if="store.isListView() && prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                class="p-button-tiny p-button-success p-button-text"
                                data-testid="addresses-table-action-restore"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                :pt="{ root: { class: '!bg-none !p-0' } }"
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
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>

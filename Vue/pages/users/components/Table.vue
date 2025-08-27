<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useUserStore } from '../../../stores/store-users'

const store = useUserStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list && store.assets">
        <!--table-->
        <DataTable :value="store.list.data"
                   dataKey="id"
                   class="p-datatable-sm"
                   :rowClass="(rowData) => rowData.id === store.item?.id ?'bg-yellow-100' : ''"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll"
        >
            <Column selectionMode="multiple"
                    v-if="store.isListView()"
                    headerStyle="width: 3em"
            />

            <Column field="id" header="ID" :style="{ width: store.getIdWidth() }" :sortable="true" class="font-bold text-xs text-gray-400" />

            <Column field="name" header="Name" class="font-bold text-xs text-gray-400"
                    :sortable="true" style="word-break: break-word;"
            >
                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"
                    />
                    {{ prop.data.name }}
                </template>
            </Column>

            <Column field="email" header="Email" class="font-bold text-xs text-gray-400"
                    :sortable="true" style="word-break: break-word;"
            >
                <template #body="prop">
                    {{ prop.data.email }}
                </template>
            </Column>

            <Column field="customer_groups" header="Groups" class="font-bold text-xs text-gray-400"
            >
                <template #body="prop">
                    <div class="p-inputgroup">
                        <span v-if="prop.data.customer_groups && prop.data.customer_groups.length" class="p-inputgroup-addon cursor-pointer"
                              @click="store.toViewCustomerGroups(prop.data)" v-tooltip.top="'View Customer Groups'">
                              <Badge severity="success" class="!bg-green-100 !text-green-600">{{prop.data.customer_groups.length}}</Badge>
                        </span>
                        <span class="p-inputgroup-addon p-2" v-else>
                             <Badge severity="success" class="!bg-green-100 !text-green-600">0</Badge>
                         </span>
                    </div>
                </template>

            </Column>

            <Column v-if="store.isListView()" class="font-bold text-xs text-gray-400"
                    field="is_active"
                    header="Is Active"
                    :sortable="false"
                    style="width:100px;"
            >
                <template #body="prop">
                    <ToggleSwitch v-model.bool="prop.data.is_active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 data-testid="users-list_data_active"
                                 @input="store.toggleIsActive(prop.data)"
                    />
                </template>
            </Column>

            <Column field="actions" style="width:150px;" class="font-bold text-xs text-gray-400"
                    :style="{ width: store.getActionWidth() }"
                    :header="store.getActionLabel()"
            >
                <template #body="prop" >
                    <div class="p-inputgroup items-center gap-2">

                        <Button class="p-button-tiny p-button-text"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                icon="pi pi-eye"
                                data-testid="users-list_data_view"
                                :pt="{ root: { class: '!bg-none !p-0' } }"
                                >
                                <Icon
                                    icon="tabler:eye"
                                    width="20"
                                    height="20"
                                    class="text-gray-400"
                                />
                            </Button>

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                            class="p-button-tiny p-button-text"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                icon="pi pi-pencil"
                                data-testid="users-list_data_edit"
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
                                v-if="store.isListView() && !prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash"
                                data-testid="users-list_data_trash"
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
                                v-if="store.isListView() && prop.data.deleted_at && store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay"
                                data-testid="users-list_data_restore"
                        />
                    </div>
                </template>
            </Column>
            <template #empty="prop">
                <div  style="text-align: center;font-size: 12px; color: #888;">No records found.</div>
            </template>
        </DataTable>
        <!--/table-->

        <Divider />

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

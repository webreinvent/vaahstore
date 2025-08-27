<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { usePaymentStore } from '../../../stores/store-payments'
import {onMounted} from "vue";

const store = usePaymentStore();
const useVaah = vaah();

onMounted(async () => {
    store.assets_is_fetching=true;
    store.getAssets();
});

</script>

<template>

    <div v-if="store.list">
        <!--table-->
         <DataTable :value="store.list.data"
                   dataKey="id"
                   :rowClass="store.setRowClass"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :nullSortOrder="-1"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
                    headerStyle="width: 3em">
            </Column>

             <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true" class="font-bold text-xs text-gray-400">
             </Column>

             <Column field="transaction_id" class="font-bold text-xs text-gray-400"
                     header="Transaction Id"
                     style="width:150px;"

                     :sortable="true">
                 <template #body="prop">
                     <Button @click="useVaah.copy(prop.data?.transaction_id)">
                         {{prop.data?.transaction_id}}
                     </Button>
                 </template>
             </Column>
             <Column field="amount" header="Amount"  :sortable="true" :style="{width: '120px'}" class="font-bold text-xs text-gray-400">
                 <template #body="prop">
                     <div class="justify-content-end flex">
                         <span v-html="store.assets?.store_default_currency"></span>{{prop.data.amount}}
                     </div>
                 </template>
             </Column>



             <Column  header="Payment Method" style="width: 150px" class="font-bold text-xs text-gray-400"
                      :sortable="true">

                 <template #body="prop">
                     <div class="justify-content-center flex">
                     <span >
                         {{prop.data.payment_method?.name}}
                     </span>
                     </div>
                 </template>

             </Column>
             <Column header="Orders Count" 
                     class="overflow-wrap-anywhere justify-content-center flex font-bold text-xs text-gray-400"
                     :style="{width: '120px'}"
                     :sortable="true">

                 <template #body="prop">
                         <Tag severity="info" class="p-inputgroup-addon cursor-pointer !bg-blue-100 text-blue-700 !rounded-lg border-0" @click="store.toView(prop.data)" >{{prop.data.orders_count}}</Tag>
                 </template>

             </Column>

             <Column  header="Payment Status"  v-if="store.isListView()"  class="overflow-wrap-anywhere font-bold text-xs text-gray-400"
                      :sortable="true">
                 <template #body="prop">

                     <template v-if="prop.data.status">
                         <Badge v-if="prop.data.status.slug == 'success'" severity="success" class="!text-[#0E9F6E] !rounded-full !bg-[#0E9F6E1A] px-2 py-1">
                             {{prop.data.status.name}}
                         </Badge>
                         <Badge v-else-if="prop.data.status.slug == 'pending'" severity="warn" class="!text-yellow-500 !rounded-full !bg-yellow-100 px-2 py-1">
                             {{prop.data.status.name}}
                         </Badge>
                         <Badge v-else severity="danger" class="!text-[#E02424] !rounded-full !bg-[#E024241A] px-2 py-1">
                             {{prop.data.status.name}}
                         </Badge>
                     </template>
                 </template>


             </Column>

             <Column field="created_at" header="Created At" class="font-bold text-xs text-gray-400"
                     v-if="store.isListView()"
                     style="width:150px;"
                     :sortable="true">

                 <template #body="prop">
                     {{useVaah.toLocalTimeShortFormat(prop.data.created_at)}}
                 </template>

             </Column>
            <Column field="actions" style="width:150px;" class="font-bold text-xs text-gray-400"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup flex items-center gap-2">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="payments-table-to-view"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                :pt="{ root: { class: '!bg-none !p-0' } }">
                                <Icon
                                icon="tabler:eye"
                                width="20"
                                height="20"
                                class="text-gray-400"
                                />
                            </Button>
                                
                        

                        <Button class="p-button-tiny p-button-text"
                                data-testid="payments-table-to-edit"
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
                                data-testid="payments-table-action-trash"
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
                                data-testid="payments-table-action-restore"
                                v-if="store.isListView() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />


                    </div>

                </template>


            </Column>

             <template #empty>
                 <div class="text-center py-3">
                     No records found.
                 </div>
             </template>

        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator v-if="store.query.rows"
                   v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="((store.query.page??1)-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>


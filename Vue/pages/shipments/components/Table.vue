<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useShipmentStore } from '../../../stores/store-shipments'

import {onMounted, ref} from "vue";
const store = useShipmentStore();
const useVaah = vaah();

const selected_shipping_status = ref();
const toggleQuickFilterState = (event) => {
    selected_shipping_status.value.toggle(event);
};
const openLinkInNewTab = (url) => {
    window.open(url, '_blank');
};
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


            <Column field="name" header="Name"
                    class="overflow-wrap-anywhere font-bold text-xs text-gray-400"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    {{prop.data.name}}
                </template>

            </Column>
             <Column header="Tracking Key"  :sortable="true" v-if="store.isListView()" class="font-bold text-xs text-gray-400">

                 <template #body="prop">
                         {{prop.data.tracking_key}}
                 </template>
             </Column>
             <Column header="Tracking Value"  :sortable="true" class="font-bold text-xs text-gray-400">

                 <template #body="prop">
                     <Button @click="useVaah.copy(prop.data.tracking_value)">
                         {{prop.data.tracking_value}}
                     </Button>
                 </template>
             </Column>
             <Column  header="Orders Count" :sortable="true" class="font-bold text-xs text-gray-400">
                 <template #body="prop">
                     <div class="p-inputgroup justify-content-center">

                 <Tag severity="info" class="p-inputgroup-addon cursor-pointer"  @click="store.toView(prop.data)">{{ store.distinctOrdersCount(prop.data.orders) }}</Tag>
                     </div>
                 </template>
             </Column>

             <Column  header="Status" :sortable="true" class="font-bold text-xs text-gray-400">
                 <template #body="prop">
                     <Badge v-if="prop.data.status && prop.data.status.name == 'Delivered'"
                            severity="success" class="!text-[#0E9F6E] !rounded-full !bg-[#0E9F6E1A] px-2 py-1"> {{prop.data.status.name}} </Badge>

                     <Badge v-else class="!text-[#E3A008] !rounded-full !bg-[#E3A0081A] px-2 py-1"
                            severity="warn"> {{prop.data.status?.name}}</Badge>

                 </template>
             </Column>
             <Column field="type" header="Is Trackable" :sortable="true" class="font-bold text-xs text-gray-400">

                 <template #body="prop">
                     <span v-if="prop.data.is_trackable === 1" style="padding-left: 5px;">
                       Yes

                         <Button class="p-button-tiny p-button-text"
                                 data-testid="shipments-table-to-view"
                                 v-tooltip.top="'Track Your Shipment'"
                                 :disabled="prop.data.is_trackable !== 1"
                                 @click="openLinkInNewTab(prop.data.tracking_url)"
                                 icon="pi pi-external-link"
                         />
                    </span>
                     <span v-else style="padding-left: 5px;">
                      No
                    </span>
                 </template>

             </Column>
                <Column field="updated_at" header="Created" class="font-bold text-xs text-gray-400"
                        v-if="store.isListView()"
                        style="width:100px;"
                        :sortable="true">

                    <template #body="prop">
                        {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}
                    </template>

                </Column>



            <Column field="actions" style="width:150px;" class="font-bold text-xs text-gray-400"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup justify-content-center gap-2 items-center">


                        <Button class="p-button-tiny p-button-text"
                                data-testid="shipments-table-to-view"
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
                                data-testid="shipments-table-to-edit"
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
                                data-testid="shipments-table-action-trash"
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
                                data-testid="shipments-table-action-restore"
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

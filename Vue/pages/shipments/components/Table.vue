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

            <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true">
            </Column>


            <Column field="name" header="Name"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    {{prop.data.name}}
                </template>

            </Column>
             <Column header="Tracking Key"  :sortable="true" v-if="store.isListView()">

                 <template #body="prop">
                         {{prop.data.tracking_key}}
                 </template>
             </Column>
             <Column header="Tracking Value"  :sortable="true">

                 <template #body="prop">
                     <Button @click="useVaah.copy(prop.data.tracking_value)">
                         {{prop.data.tracking_value}}
                     </Button>
                 </template>
             </Column>
             <Column  header="Orders Count" :sortable="true">
                 <template #body="prop">
                     <div class="p-inputgroup justify-content-center">

                 <Tag severity="info" class="p-inputgroup-addon cursor-pointer"  @click="store.toView(prop.data)">{{ store.distinctOrdersCount(prop.data.orders) }}</Tag>
                     </div>
                 </template>
             </Column>

             <Column  header="Status" :sortable="true">
                 <template #body="prop">
                     <Badge v-if="prop.data.status && prop.data.status.name == 'Delivered'"
                            severity="success"> {{prop.data.status.name}} </Badge>

                     <Badge v-else class="min-w-max"
                            severity="warn"> {{prop.data.status?.name}}</Badge>

                 </template>
             </Column>
             <Column field="type" header="Is Trackable" :sortable="true">

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
                <Column field="updated_at" header="Created"
                        v-if="store.isListView()"
                        style="width:100px;"
                        :sortable="true">

                    <template #body="prop">
                        {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}
                    </template>

                </Column>



            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup justify-content-center">


                        <Button class="p-button-tiny p-button-text"
                                data-testid="shipments-table-to-view"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="shipments-table-to-edit"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="shipments-table-action-trash"
                                v-if="store.isListView() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


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

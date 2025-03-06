<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useOrderStore} from '../../../stores/store-orders'
import OrderItems from "./OrderItems.vue";
import {useRootStore} from "../../../stores/root";
import {onMounted} from "vue";

const store = useOrderStore();
const useVaah = vaah();
const root=useRootStore();

onMounted(async () => {
    store.assets_is_fetching=true;
    store.getAssets();
});

</script>

<template>

    <div v-if="store.list">
        <!--table-->
        <DataTable v-model:selection="store.action.items"
                   :rowClass="(rowData) => rowData.id === store.item?.id ? 'bg-yellow-100' : ''"
                   :value="store.list.data"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   dataKey="id"
                   responsiveLayout="scroll"
                   stripedRows>

            <Column v-if="store.isListView()"
                    headerStyle="width: 3em"
                    selectionMode="multiple">
            </Column>

            <Column :sortable="true" :style="{width: store.getIdWidth()}" field="id" header="ID">
            </Column>

            <Column :sortable="true" field="user.display_name"
                    header="User">
                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           severity="danger"
                           value="Trashed"></Badge>
                    <Badge v-if="prop.data.user == null"
                           severity="danger"
                           value="Trashed"></Badge>
                    <span v-else>
                    {{ prop.data.user?.display_name }}<br><Button class="p-button-tiny p-button-text p-0 mr-2"
                                                                  data-testid="taxonomies-table-to-edit"

                                                                  icon="pi pi-copy"
                                                                  @click="useVaah.copy(prop.data.user?.email)"
                    > {{ prop.data.user?.email }}</Button>
                    </span>
                    {{ prop.data.name }}
                </template>
            </Column>
            <Column v-if="store.isListView()" :sortable="true" field="user.phone"
                    header="Mobile No.">
                <template #body="prop">
                    {{ prop.data.user?.phone }}
                </template>
            </Column>
            <Column :sortable="false" field="items"
                    header="Order Items">
                <template #body="prop">
                    <div class="p-inputgroup">
                          <span v-tooltip.top="'View Order Items'" class="p-inputgroup-addon cursor-pointer"
                                @click="store.openOrderItems(prop.data)">
                             <b>
                                {{ prop.data.items_count }}
                            </b>
                         </span>
                        <Button
                            v-tooltip.top="'Create Payment'"
                            :disabled="parseFloat(prop.data.paid) >= parseFloat(prop.data.amount)"
                            icon="pi pi-plus"
                            severity="info"
                            size="small"
                            @click="store.toOrderPayment(prop.data.id)"
                        />
                    </div>
                </template>
            </Column>


            <Column v-if="store.isListView()" :sortable="true"
                    field="payable"
                    header="Payable">
                <template #body="prop">
                    <Badge severity="info"><span v-html="store.assets?.store_default_currency"></span>{{ prop.data.payable }}</Badge>
                </template>
            </Column>

            <Column v-if="store.isListView()" :sortable="true"
                    field="paid"
                    header="Paid">
                <template #body="prop">
                    <Badge v-if="prop.data.paid == 0"
                           severity="danger"
                           ><span v-html="store.assets?.store_default_currency"></span>0</Badge>
                    <Badge v-else-if="prop.data.paid > 0"
                           severity="info"><span v-html="store.assets?.store_default_currency"></span>{{ prop.data.paid }}
                    </Badge>
                </template>
            </Column>

            <Column v-if="store.isListView()" :sortable="true"
                    header="Order Status">
                <template #body="prop">
                    <Badge :severity="prop.data.order_status === 'Completed' ? 'success' : ''" class="min-w-max">
                        {{ prop.data.order_status }}
                    </Badge>
                </template>
            </Column>


            <Column v-if="store.isListView()" :sortable="true"
                    header="Payment Status">
                <template #body="prop">
                    <template v-if="prop.data.order_payment_status">
                        <Badge v-if="prop.data.order_payment_status.slug === 'paid'" severity="success">
                            {{ prop.data.order_payment_status.name }}
                        </Badge>
                        <Badge v-else-if="prop.data.order_payment_status.slug === 'partially-paid'" severity="info">
                            {{ prop.data.order_payment_status.name }}
                        </Badge>
                        <Badge v-else severity="danger">
                            {{ prop.data.order_payment_status.name }}
                        </Badge>
                    </template>
                    <template v-else>
                        <Badge severity="danger">

                        </Badge>
                    </template>
                </template>
            </Column>

            <Column v-if="store.isListView()"
                    :sortable="true" header="Shipping Status">
                <template #body="prop">
                    <Badge
                        :severity="prop.data.order_shipment_status === 'Delivered' ? 'success' : 'warn'"
                        class="min-w-max"
                    >
                        {{ prop.data.order_shipment_status }}
                    </Badge>
                </template>
            </Column>


            <Column :header="store.getActionLabel()" :style="{width: store.getActionWidth() }"
                    field="actions"
                    style="width:150px;">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                class="p-button-tiny p-button-text"
                                data-testid="orders-table-to-view"
                                icon="pi pi-eye"
                                @click="store.toView(prop.data)"/>

                        <Button v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                class="p-button-tiny p-button-text"
                                data-testid="orders-table-to-edit"
                                icon="pi pi-pencil"
                                @click="store.toEdit(prop.data)"/>

                        <Button v-if="store.isListView() && !prop.data.deleted_at"
                                v-tooltip.top="'Trash'"
                                class="p-button-tiny p-button-danger p-button-text"
                                data-testid="orders-table-action-trash"
                                icon="pi pi-trash"
                                @click="store.itemAction('trash', prop.data)"/>


                        <Button v-if="store.isListView() && prop.data.deleted_at"
                                v-tooltip.top="'Restore'"
                                class="p-button-tiny p-button-success p-button-text"
                                data-testid="orders-table-action-restore"
                                icon="pi pi-replay"
                                @click="store.itemAction('restore', prop.data)"/>


                    </div>

                </template>


            </Column>


        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator v-model:rows="store.query.rows"
                   :rowsPerPageOptions="store.rows_per_page"
                   :totalRecords="store.list.total"
                   class="bg-white-alpha-0 pt-2"
                   @page="store.paginate($event)">
        </Paginator>
        <!--/paginator-->
        <OrderItems/>
    </div>

</template>

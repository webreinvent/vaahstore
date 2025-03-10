<script setup>
import { vaah } from "../../../vaahvue/pinia/vaah";
import { useOrderStore } from "../../../stores/store-orders";
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
    <Card>
      <template #content>
        <!--table-->
        <DataTable
          v-model:selection="store.action.items"
          :rowClass="
            (rowData) => (rowData.id === store.item?.id ? 'bg-yellow-100' : '')
          "
          :value="store.list.data"
          class="p-datatable-sm p-datatable-hoverable-rows !bg-gray-50"
          dataKey="id"
          responsiveLayout="scroll"
        >
          <Column
            v-if="store.isListView()"
            headerStyle="width: 3em"
            selectionMode="multiple"
          >
          </Column>

          <Column
            :sortable="true"
            :style="{ width: store.getIdWidth() }"
            field="id"
            header="ID"
            class="font-bold text-xs text-gray-400"
          >
          </Column>

          <Column
            :sortable="true"
            field="user.display_name"
            header="User"
            class="font-bold text-xs text-gray-400"
          >
            <template #body="prop">
              <Badge
                v-if="prop.data.deleted_at"
                severity="danger"
                value="Trashed"
              ></Badge>
              <Badge
                v-if="prop.data.user == null"
                severity="danger"
                value="Trashed"
              ></Badge>
              <span v-else>
                {{ prop.data.user?.display_name }}<br /><Button
                  class="p-button-tiny p-button-text p-0 mr-2 !font-bold !text-xs !text-gray-300"
                  data-testid="taxonomies-table-to-edit"
                  :pt="{ root: { class: '!bg-none !p-0' } }"
                  icon="pi pi-copy"
                  @click="useVaah.copy(prop.data.user?.email)"
                >
                  {{ prop.data.user?.email }}</Button
                >
              </span>
              {{ prop.data.name }}
            </template>
          </Column>
          <Column
            v-if="store.isListView()"
            :sortable="true"
            field="user.phone"
            class="font-bold text-xs text-gray-400"
            header="Mobile No."
          >
            <template #body="prop">
              {{ prop.data.user?.phone }}
            </template>
          </Column>
          <Column
            :sortable="false"
            field="items"
            class="font-bold text-xs text-gray-400"
            header="Order Items"
          >
            <template #body="prop">
              <div
                class="p-inputgroup !rounded-lg border !border-gray-200 p-2 flex justify-between items-center"
              >
                <span
                  v-tooltip.top="'View Order Items'"
                  class="cursor-pointer"
                  @click="store.openOrderItems(prop.data)"
                >
                  <p class="font-bold text-xs text-gray-400">
                    {{ prop.data.items_count }}
                  </p>
                </span>
                <Button
                  v-tooltip.top="'Create Payment'"
                  :disabled="
                    parseFloat(prop.data.paid) >= parseFloat(prop.data.amount)
                  "
                  :pt="{ icon: { class: '!text-[8px]' } }"
                  icon="pi pi-plus"
                  class="quantity-button !rounded"
                  severity="info"
                  @click="store.toOrderPayment(prop.data.id)"
                />
              </div>
            </template>
          </Column>

          <Column
            v-if="store.isListView()"
            :sortable="true"
            class="font-bold text-xs text-gray-400"
            field="payable"
            header="Payable"
          >
            <template #body="prop"> <span v-html="store.assets?.store_default_currency"></span>{{ prop.data.payable }} </template>
          </Column>

          <Column
            v-if="store.isListView()"
            :sortable="true"
            class="font-bold text-xs text-gray-400"
            field="paid"
            header="Paid"
          >
            <template #body="prop"> <span v-html="store.assets?.store_default_currency"></span>{{ prop.data.paid }} </template>
          </Column>

          <Column
            v-if="store.isListView()"
            :sortable="true"
            class="font-bold text-xs text-gray-400"
            header="Order Status"
          >
            <template #body="prop">
              <span
                :class="{
                  'text-[#0E9F6E] rounded-full bg-[#0E9F6E1A] px-2 py-1':
                    prop.data.order_status === 'Completed',
                  'text-purple-600 rounded-full bg-purple-100 px-2 py-1':
                    prop.data.order_status === 'Placed',
                }"
              >
                {{ prop.data.order_status }}
              </span>
            </template>
          </Column>



          <Column
            :header="store.getActionLabel()"
            :style="{ width: store.getActionWidth() }"
            class="font-bold text-xs text-gray-400"
            field="actions"
            style="width: 150px"
          >
            <template #body="prop">
              <div class="p-inputgroup flex gap-3">
                <Button
                  v-tooltip.top="'View'"
                  :disabled="
                    $route.path.includes('view') &&
                    prop.data.id === store.item?.id
                  "
                  class="p-button-tiny p-button-text"
                  data-testid="orders-table-to-view"
                  :pt="{ root: { class: '!bg-none !p-0' } }"
                  @click="store.toView(prop.data)"
                >
                  <Icon
                    icon="tabler:eye"
                    width="20"
                    height="20"
                    class="text-gray-400"
                  />
                </Button>

                <Button
                  v-tooltip.top="'Update'"
                  :disabled="
                    $route.path.includes('form') &&
                    prop.data.id === store.item?.id
                  "
                  class="p-button-tiny p-button-text"
                  data-testid="orders-table-to-edit"
                  :pt="{ root: { class: '!bg-none !p-0' } }"
                  @click="store.toEdit(prop.data)"
                >
                  <Icon
                    icon="mdi:pencil-outline"
                    width="20"
                    height="20"
                    class="text-gray-400"
                  />
                </Button>

                <Button
                  v-if="store.isListView() && !prop.data.deleted_at"
                  v-tooltip.top="'Trash'"
                  class="p-button-tiny p-button-danger p-button-text"
                  data-testid="orders-table-action-trash"
                  :pt="{ root: { class: '!bg-none !p-0' } }"
                  @click="store.itemAction('trash', prop.data)"
                >
                  <Icon
                    icon="mdi:trash-can-outline"
                    width="20"
                    height="20"
                    class="text-[#E02424]"
                  />
                </Button>

                <Button
                  v-if="store.isListView() && prop.data.deleted_at"
                  v-tooltip.top="'Restore'"
                  class="p-button-tiny p-button-success p-button-text"
                  data-testid="orders-table-action-restore"
                  icon="pi pi-replay"
                  @click="store.itemAction('restore', prop.data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator
          v-model:rows="store.query.rows"
          :rowsPerPageOptions="store.rows_per_page"
          :totalRecords="store.list.total"
          class="bg-white-alpha-0 pt-2"
          @page="store.paginate($event)"
        >
        </Paginator>

        <OrderItems />
      </template>
    </Card>
    <!--/paginator-->
  </div>
</template>

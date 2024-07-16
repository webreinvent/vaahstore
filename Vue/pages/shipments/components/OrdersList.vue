<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {computed, ref, watch} from "vue";
import {useShipmentStore} from "../../../stores/store-shipments";

const store = useShipmentStore();
const useVaah = vaah()


</script>


<template>
    <Sidebar v-model:visible="store.show_orders_panel"  header="Order Name:" position="right" style="width:600px;">
        <template #header>
            <h2 style="font-weight: bold;"  >Order Name: Order 1</h2>
        </template>



        <DataTable :value="store.order_list_table_with_vendor"
                   dataKey="id"
                   :rows="10"
                   :paginator="true"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :nullSortOrder="-1"
                   showGridlines
                   v-model:selection="store.action.items"
                   responsiveLayout="scroll">



            <Column field="id" header="Order ID"  >
            </Column>

            <Column  header="Item Name"
                     class="overflow-wrap-anywhere"
            >
                <template #body="prop">
                    {{prop.data.name}}
                </template>
            </Column>

            <Column  header="Vendor Name"
                     class="overflow-wrap-anywhere"
            >
                <template #body="prop">
                    {{prop.data.vendor_name}}
                </template>
            </Column>
            <Column  header="Quantity "
                     class="overflow-wrap-anywhere"
            >
                <template #body="prop">
                    {{prop.data.available_quantity}}
                </template>
            </Column>
            <Column  header="Status"
                     class="overflow-wrap-anywhere"
            >
                <template #body="prop">

                    <Badge  v-if="prop.data.status === 'Out For Delivery'" severity="success">
                                    {{ prop.data.status }}
                                </Badge>

                    <Badge v-else severity="warn">
                                    {{ prop.data.status }}
                                </Badge>
                </template>
            </Column>
            <template #empty>
                <div class="text-center py-3">
                    No records found.
                </div>
            </template>

        </DataTable>


    </Sidebar>
</template>



<style scoped>

</style>

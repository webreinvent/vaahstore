<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {computed, ref, watch} from "vue";
import {useOrderStore} from "../../../stores/store-orders";

const store = useOrderStore();
const useVaah = vaah()


</script>


<template>
    <Drawer v-model:visible="store.show_orders_panel"  header="Order Name:" position="right" style="width:700px;">
        <template #header>
            <h2 style="font-weight: bold;" v-if="store.item"  >{{ store.order_name }}
            </h2>
        </template>



        <DataTable :value="store.order_list_table_with_vendor"
                   dataKey="id"
                   :rows="10"
                   :paginator="true"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :nullSortOrder="-1"
                   showGridlines
                   v-model:selection="store.action.items"
                   scrollHeight="700px"
                   responsiveLayout="scroll">



            <Column field="id" header="Item ID"  >
            </Column>

            <Column  header="Item Name"
                     class="overflow-wrap-anywhere"
            >
                <template #body="prop">

                    {{prop.data.product?.name  + '-' +prop.data.product_variation?.name}}
                </template>
            </Column>

          <Column  header="Vendor"
                     class="overflow-wrap-anywhere"
            >
                <template #body="prop">
                     <span style="color: #1d4ed8;  cursor: pointer;">
                    {{prop.data.vendor?.name}}
                         </span>
                </template>
            </Column>
            <Column  header=" Quantity "
                     class="overflow-wrap-anywhere"
            >
                <template #body="prop">
                    {{prop.data.quantity}}
                </template>
            </Column>
            <Column  header="Shipped  "
                     class="overflow-wrap-anywhere"
            >
                <template #body="prop">
                    {{prop.data.shipped_quantity}}
                </template>
            </Column>
            <Column  header="Pending"
                     class="overflow-wrap-anywhere"
            >
                <template #body="prop">
                    {{prop.data.quantity-prop.data.shipped_quantity}}
                </template>
            </Column>

            <template #empty>
                <div class="text-center py-3">
                    No records found.
                </div>
            </template>

        </DataTable>


    </Drawer>
</template>



<style scoped>

</style>

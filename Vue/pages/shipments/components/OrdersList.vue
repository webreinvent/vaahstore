<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {computed, ref, watch} from "vue";
import {useShipmentStore} from "../../../stores/store-shipments";

const store = useShipmentStore();
const useVaah = vaah()


</script>


<template>
    <Sidebar v-model:visible="store.show_orders_panel"  header="Product Price With Vendors" position="right" style="width:600px;">
        <template #header>
            <h2 style="font-weight: bold;" v-if="store.item" >Shipment Orders:</h2>
        </template>






        <DataTable  :value="store.order_list" style="border: 1px solid #ccc;margin-top:20px;"
                   :rows="20"
                   :paginator="true"
                   class="p-datatable-sm p-datatable-hoverable-rows">

            <Column field="name" header="Order" style="border: 1px solid #ccc;">
                <template #body="props">
                    <div  >
                        {{props.data.name}}

                    </div>
                </template>
            </Column>

            <Column  header="Shipment Order Items" style="border: 1px solid #ccc;" :sortable="false">
                <template #body="prop">
                    <div class="p-inputgroup justify-content-center">
            <span class="p-inputgroup-addon cursor-pointer"
                  v-tooltip.top="'Track Order Shipment'"
                  @click="store.openOrdersPanel(prop.data)"
            >
                <b > 2</b>
            </span>
                    </div>
                </template>
            </Column>




            <column field="Action" header="Track Order Shipment" style="border:1px solid #ccc;">
                <template #body="props">
                    <Button class="p-button-tiny p-button-text"
                            data-testid="shipments-table-to-view"
                            v-tooltip.top="'View'"
                            @click="store.toView(prop.data)"
                            icon="pi pi-eye" />
                </template>
            </column>
            <template #empty="prop">

                <div  style="text-align: center;font-size: 12px; color: #888;">No records found.</div>

            </template>
        </DataTable>
    </Sidebar>
</template>



<style scoped>

</style>

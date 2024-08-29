<script setup>
import {computed, defineProps, inject, onMounted, ref, watch} from 'vue';
import { vaah } from '../../../vaahvue/pinia/vaah';
import {useShipmentStore} from "../../../stores/store-shipments";
import {useRoute} from "vue-router";

const store = useShipmentStore();
const useVaah = vaah();
const injectedCategories = ref({ shipment_item_id: [] });
const header = ref('');
const dialogRef = inject('dialogRef');
const route = useRoute();
onMounted(async () => {

    if (dialogRef && dialogRef.value && dialogRef.value.data) {
        injectedCategories.value = dialogRef.value.data;
        header.value = dialogRef.value.options.props.header;
        await store.getShipmentItemList(dialogRef.value.data.shipment_item_id)
        store.updatePendingQuantity();
    }
})

</script>


<template>
    <div class="flex justify-content-end mb-2">
        <Button label="Save"
                class="p-button-sm"
                v-if="store.item && store.item.id"
                data-testid="shipments-save"
                @click="store.saveShippedItemQuanity('update-shipped-item-quantity',store.shipped_items_list,route.params.id)"
                icon="pi pi-save"/>
    </div>

    <div style="width: 40rem" class="flex justify-content-between ml-8">
        <b>Total Shipping Quantity: {{ store.total_quantity_to_be_shipped }}</b>
        <b>Total Shipped Quantity: {{ store.total_shipped_quantity }}</b>
        <b>Total Pending Quantity: {{ store.total_quantity_to_be_shipped-store.total_shipped_quantity }}</b>
    </div>

    <div class="card">
        <Message :closable="false" severity="warn">This will impact quantity on other shipments as well.</Message>
    </div>
    <div v-if="store.item">
        <DataTable v-model:editingRows="store.editingRows"
                   :rows="10"
                   :rowClass="store.setShippedRowClass"
                   :paginator="true"
                   :value="store.shipped_items_list"
                   editMode="row"
                   dataKey="id"
                   @row-edit-save="store.onRowEditSave" >
            <Column header="Sr No" >
                <template #body="props">
                    {{ props.index + 1 }}
                </template>
            </Column>
            <Column  header="Shipment ID">
                <template #body="props">
                    {{ props.data.vh_st_shipment_id }}
                </template>
            </Column>
            <Column  header="Total Quantity" >
                <template #body="props">
                    {{ props.data.total_quantity }}
                </template>
            </Column>
            <Column field="quantity" header="Shipping Quantity" >
                <template #editor="{ data, field ,index}">
                    <InputNumber
                        v-model="data[field]"
                        @input="store.updatePendingQuantity(data, index)"
                        data-testid="edit-shipments-quantity"
                        mode="decimal"
                        :min="0"
                        :max="store.getMaxValue(index)"
                        fluid
                    />
                </template>
            </Column>

            <Column field="pending" header="Pending Quantity" >

                <template #editor="{ data, field }">
                    <InputText readonly v-model="data[field]" fluid />

                </template>
            </Column>

            <Column header="Click To Edit" :rowEditor="true" style="width: 10%; min-width: 8rem" bodyStyle="text-align:center"></Column>
        </DataTable>

    </div>
</template>

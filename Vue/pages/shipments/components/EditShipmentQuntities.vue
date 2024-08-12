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
onMounted(() => {

    if (dialogRef && dialogRef.value && dialogRef.value.data) {
        injectedCategories.value = dialogRef.value.data;
        header.value = dialogRef.value.options.props.header;
        store.getShipmentItemList(dialogRef.value.data.shipment_item_id)
    }
})





const rowClass = (data) => {
    return {
        '!bg-primary !text-primary-contrast': data.vh_st_shipment_id === Number(route.params.id)
    };
};
const rowStyle = (data) => {
    return data.vh_st_shipment_id === Number(route.params.id) ? { fontWeight: 'bold', fontStyle: 'italic' } : {};
};

const totalShipped = computed(() => {
    if (Array.isArray(store.shipped_items_list)) {
        return store.shipped_items_list.reduce((total, item) => total + (parseFloat(item.quantity) || 0), 0);
    }
    return 0;
});
watch(totalShipped, (newTotal) => {
    store.item.updated_total_shipped_quantity = newTotal;
});
</script>


<template>
    <div class="flex justify-content-end">
        <Button label="Save"
                class="p-button-sm"
                v-if="store.item && store.item.id"
                data-testid="shipments-save"
                @click="store.saveShippedItemQuanity('save-updated-shipped-quantity',store.shipped_items_list)"
                icon="pi pi-save"/>
    </div>

    <div class="card">
        <Message :closable="false" severity="warn">This will impact quantity on other shipments as well.</Message>
    </div>
    <div v-if="store.item">
        <DataTable v-model:editingRows="store.editingRows" :rows="10"  :rowClass="store.rowClass" :rowStyle="rowStyle"
                   :paginator="true" :value="store.shipped_items_list" editMode="row" dataKey="id" @row-edit-save="store.onRowEditSave"
                   :pt="{

                column: {
                    bodycell: ({ state }) => ({
                        style:  state['d_editing']&&'padding-top: 0.75rem; padding-bottom: 0.75rem'
                    })
                },

            }"
        >
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
                <template #footer="slotProps">
                    <div class="ml-2">
                         Total Item Quantity: {{ store.total_quantity_to_be_shipped }}
                    </div>
                </template>
                <template #body="props">
                    {{ props.data.total_quantity }}
                </template>
            </Column>
            <Column field="quantity" header="Shipping Quantity" >
                <template #footer="slotProps">
                    <div class="ml-2">
                        Updated Shipping Quantity: {{ store.item.updated_total_shipped_quantity }}
                    </div>
                </template>
                <template #editor="{ data, field ,index}">
                    <InputNumber
                        v-model="data[field]"
                        @input="store.updatePendingQuantity(data, index)"
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

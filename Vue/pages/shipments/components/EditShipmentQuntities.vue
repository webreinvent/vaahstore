<script setup>
import {computed, defineProps, inject, onMounted, ref} from 'vue';
import { vaah } from '../../../vaahvue/pinia/vaah';
import {useShipmentStore} from "../../../stores/store-shipments";

const store = useShipmentStore();
const useVaah = vaah();
const injectedCategories = ref({ shipment_item_id: [] });
const header = ref('');
const dialogRef = inject('dialogRef');
onMounted(() => {

    if (dialogRef && dialogRef.value && dialogRef.value.data) {
        injectedCategories.value = dialogRef.value.data;
        header.value = dialogRef.value.options.props.header;
        store.getShipmentItemList(dialogRef.value.data.shipment_item_id)
    }
})
// const categoriesData = computed(() => {
//     const search = (store.item.search_category || '').toLowerCase().trim();
//     return injectedCategories.value.categories.filter(category =>
//         category.name.toLowerCase().includes(search)
//     );
// });


const editingRows = ref([]);
const onRowEditSave = (event) => {
    let { newData, index } = event;

    store.shipped_items_list[index] = newData;
    console.log( store.shipped_items_list)
};
</script>


<template>
    <div class="flex justify-content-end">
        <Button label="Save"
                class="p-button-sm"
                v-if="store.item && store.item.id"
                data-testid="shipments-save"
                @click="store.saveShippedItemQuanity('save')"
                icon="pi pi-save"/>
    </div>

    <div class="card">
        <Message :closable="false" severity="warn">This will impact quantity on other shipments as well.</Message>
    </div>
    <div v-if="store.item">
<!--        <DataTable-->
<!--            :value="store.shipped_items_list"-->
<!--            :rows="10"-->
<!--            :paginator="true"-->
<!--            style="border: 1px solid #ccc; margin-top: 20px;"-->
<!--            class="p-datatable-sm p-datatable-hoverable-rows"-->
<!--        >-->
<!--            <Column header="Sr No" style="border: 1px solid #ccc; width: 10px">-->
<!--                <template #body="props">-->
<!--                    {{ props.index + 1 }}-->
<!--                </template>-->
<!--            </Column>-->
<!--            <Column  header="Shipment ID" style="border: 1px solid #ccc;">-->
<!--                <template #body="props">-->
<!--                    {{ props.data.vh_st_shipment_id }}-->
<!--                </template>-->
<!--            </Column>-->
<!--            <Column  header="Total Quantity" style="border: 1px solid #ccc;">-->
<!--                <template #body="props">-->
<!--                    {{ props.data.total_quantity }}-->
<!--                </template>-->
<!--            </Column>-->
<!--            <Column  header="Shipped Quantity" style="border: 1px solid #ccc;">-->
<!--                <template #body="props">-->
<!--&lt;!&ndash;                    {{ props.data.quantity }}&ndash;&gt;-->

<!--                    <div class="p-inputgroup w-5rem max-w-full" >-->
<!--                        <InputNumber-->

<!--                            v-model="props.data.quantity"-->
<!--                            buttonLayout="horizontal"-->
<!--                            :min="0"-->
<!--                            class="w-full"-->
<!--                            placeholder="Enter quantity"-->

<!--                        ></InputNumber>-->
<!--                    </div>-->
<!--                </template>-->


<!--            </Column>-->

<!--            <Column  header="Pending Quantity" style="border: 1px solid #ccc;">-->
<!--                <template #body="props">-->
<!--                    {{ props.data.pending }}-->
<!--                </template>-->
<!--            </Column>-->
<!--            <Column field="created_at" header="Created At"-->
<!--                    style="border: 1px solid #ccc;">-->

<!--                <template #body="prop">-->
<!--                    {{useVaah.toLocalTimeShortFormat(prop.data.created_at)}}-->
<!--                </template>-->

<!--            </Column>-->
<!--&lt;!&ndash;            <Column header="Action"  style="border: 1px solid #ccc;">-->
<!--                <template #body="props">-->
<!--                    <Button class="p-button-tiny p-button-danger p-button-text"-->
<!--                            data-testid="products-product_categories-action-remove"-->
<!--                            @click="removeCategory(props.data)"-->
<!--                            v-tooltip.top="'Remove'"-->
<!--                            icon="pi pi-trash" />-->
<!--                </template>-->
<!--            </Column>&ndash;&gt;-->

<!--            <template #empty="prop">-->
<!--                <div style="text-align: center; font-size: 12px; color: #888;">-->
<!--                    No category found.-->
<!--                </div>-->
<!--            </template>-->
<!--        </DataTable>-->



        <DataTable v-model:editingRows="editingRows" :rows="10"
                   :paginator="true" :value="store.shipped_items_list" editMode="row" dataKey="id" @row-edit-save="onRowEditSave"
                   :pt="{

                column: {
                    bodycell: ({ state }) => ({
                        style:  state['d_editing']&&'padding-top: 0.75rem; padding-bottom: 0.75rem'
                    })
                }
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
                <template #body="props">
                    {{ props.data.total_quantity }}
                </template>
            </Column>
            <Column field="quantity" header="Shipped Quantity" >
                <template #footer="slotProps">
                    <div class="ml-2">
                        Total Shipped: {{ store.calculateTotalShippedas() }}
                    </div>
                </template>
                <template #editor="{ data, field }">
                    <InputText v-model="data[field]" fluid />

                </template>
            </Column>
            <Column field="pending" header="Pending Quantity" >

                <template #editor="{ data, field }">
                    <InputText v-model="data[field]" fluid />

                </template>
            </Column>

            <Column :rowEditor="true" style="width: 10%; min-width: 8rem" bodyStyle="text-align:center"></Column>
        </DataTable>

    </div>
</template>

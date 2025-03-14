<script setup>
import {computed, onMounted, ref, watch, watchEffect} from "vue";
import { useShipmentStore } from '../../stores/store-shipments'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';
import { useDialog } from "primevue/usedialog";
import EditShipmentQuntities from "../shipments/components/EditShipmentQuntities.vue";

const store = useShipmentStore();
const route = useRoute();

onMounted(async () => {
    /**
     * Fetch the record from the database
     */
    if((!store.item || Object.keys(store.item).length < 1)
        && route.params && route.params.id)
    {
        await store.getItem(route.params.id);
    }

    await store.getFormMenu();
});

//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};
//--------/form_menu



const trackableSelection = computed(() => {
    const isEmpty = !store.item?.tracking_key || !store.item?.tracking_value || !store.item?.tracking_url;
    return isEmpty ? 0 : 1;
});

watchEffect(() => {
    // Check if store.item exists before assigning is_trackable
    if (store.item) {
        store.item.is_trackable = trackableSelection.value;
    }
});

const dialog = useDialog();
const openShippingQuantityModal = (shipment_id,product,shipped_item_id) => {
    const dialogRef = dialog.open(EditShipmentQuntities, {
        props: {
            header: product,

            style: {
                width: '70vw',
                borderRadius: '10px',
                overflow: 'hidden'
            },
            breakpoints:{
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true
        },
        data : {'shipment_item_id' : shipped_item_id},
    });
}
</script>
<template>

    <div>

        <Panel class="is-small">

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <span v-if="store.item && store.item.id">
                            Update
                        </span>
                        <span v-else>
                            Create
                        </span>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">

                    <Button class="p-button-sm"
                            v-tooltip.left="'View'"
                            v-if="store.item && store.item.id"
                            data-testid="shipments-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="shipments-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="shipments-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="shipments-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="shipments-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="mt-2">

                <Message severity="error"
                         class="p-container-message mb-3"
                         :closable="false"
                         icon="pi pi-trash"
                         v-if="store.item.deleted_at">

                    <div class="flex align-items-center justify-content-between">

                        <div class="">
                            Deleted {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="articles-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>
                <VhField label="Name">
                    <InputText class="w-full"
                               name="products-name"
                               data-testid="products-name"
                               @update:modelValue="store.watchItem"
                               placeholder="Enter Name"
                               v-model="store.item.name"/>
                </VhField>
                <VhField label="Orders">
                    <div class="flex justify-content-center">
                        <AutoComplete
                            v-model="store.item.orders"
                            :suggestions="store.order_suggestion_list"
                            multiple

                            @complete="store.searchOrders($event)"
                            @item-unselect="store.removeOrders($event)"
                            optionLabel="user_name"
                            placeholder="Select orders"
                            display="chip"

                            class="w-full relative"
                        />

                        <Button
                            label="Add"
                            class="p-button-sm ml-1"
                            @click="store.addOrdersToShipment"
                        />
                    </div>
                </VhField>
                <template v-for="(order, index) in store.order_list_tables" :key="index">
                    <!--                    <VhField>-->
                    <div class="mb-4">
                        <div class="mt-2 mb-2 p-1" style="font-size: 16px"><b>{{order.name}}</b>
                            <i style="font-size: 15px" @click="store.removeOrderDetail(index)" class="pi pi-times-circle cursor-pointer text-danger  ml-2"></i>
                        </div>
                        <DataTable
                            :value="order.items"
                            rowGroupMode="subheader"
                            :groupRowsBy="order.name"
                            sortMode="single"
                            :sortField="order.name"
                            showGridlines
                            :sortOrder="1"
                            scrollable
                            scrollHeight="400px"
                        >
                            <Column field="name" header="Order Item">
                                <template #footer="slotProps">
                                    Total
                                </template>
                            </Column>

                            <Column  field="quantity" header="Quantity">
                                <template #footer="slotProps">
                                    <div class="ml-2">
                                        {{ store.calculateTotalQuantity(order.items) }}
                                    </div>
                                </template>
                            </Column>

                            <Column field="shipped" header="Shipped">
                                <template #footer="slotProps">
                                    <div class="ml-2">
                                        {{ store.calculateTotalShipped(order.items) }}
                                    </div>
                                </template>
                            </Column>
                            <Column field="pending" header="Pending">

                                <template #footer="slotProps">
                                    <div class="ml-2">
                                        {{ store.calculateTotalPending(order.items) }}
                                    </div>
                                </template>
                            </Column>

                            <template >
                                <Column header="To Be Shipped" class="overflow-wrap-anywhere">
                                    <template #body="prop">
                                        <div v-if="(!store.item.id || prop.data.exists_in_shipment === false)">
                                            <div class="p-inputgroup w-7rem max-w-full" v-if="prop.data.pending !== 0">
                                                <InputNumber
                                                    v-model="prop.data.to_be_shipped"
                                                    buttonLayout="horizontal"
                                                    :min="0"
                                                    class="w-full"
                                                    placeholder="Enter quantity"
                                                    @input="store.updateQuantities($event, index, prop.data, order)"
                                                />
                                            </div>

                                        </div>
                                        <div v-else-if="(store.item.id && prop.data.exists_in_shipment === true)">
                                            <InputNumber  v-if="prop.data.pending !== 0"
                                                          v-model="prop.data.to_be_shipped"
                                                          buttonLayout="horizontal"
                                                          :min="0"
                                                          class="w-full"
                                                          placeholder="Enter quantity"
                                                          @input="store.updateQuantities($event, index, prop.data, order)"
                                            />

                                        </div>
                                        <div v-else-if="store.item.id">
                                            <Button v-if="prop.data.exists_in_shipment !== false" class="p-button-tiny p-button-text"
                                                    data-testid="shipments-table-to-view"
                                                    v-tooltip.top="'View & Edit'"
                                                    @click="openShippingQuantityModal(store.item.id,prop.data.name,prop.data.id)"
                                                    icon="pi pi-pencil" />
                                        </div>
                                    </template>
                                </Column>




                            </template>

                        </DataTable>
                    </div>
                    <!--                    </VhField>-->
                </template>

                <VhField label="Tracking Url">
                    <div class="p-inputgroup ">
                        <InputText class="w-full"
                                   placeholder="Enter the tracking url"
                                   name="shipments-tracking-url"
                                   data-testid="shipments-tracking-url"
                                   v-model="store.item.tracking_url" required/>

                    </div>
                </VhField>

                <VhField label="Tracking Key">
                    <div class="p-inputgroup">
                        <InputText class="w-full"
                                   placeholder="Enter tracking key"
                                   name="sources-slug"
                                   data-testid="sources-slug"
                                   v-model="store.item.tracking_key" required/>

                    </div>
                </VhField>
                <VhField label="Tracking Value">
                    <div class="p-inputgroup">
                        <InputText class="w-full"
                                   placeholder="Enter tracking value"
                                   name="shipments-tracking-value"
                                   data-testid="shipments-tracking-value"
                                   v-model="store.item.tracking_value" required/>

                    </div>
                </VhField>

                <VhField label="Status">
                    <AutoComplete
                        value="id"
                        v-model="store.item.status"
                        @change="store.setStatus($event)"
                        class="w-full"
                        name="shipments-status"
                        :suggestions="store.status_suggestion"
                        @complete="store.searchStatus($event)"
                        placeholder="Select Status"
                        :dropdown="true" optionLabel="name"
                        data-testid="shipments-status"
                        forceSelection>
                    </AutoComplete>
                </VhField>

                <VhField label="Is Trackable">

                    <SelectButton v-model="trackableSelection" :options="store.options"
                                  optionLabel="name"
                                  optionValue="value"
                                  readonly
                                  aria-labelledby="basic" allowEmpty />
                </VhField>


            </div>
        </Panel>

    </div>
    <DynamicDialog  />
</template>

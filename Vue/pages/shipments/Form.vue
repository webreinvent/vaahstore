<script setup>
import {onMounted, ref, watch} from "vue";
import { useShipmentStore } from '../../stores/store-shipments'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


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




</script>
<template>

    <div class="col-8" >

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

                <VhField label="Select Order">
                    <div class="flex justify-content-center">
                        <AutoComplete
                            v-model="store.item.orders"
                            :suggestions="store.order_suggestion_list"
                            multiple
                            dropdown
                            @complete="store.searchOrders($event)"
                            optionLabel="name"
                            placeholder="Select Order"
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
                    <VhField>
                        <div class="mb-1"><b>{{order.name}}</b>
                            <i  @click="store.removeOrderDetail(index)" class="pi pi-times-circle text-danger ml-2"></i>
                        </div>
                        <DataTable
                            :value="order.items"
                            rowGroupMode="subheader"
                            :groupRowsBy="order.name"
                            sortMode="single"
                            :sortField="order.name"
                            :sortOrder="1"
                            scrollable
                            scrollHeight="400px"
                        >
                            <Column field="name" header="Name">
                                <template #footer="slotProps">
                                    Total
                                </template>
                            </Column>
                            <Column field="quantity" header="Quantity">
                                <template #footer="slotProps">
                                     {{ store.calculateTotalQuantity(order.items) }}
                                </template>
                            </Column>
                            <Column field="shipped" header="Shipped">
                                <template #footer="slotProps">
                                     {{ store.calculateTotalShipped(order.items) }}
                                </template>
                            </Column>
                            <Column field="pending" header="Pending">
                                <template #footer="slotProps">
                                    {{ store.calculateTotalPending(order.items) }}
                                </template>
                            </Column>
                            <Column  header="To Be Shipped" class="overflow-wrap-anywhere">
                                <template #body="prop" >
                                    <div class="p-inputgroup w-8rem max-w-full">
                                        <InputNumber
                                            v-model="prop.data.shipped"
                                            buttonLayout="horizontal"
                                            :min="0"
                                            :max="prop.data.quantity"
                                            @input="store.updateQuantities($event, index,prop.data,order)"
                                        ></InputNumber>
                                    </div>
                                </template>
                            </Column>


                        </DataTable>
                    </VhField>
                </template>



            </div>
        </Panel>

    </div>

</template>

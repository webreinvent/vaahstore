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
const selectedOrders = ref([]);
const orderListTables = ref([]);
const addOrdersToShipment = () => {
    store.orderListTables = store.item.orders.map(order => ({
        name: order.name,
        items: order.items // Assuming 'items' is an array of items for each order
    }));
};
const removeOrder = (removedOrder) => {
    const index = store.item.orders.findIndex(order => order.name === removedOrder.name);
    if (index !== -1) {
        store.item.orders.splice(index, 1);
        store.orderListTables.splice(index, 1);
    }
};
const updateQuantities = (event,index,item,order) => {
    // console.log(event.value)
    // const shipped = parseFloat(event.value) || 0;
    // item.shipped = shipped;
    // item.pending = item.quantity - shipped;
    //
    //
    // order.shipped = order.items.reduce((total, i) => total + i.shipped, 0);
    //
    // console.log('Updated quantities:', item, order);
    const shipped = parseFloat(event.value) || 0;

    if (shipped > item.quantity) {
        item.shipped = item.quantity;
    } else {
        item.shipped = shipped;
    }

    item.pending = item.quantity - item.shipped;
    if (item.pending < 0) {
        item.pending = 0;
    }

    // Update total shipped for the order
    order.shipped = order.items.reduce((total, i) => total + i.shipped, 0);

};


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


<!--                <VhField label="Select Order">-->
<!--                    <div class="flex justify-content-center">-->
<!--                        <AutoComplete-->
<!--                            v-model="store.item.orders"-->
<!--                            :suggestions="store.order_suggestion_list"-->
<!--                           multiple-->
<!--                            dropdown-->
<!--                            optionLabel="name"-->
<!--                            placeholder="Select Order"-->
<!--                            display="chip"-->
<!--                            @change = "store.addOrders()"-->
<!--                            @complete="store.searchOrders($event)"-->
<!--                            class="w-full relative" />-->
<!--                        <Button label="Add"-->
<!--                                class="p-button-sm ml-1"-->
<!--                                data-testid="articles-item-restore"-->
<!--                                @click="store.AddOrderToShipment(store.item.orders)">-->
<!--                        </Button>-->
<!--&lt;!&ndash;                        <div class="required-field hidden"></div>&ndash;&gt;-->
<!--                    </div>-->
<!--                </VhField>-->

<!--                <VhField >-->
<!--                <DataTable :value="store.order_list1" rowGroupMode="subheader" groupRowsBy="order.name" sortMode="single"-->
<!--                           sortField="order.name" :sortOrder="1" scrollable scrollHeight="400px" >-->
<!--&lt;!&ndash;                    <Column field="order.name" header="Representative"></Column>&ndash;&gt;-->
<!--                    <Column field="name" header="Name" ></Column>-->
<!--                    <Column field="quantity" header="Quantity" ></Column>-->
<!--                    <Column field="shipped" header="Shipped" ></Column>-->
<!--                    <Column field="pending" header="Pending" ></Column>-->

<!--                    <template #groupheader="slotProps">-->
<!--                        <div class="flex items-center gap-2">-->
<!--                            <span>{{ slotProps.data.order.name }}</span>-->
<!--                        </div>-->
<!--                    </template>-->

<!--                </DataTable>-->
<!--                </VhField>-->



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
                            @click="addOrdersToShipment"
                        />
                    </div>
                </VhField>
                <template v-for="(order, index) in store.orderListTables" :key="index">
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
                            <Column field="name" header="Name"></Column>
                            <Column field="quantity" header="Quantity"></Column>
                            <Column field="shipped" header="Shipped"></Column>
                            <Column field="pending" header="Pending">
<!--                                <template #body="prop">-->
<!--                                   {{prop.data.quantity-prop.data.shipped}}-->
<!--                                </template>-->
                            </Column>
                            <Column  header="To Be Shipped" class="overflow-wrap-anywhere">
                                <template #body="prop" >
                                    <div class="p-inputgroup w-8rem max-w-full">
                                        <InputNumber
                                            v-model="prop.data.shipped"
                                            buttonLayout="horizontal"
                                            :min="0"
                                            :max="prop.data.quantity"
                                            @input="updateQuantities($event, index,prop.data,order)"
                                        ></InputNumber>
                                    </div>
                                </template>
                            </Column>

<!--                            <template #groupheader="slotProps">-->
<!--                                <div class="flex items-center gap-2">-->
<!--                                    <span>{{ slotProps.data.order.name }}</span>-->
<!--                                </div>-->
<!--                            </template>-->
                        </DataTable>
                    </VhField>
                </template>



            </div>
        </Panel>

    </div>

</template>

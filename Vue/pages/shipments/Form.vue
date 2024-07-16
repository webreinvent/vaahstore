<script setup>
import {computed, onMounted, ref, watch, watchEffect} from "vue";
import { useShipmentStore } from '../../stores/store-shipments'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';
import {useRootStore} from '../../stores/root'
import {useShipmentStore} from "../../stores/store-shipments";
import {vaah} from "../../vaahvue/pinia/vaah";
const store = useShipmentStore();
const route = useRoute();
const root = useRootStore();
const useVaah = vaah();

onMounted(async () => {
    /**
     * Fetch the record from the database
     */
    if((!store.item || Object.keys(store.item).length < 1)
        && route.params && route.params.id)
    {
        await store.getItem(route.params.id);
    }






// onMounted(async () => {
//     /**
//      * Fetch the record from the database
//      */
//
//     if(store.assets_is_fetching === true)
//     {
//         await store.getAssets();
//     }
//     await store.watchRoutes(route);
//
//     if ((!store.item || Object.keys(store.item).length < 1)
//         && route.params && route.params.id) {
//
//         await store.getItem(route.params.id);
//     }
//
//     if(!store.list)
//     {
//         await store.getList();
//     }
//
//     store.getSelectButtonOptions();
//     store.getInventoryBulkMenu();
//     store.watchSupplierName();
//     store.watchInventoryStates();
//
//     if (store.quick_search_options?.length > 0) {
//         store.selected_quick_search_options = store.quick_search_options[0].slug;
//     }
//
// });

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
</script>
<template>

    <div class="col-7" >

        <Card class="is-small">


            <template #content>
                <div v-if="store.item" class="mt-2">


                    <div class="flex w-full justify-content-between mb-3">
                        <div class="flex justify-content-center">

                            <div class="ml-2">
                                <h3>Shipments</h3>
                                <h5>Create your shipment</h5>
                            </div>
                            <span v-if="store.item?.uuid" class="ml-1" style="font-size:15px;"><b>#{{store.item.uuid}}</b></span>
                            <div >
<!--                                <Chip label="Out "
                                      style="margin-left:8px;height:25px;"
                                      class="w-4rem bg-primary-100 border-primary-500"
                                      input-class="text-primary-500"

                                />-->
<!--                                <Chip-->
<!--                                      style="margin-left:8px;height:25px;"-->
<!--                                      class="w-9rem bg-primary-100 border-primary-500"-->
<!--                                      input-class="text-primary-500"-->
<!--                                />-->
                                <Dropdown
                                    v-model="store.item.shipment_status"
                                    :options="store.shipment_status"
                                    optionLabel="name"
                                    optionValue="id"
                                    class="ml-2 w-7rem border-round-3xl border-primary-500 bg-primary-50"
                                    input-class="text-primary-500 w-4rem px-2 py-1"
                                    :pt="{
                                        trigger: {
                                            class: 'text-primary-500'
                                        }
                                    }"
                                />

                            </div>

                        </div>
                 <div>
                     <div class="p-inputgroup">

                         <Button class="p-button-sm"
                                 v-tooltip.left="'View'"
                                 v-if="store.item && store.item.id"
                                 data-testid="categories-view_item"
                                 @click="store.toView(store.item)"
                                 icon="pi pi-eye"/>

                         <Button label="Save"
                                 class="p-button-sm"
                                 v-if="store.item && store.item.id"
                                 data-testid="categories-save"
                                 @click="store.itemAction('save')"
                                 icon="pi pi-save"/>

                         <Button label="Create & New"
                                 v-else
                                 @click="store.itemAction('create-and-new')"
                                 class="p-button-sm"
                                 data-testid="categories-create-and-new"
                                 icon="pi pi-save"/>


                         <!--form_menu-->
                         <Button
                             type="button"
                             @click="toggleFormMenu"
                             class="p-button-sm"
                             data-testid="categories-form-menu"
                             icon="pi pi-angle-down"
                             aria-haspopup="true"/>

                         <Menu ref="form_menu"
                               :model="store.form_menu_list"
                               :popup="true"/>
                         <!--/form_menu-->


                         <Button class="p-button-primary p-button-sm"
                                 icon="pi pi-times"
                                 data-testid="categories-to-list"
                                 @click="store.toList()">
                         </Button>
                     </div>
                        </div>
                    </div>
                    <hr />
                    <div class="flex w-full justify-content-between mt-4">
                        <div class="supplier_details w-full ml-2">
                            <Card class="h-full py-3 px-3" style="font-weight: 500;"
                                  :pt="{
                                        content: {
                                            class: 'py-0'
                                        }
                                    }"
                            >
                                <template #content>
                                    <div class="flex w-full justify-content-between">
                                        <div class="flex">
                                            <h4>Order Details</h4>
<!--                                            <Chip-->
<!--                                                :label="`Credit: `"-->
<!--                                                style="margin-left:8px;height:20px"-->
<!--                                                :pt="{-->
<!--                                                root: {-->
<!--                                                        style: {-->
<!--                                                                background: 'lightblue'-->
<!--                                                                }-->
<!--                                                        }-->
<!--                                                }"-->
<!--                                            />-->
                                        </div>

                                    </div>
                                    <div class="w-full mt-2">

                                        <VhField label="Order">
                                            <MultiSelect
                                                v-model="store.item.orders"
                                                :options="store.order_list"
                                                filter
                                                optionValue="id"
                                                optionLabel="name"
                                                placeholder="Select Order"
                                                display="chip"
                                                append-to="self"
                                                class="w-full relative" />
                                        </VhField>
                                    </div>

                                    <div v-if="store.item.orders?.length>0" class="w-full mt-2">

                                        <VhField label="Order Items">
                                            <MultiSelect
                                                v-model="store.item.order_items"
                                                :options="store.order_item_list"
                                                filter
                                                optionValue="id"
                                                optionLabel="name"
                                                placeholder="Select Order Items"
                                                display="chip"
                                                append-to="self"
                                                class="w-full relative" />
                                        </VhField>
                                    </div>
                                    <VhField label="Comment">
                                            <Textarea rows="3" class="w-full"
                                                      placeholder="Enter Comment"
                                                      name="products-status_notes"
                                                      data-testid="products-status_notes"
                                                      v-model="store.item.status_notes"/>
                                    </VhField>

                                </template>
                            </Card>
                        </div>


                    </div>
                    <hr class="mt-4" />

                    <div v-if="store.item.order_items"  class="flex w-full justify-content-between mt-3">

                        <div>
                            <div class="p-inputgroup">

                                <Button
                                    type="button"
                                    aria-haspopup="true"
                                    class="p-button-sm"
                                    label="Delete"

                                    :badge="store.selected_inventory?.length"
                                    @click="store.removeInventoryItems()"
                                />

                                <!--bulk_menu-->
                                <Button
                                    type="button"
                                    @click="toggleInventoryBulkMenu"
                                    severity="primary" outlined
                                    data-testid="companies-actions-bulk-menu"
                                    aria-haspopup="true"
                                    aria-controls="bulk_menu_state"
                                    class="ml-1 p-button-sm">
                                    <i class="pi pi-ellipsis-v"></i>
                                </Button>

                                <Menu ref="inventory_bulk_menu_state"
                                      :model="store.inventory_bulk_menu"
                                      :popup="true" />
                                <!--/bulk_menu-->

                            </div>

                        </div>

                        <div>
                            <Button class="p-button-sm"
                                    label="Add From Orders"
                                    @click="store.showInventory()"
                                    style="font-weight:600"
                            />
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
                            <template v-if=" !store.item.id">
                            <Column  header="To Be Shipped"  class="overflow-wrap-anywhere">
                                <template #body="prop" >

                                    <div v-if="Number(prop.data.pending) === 0 && (Number(prop.data.overall_shipped_quantity) === Number(prop.data.quantity)) ">
                                        <Button data-testid="vendors-document" icon="pi pi-info-circle"
                                                href="https://vaah.dev/store"
                                                class="p-button-sm"
                                                v-tooltip.top="`Overall shipped quantity with other shipment is : ${prop.data.overall_shipped_quantity}`"
                                               />
                                    </div>
<!--                                    <div class="p-inputgroup w-7rem max-w-full" v-else-if="((store.item.id && prop.data.pending ===0) || prop.data.pending !==0) && !store.item.id">-->
                                    <div class="p-inputgroup w-7rem max-w-full" >
                                        <InputNumber

                                            v-model="prop.data.to_be_shipped"
                                            buttonLayout="horizontal"
                                            :min="0"
                                            class="w-full"
                                            placeholder="Enter quantity"
                                            :max="prop.data.quantity"
                                            @input="store.updateQuantities($event,index,prop.data,order)"
                                        ></InputNumber>
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
                    <div class="flex mt-4 mb-4 justify-content-between">
                        <div style="width:40%;">

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
                                  aria-labelledby="basic" allowEmpty :invalid="value === null"  />
                </VhField>


            </div>
        </Panel>

    </div>





</template>

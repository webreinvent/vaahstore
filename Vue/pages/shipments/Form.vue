<script setup>
import {onMounted, ref, watch,computed} from "vue";

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';
import {useRootStore} from '../../stores/root'
import {useShipmentStore} from "../../stores/store-shipments";
import {vaah} from "../../vaahvue/pinia/vaah";
const store = useShipmentStore();
const route = useRoute();
const root = useRootStore();
const useVaah = vaah();







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

//--------bulk_menu
const inventory_bulk_menu_state = ref();
const toggleInventoryBulkMenu = (event) => {
    inventory_bulk_menu_state.value.toggle(event);
};

//--------/bulk_menu

</script>
<template>

    <div class="col-8" >

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

                    <div  class="mt-2" v-if="store.item.order_items">
                        <DataTable :value="store.order_list_table"
                                   scrollable
                                   scrollHeight="400px"
                                   dataKey="id"
                                   class="p-datatable-sm p-datatable-hoverable-rows"
                                   :nullSortOrder="-1"
                                   v-model:selection="store.selected_inventory"
                                   stripedRows
                                   responsiveLayout="scroll">

                            <Column  selectionMode="multiple"
                                     headerStyle="width: 3em">

                            </Column>
                            <Column header="Order Item Name" style="width: 200px" >
                                <template #body="prop">
                                    <span >{{prop.data.name}}</span>

                                </template>
                            </Column>
<!--                            <Column header="Is Paid" >-->
<!--                                <template #body="prop">-->
<!--                                    <span >{{prop.data.is_paid}}</span>-->

<!--                                </template>-->
<!--                            </Column>-->
                            <Column header="Qty in Stock" field="quantity" style="width:100px;">
                                <template #body="prop">
                                    <span >{{prop.data.quantity}}</span>
                                </template>
                            </Column>
                            <Column header="Quantity" field="ordered_quantity" style="width:100px;">
                                <template #body="prop">
                                    <div class="p-inputgroup w-8rem max-w-full" >
                                    <InputNumber  v-model="prop.data.available_quantity" buttonLayout="horizontal" showButtons :min="1" :max="1000000" @input="store.updateQuantity(prop.data.pivot,$event)">
                                        <template #incrementbuttonicon>
                                            <span class="pi pi-plus" />
                                        </template>
                                        <template #decrementbuttonicon>
                                            <span class="pi pi-minus" />
                                        </template>
                                    </InputNumber>
                                    </div>
                                </template>
                            </Column>
                            <Column header="Action" style="width:100px;">
                                <template #body="prop">
                                    <div class="p-inputgroup " >


                                        <Button class="p-button-tiny p-button-danger p-button-text"
                                                data-testid="inventories-table-action-trash"
                                                @click="store.removeInventoryItem(prop.data)"
                                                v-tooltip.top="'Trash'"
                                                icon="pi pi-trash" />

                                    </div>


                                </template>
                            </Column>
                            <ColumnGroup type="footer">
                                <Row>
                                    <Column :colspan="7" />
                                    <Column footerStyle="text-align:left" :footer="store.item?.ordered_quantity" />
                                    <Column />
                                    <Column footerStyle="text-align:left" :footer="store.item?.ordered_amount" />
                                    <Column />
                                    <Column footerStyle="text-align:left" :footer="store.item?.total_volume" />

                                    <Column :colspan="4" />
                                </Row>
                            </ColumnGroup>
                            <template #empty>
                                <div class="text-center py-3">
                                    No records found.
                                </div>
                            </template>

                        </DataTable>
                    </div>
                    <div  class="flex mt-4 mb-4 justify-content-between">
                        <div style="width:40%;">

                        </div>
                        <div class="flex justify-content-end align-items-center" style="width:60%">

                            <span class="mr-2" style="font-weight:500">Total Quantity : <b>{{store.item?.ordered_quantity}}</b></span>
                            <span class="mr-2">|</span>
                            <span style="font-weight:500">Total Amount : <b v-if="store.item.ordered_amount">${{store.item?.ordered_amount}}</b></span>



<!--                            <Button-->

<!--                                class="p-button ml-4 mr-2"-->
<!--                                data-testid="po-update_changes"-->
<!--                                label="Update"-->
<!--                                @click="store.savePurchaseOrder()"-->

<!--                            />-->

                        </div>
                    </div>
                </div>
            </template>

        </Card>

    </div>





</template>

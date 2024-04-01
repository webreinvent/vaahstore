<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductStore } from '../../../stores/store-products'
import {computed, ref, watch} from "vue";
const store = useProductStore();
const useVaah = vaah()
const show_preferred = ref(false);

const filtered_vendors = computed(() => {
    if (!store.item) return [];
    if (show_preferred.value) {
        return store.item.vendor_data.filter(vendor => vendor.is_preferred === 1);
    } else {
        return store.item.vendor_data;
    }
});
watch(() => store.show_vendor_panel, (newValue) => {
    if (!newValue) {
        show_preferred.value = false;
    }
})

</script>

<template>

    <div v-if="store.list" class="data-container" style=" display: flex;flex-direction: column;justify-content: center; height: 100%;">
        <!--table-->
         <DataTable :value="store.list.data"
                       dataKey="id"
                    :rowClass="(rowData) => rowData.id === store.item?.id ? 'bg-yellow-100' : ''"

                   class="p-datatable-sm p-datatable-hoverable-rows"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

            <Column field="name" header="Name"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <span v-if="prop.data.is_default">
                        <div style="word-break: break-word;">{{ prop.data.name }}</div>
                         </span>
                    <span v-else>
                        <div style="word-break: break-word;">{{ prop.data.name }}</div>
                    </span>
                </template>

            </Column>

             <Column field="store.name" header="Store"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data && prop.data.store && prop.data.store.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <span>
                        <div style="word-break: break-word;" v-if="prop.data && prop.data.store">
                            {{ prop.data.store.name }}</div>
                         </span>
                 </template>

             </Column>


             <Column field="quantity" header="Quantity"
                     v-if="store.isViewLarge()"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.quantity == 0 || prop.data.quantity === null"
                            value="0"
                            severity="danger"></Badge>
                     <Badge v-else-if="prop.data.quantity > 0"
                            :value="prop.data.quantity"
                            severity="info"></Badge>
                 </template>
             </Column>
             <Column field="price range" header="Price Range">
                 <template #body="props">
        <span>
            {{
                props.data.vendors_data && props.data.vendors_data.length > 0
                ? store.calculatePriceRangeForProduct(props.data.vendors_data[0].variation_prices) || 'Not available'
                : 'Not available'
            }}
        </span>
                 </template>
             </Column>

             <Column field="variations" header="Variations"
                     :sortable="false">

                 <template #body="prop">
                     <div class="p-inputgroup">
                         <span  v-if="prop.data.product_variations && prop.data.product_variations.length"
                                class="p-inputgroup-addon cursor-pointer"
                                v-tooltip.top="'View Variations'"
                                @click="store.toViewVariation(prop.data)">

                             <b>{{prop.data.product_variations.length}}</b>

                         </span>
                         <span class="p-inputgroup-addon" v-else>
                             <b>{{prop.data.product_variations.length}}</b>
                         </span>
                         <Button icon="pi pi-plus" severity="info" v-if="!prop.data.deleted_at"
                                 v-tooltip.top="'Add Variations'"
                                 :disabled="prop.data.id===store.item?.id && $route.path.includes('variation')"
                                 @click="store.toVariation(prop.data)" />
                     </div>

                 </template>
             </Column>

             <Column field="vendors" header="Vendors"
                     :sortable="false">
                 <template #body="prop">
                     <div class="p-inputgroup">
                         <span class="p-inputgroup-addon cursor-pointer"
                               v-tooltip.top="'View Vendors'"
                               v-if="prop.data.product_vendors && prop.data.product_vendors.length"

                               @click="store.openVendorsPanel(prop.data)"
                         >
                             <b >{{prop.data.product_vendors.length}}</b>

                         </span>
                         <span class="p-inputgroup-addon"
                               v-else>
                             <b >{{prop.data.product_vendors.length}}</b>

                         </span>
                         <Button icon="pi pi-plus" severity="info" v-if="!prop.data.deleted_at"
                                 v-tooltip.top="'Add Vendors'"
                                 :disabled="prop.data.id===store.item?.id  && $route.path.includes('vendor')"
                                 @click="store.toVendor(prop.data)" />

                     </div>
                 </template>


             </Column>

             <Column field="status.name" header="Status"
                     :sortable="true">

                 <template #body="prop">

                     <Badge v-if="prop.data.status.slug == 'approved'"
                            severity="success"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="prop.data.status.slug == 'rejected'"
                            severity="danger"> {{prop.data.status.name}} </Badge>
                     <Badge v-else
                            severity="warning"> {{prop.data.status.name}} </Badge>
                 </template>

             </Column>

            <Column field="is_active" v-if="store.isViewLarge()"
                    style="width:80px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 data-testid="products-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsActive(prop.data)">
                    </InputSwitch>
                </template>

            </Column>

            <Column field="actions"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="products-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                                class="p-button-tiny p-button-text"
                                data-testid="products-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="products-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at &&
                                store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="products-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at &&
                                 store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />

                    </div>

                </template>


            </Column>

             <template #empty="prop">

                 <div class="no-record-message" style="text-align: center;font-size: 12px; color: #888;">No records found.</div>

             </template>


        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>


    <Sidebar v-model:visible="store.show_vendor_panel"  header="Product Price With Vendors" position="right" style="width:800px;">
        <template #header>
            <h2 style="font-weight: bold;" v-if="store.item" >{{store.product_name}}</h2>
        </template>
        <div class="flex align-items-center pt-1">
            <Checkbox v-model="show_preferred" :binary="true" />
            <label for="preferred-filter" class="ml-2"> Only Preferred Vendor </label>
        </div>

        <DataTable v-if="store.item " :value="filtered_vendors" style="border: 1px solid #ccc;margin-top:20px;"
                   :rows="10"
        :paginator="true"
                   class="p-datatable-sm p-datatable-hoverable-rows">
            <Column header="Sr No" style="border: 1px solid #ccc;">
                <template #body="props">
                    {{ props.index + 1 }}
                </template>
            </Column>
            <Column field="name" header="Vendor Name" style="border: 1px solid #ccc;">
                <template #body="props">
                    <div  class=" hover:text-primary-700 cursor-pointer">
                        {{props.data.name}}
                        <span v-if="props.data.is_default === 1">
                         <Badge severity="info">&nbsp;(Default)</Badge>
                     </span>
                    </div>
                </template>
            </Column>

            <Column field="price range" header="Price Range" style="border: 1px solid #ccc;">
                <template #body="props">
                    <Badge  v-if="props.data.is_default === 1 || props.data.is_preferred===1">
                        {{ store.calculatePriceRange(props.data.variation_prices) }}
                    </Badge>
                </template>
            </Column>
            <column field="Action" header="Is Preferred" style="border:1px solid #ccc;">
                <template #body="props">
                    <InputSwitch v-model.bool="props.data.is_preferred "
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 data-testid="products-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsPreferred(props.data)">
                    </InputSwitch>
                </template>
            </column>
            <template #empty="prop">

                <div  style="text-align: center;font-size: 12px; color: #888;">No records found.</div>

            </template>
        </DataTable>
    </Sidebar>

</template>

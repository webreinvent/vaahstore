<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductStore } from '../../../stores/store-products'
import ProductCategories from '../components/ProductCategories.vue'
import { useDialog } from "primevue/usedialog";
import {computed, ref, watch} from "vue";
import VendorsList from './VendorsList.vue'
const store = useProductStore();
const useVaah = vaah()
const visible = ref(false);

const dialog = useDialog();
const openProductCategories = (categories,product) => {
    const dialogRef = dialog.open(ProductCategories, {
        props: {
            header: product,
            style: {
                width: '50vw',
            },
            breakpoints:{
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true
        },
        data : {'categories' : categories

        },
    });
}

</script>

<template>

    <div v-if="store.list" class="data-container" style=" display: flex;flex-direction: column;justify-content: center; height: 100%;">
        <!--table-->
         <DataTable  :value="store.list.data"
                       dataKey="id"
                    :rowClass="(rowData) => rowData.id === store.item?.id ? 'bg-yellow-100' : ''"

                   class="p-datatable-sm p-datatable-hoverable-rows"
                   v-model:selection="store.action.items"
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
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
                        <div  style="word-break: break-word;">{{ prop.data.name }}</div>
                         </span>
                    <span v-else>
                        <div class=" text-xs" style="word-break: break-word;">{{ prop.data.name }}</div>
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

             <Column field="quantity" header="Quantity" v-if="store.isListView()" :sortable="true">
                 <template #body="prop">
                     <template v-if="prop.data && prop.data.product_price_range">
                         <p class="text-center" v-if="prop.data.product_price_range.quantity"

                                >{{prop.data.product_price_range.quantity}}</p>
                         <p class="text-center" v-else-if="prop.data.quantity == 0 || prop.data.quantity === null"

                         >0</p>
                         <Badge v-else
                                :value="prop.data.quantity"
                                severity="info"></Badge>
                     </template>
                     <template v-else>
                         <Badge value="0" severity="danger"></Badge>
                     </template>
                 </template>
             </Column>

             <Column field="vendors" header="Vendors" :sortable="false">
                 <template #body="prop">
                     <div class="p-inputgroup  justify-between !items-center border py-1 px-2 rounded-lg !gap-8">
            <span class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
                  v-tooltip.top="'View Vendors'"
                  @click="store.openVendorsPanel(prop.data)">
                <b v-if="prop.data && prop.data.is_attached_default_vendor === false">
                    {{ prop.data.product_vendors.length + 1 }}
                </b>
                <b v-else>
                    {{ prop.data ? prop.data.product_vendors.length : 0 }}
                </b>
            </span>
                         <Button :pt="{ icon: { class: '!text-[8px]' } }"  icon="pi pi-plus" class="quantity-button !rounded" severity="info" v-if="!prop.data.deleted_at"
                                 v-tooltip.top="'Add Vendors'"
                                 :disabled="prop.data && prop.data.id === store.item?.id && $route.path.includes('vendor')"
                                 @click="store.toVendor(prop.data)" />
                     </div>
                 </template>
             </Column>



             <Column  header="Selected Vendor"
                      v-if="store.isListView()">

                 <template #body="prop">
                     <Badge v-if="prop.data && prop.data.product_price_range && prop.data.product_price_range.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <span>
                        <div style="word-break: break-word;" v-if="prop.data && prop.data.product_price_range.selected_vendor">
                            {{ prop.data.product_price_range.selected_vendor.name }}</div>
                         </span>
                 </template>
             </Column>

             <Column field="price range" header="Price Range">
                 <template #body="prop">
       <span v-if="prop.data && Array.isArray(prop.data.product_price_range.price_range) && prop.data.product_price_range.price_range.length > 0">
            <span v-html="prop.data.store.default_currency?.symbol"></span>  {{ prop.data.product_price_range.price_range.join(' - ') }}
        </span>
                     <span v-else>
            <span v-html="prop.data.store.default_currency?.symbol"></span> 0
        </span>
                 </template>
             </Column>

             <Column field="variations" header="Variations"
                     :sortable="false">

                 <template #body="prop">
                     <div class="p-inputgroup  justify-between !items-center border py-1 px-2 rounded-lg !gap-8">
            <span v-if="prop.data.product_variations_count && prop.data.product_variations_count!= null" class="p-inputgroup-addon border-none py-1 bg-transparent cursor-pointer leading-[14px] text-xs p-0 min-w-max"
                  v-tooltip.top="'View Variations'"
                  @click="store.toViewVariation(prop.data)">
               <b>{{prop.data.product_variations_count}}</b>
            </span>
                         <Button :pt="{ icon: { class: '!text-[8px]' } }"  icon="pi pi-plus" class="quantity-button !rounded" severity="info" v-if="!prop.data.deleted_at"
                                 v-tooltip.top="'Add Variations'"
                                 :disabled="prop.data.id===store.item?.id && $route.path.includes('variation')"
                                 @click="store.toVariation(prop.data)" />
                     </div>
                 </template>
             </Column>

             <Column field="product_categories.name" header="Categories">
                 <template #body="prop">
                     <div class="p-inputgroup justify-center">
                        <span v-if="prop.data.product_categories && prop.data.product_categories.length" class=""
                              @click="openProductCategories(prop.data.product_categories,prop.data.name)" v-tooltip.top="'View Categories'">
                              <p>{{prop.data.product_categories.length}}</p>
                        </span>
                         <span class="p-inputgroup-addon" v-else>
                             <Badge severity="info">0</Badge>
                         </span>
                     </div>
                 </template>
             </Column>

            <Column field="is_active" v-if="store.isListView()"
                    style="width:80px;"
                    header="Is Active">

                <template #body="prop">
                    <ToggleSwitch v-model.bool="prop.data.is_active"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 data-testid="products-table-is-active"
                                  v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                  size="small"
                                  variant="success"
                                 @input="store.toggleIsActive(prop.data)">
                    </ToggleSwitch>
                </template>

            </Column>

            <Column field="actions"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup gap-1  ">

                        <Button class="p-button-tiny icon-button p-button-text"
                                data-testid="products-table-to-view"
                                v-tooltip.top="'Add To Cart'"
                                @click="store.addToCart(prop.data)"
                                icon="pi pi-shopping-cart" />

                        <Button class="p-button-tiny icon-button p-button-text"
                                data-testid="products-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                                class="p-button-tiny  p-button-text icon-button"
                                data-testid="products-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny text-red-400 p-button-danger p-button-text icon-button"
                                data-testid="products-table-action-trash"
                                v-if="store.isListView() && !prop.data.deleted_at &&
                                store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text icon-button"
                                data-testid="products-table-action-restore"
                                v-if="store.isListView() && prop.data.deleted_at &&
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
        <DynamicDialog  />

        <VendorsList/>
    </div>

    <Dialog v-model:visible="store.add_to_cart" modal header="Add To Cart" :style="{ width: '30rem' }"
            @hide="store.onHideCartDialog"
    >
        <div class="p-inputgroup py-3">
            <AutoComplete
                v-model="store.item.user"
                @change="store.setUser($event)"
                class="w-full"
                :suggestions="store.user_suggestions"
                @complete="store.searchUser($event)"
                placeholder="Enter Email or Phone"
                data-testid="products-cart"
                name="products-cart"
                optionLabel="email"

                :pt="{
                                              token: {
                        class: 'max-w-full'
                    },
                    removeTokenIcon: {
                    class: 'min-w-max'
                    },
                    item: { style: {
                    textWrap: 'wrap'
                    }  },
                    panel: { class: 'w-16rem ' }
                                                }">
            </AutoComplete>
            <Button type="button" size="small" label="Add To Cart" @click="store.addProductToCart(store.product_detail)">

            </Button>
        </div>
    </Dialog>

</template>

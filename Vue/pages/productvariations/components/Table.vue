<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductVariationStore } from '../../../stores/store-productvariations'
import {onMounted} from "vue";
import {useRoute} from "vue-router";


const store = useProductVariationStore();
const useVaah = vaah();
const route = useRoute();

</script>

<template>

    <div v-if="store.list">
        <Message v-if="!store.list.data || store.default_variation_message" severity="warn" class="mt-1" :closable="false">
            There is no default variation. Mark a variation as <strong>default</strong>.
        </Message>
        <!--table-->
        <DataTable
            :value="store.list.data"
            dataKey="id"
            :rowClass="(rowData) => rowData && rowData.id === store.item && store.item.id ? 'bg-yellow-200' : ''"
            class="p-datatable-sm p-datatable-hoverable-rows"
            v-model:selection="store.action.items"
            stripedRows
            responsiveLayout="scroll"
        >

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

            <Column field="name" header="Name" :sortable="true">

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

            <Column field="product.name" header="Product"
                    :sortable="true">


                <template #body="prop" >
                    <Badge v-if="prop.data.product && prop.data.product.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <div style="word-break: break-word;" v-if="prop.data.product && prop.data.product.name">
                        {{ prop.data.product.name }}
                    </div>
                </template>

            </Column>

             <Column field="quantity" header="Quantity"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.quantity == 0"
                            value="0"
                            severity="danger"></Badge>
                     <Badge v-else-if="prop.data.quantity > 0"
                            :value="prop.data.quantity"
                            severity="info"></Badge>
                 </template>

             </Column>
            <Column field="price" header="Price"
                    :sortable="true">

                <template #body="prop">
                    {{prop.data.price}}
                </template>

            </Column>
            <Column field="stock_status" header="Stock Status"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.quantity === 0"
                           severity="danger" :style="{height: 'max-content !important', lineHeight: 'normal', Padding: '0.4rem'}">Out Of Stock</Badge>
                    <Badge v-else-if="prop.data.quantity > 0"
                           severity="info">In Stock</Badge>
                </template>

            </Column>

             <Column field="status.name" header="Status">

                 <template #body="prop">
                     <Badge v-if="prop.data.status.slug == 'approved'"
                            severity="success"> {{prop.data.status.name}} </Badge>

                     <Badge v-else-if="prop.data.status.slug == 'rejected'"
                            severity="danger"> {{prop.data.status.name}} </Badge>

                     <Badge v-else
                            severity="warning"> {{prop.data.status.name}} </Badge>
                 </template>

             </Column>




            <Column
                field="is_active"
                v-if="store.isViewLarge()"
                style="width:100px;"
                header="Is Active"
            >
                <template #body="prop">
                    <InputSwitch
                        v-model.bool="prop.data.is_active"
                        data-testid="productvariations-table-is-active"
                        v-bind:false-value="0"
                        v-bind:true-value="1"
                        class="p-inputswitch-sm"
                        @input="store.toggleIsActive(prop.data)"
                        :pt="{
        slider: ({ props }) => ({
          class: props.modelValue ? 'bg-green-400' : '',
        }),
      }"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                    ></InputSwitch>
                </template>
            </Column>


            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="products-table-to-view"
                                v-tooltip.top="'Add To Cart'"
                                @click="store.addToCart(prop.data)"
                                icon="pi pi-shopping-cart" />
                        <Button class="p-button-tiny p-button-text"
                                data-testid="productvariations-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                                class="p-button-tiny p-button-text"
                                data-testid="productvariations-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="productvariations-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at"
                                @click="store.itemAction('trash', prop.data)"
                                :disabled="!store.assets.permissions.includes('can-update-module')"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="productvariations-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
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
                   :first="(store.query.page-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>
    <Dialog v-model:visible="store.add_to_cart" modal header="Add To Cart" :style="{ width: '25rem' }">
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
                optionLabel="name"
                forceSelection
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
            <Button type="button" label="Add To Cart" @click="store.addVariationToCart(store.product_detail)">

            </Button>
        </div>
    </Dialog>
</template>

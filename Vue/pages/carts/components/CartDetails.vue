<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useCartStore } from '../../../stores/store-carts'

const store = useCartStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list" class="p-4 bg-white">
        <div class="cart_detail">
        <!--table-->
        <DataTable :value="store.list.data"
                   dataKey="id"
                   :rowClass="store.setRowClass"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :nullSortOrder="-1"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true">
            </Column>

            <Column field="product_name" header="Product Name"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    {{prop.data.name}}
                </template>

            </Column>

            <Column field="product_quantity" header="Product Quantity"
                    class="overflow-wrap-anywhere"
                    >

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="carts-table-to-view"
                                v-tooltip.top="'Cart Details'"
                                @click=""
                                icon="pi pi-minus" />
                        <InputNumber v-model="value"style="width: 1rem" :min="0" :max="99"/>

                        <Button class="p-button-tiny p-button-text"
                                data-testid="carts-table-to-view"
                                v-tooltip.top="'Cart Details'"
                                @click=""
                                icon="pi pi-plus"/>
                    </div>
                </template>

            </Column>

            <Column field="product_price" header="Product Price"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    {{'40000'}}
                    <Button class="p-button-tiny p-button-danger p-button-text"
                            data-testid="orders-table-action-trash"
                            v-tooltip.top="'Remove'"
                            icon="pi pi-trash" />
                </template>

            </Column>

            <template #empty>
                <div class="text-center py-3">
                    No records found.
                </div>
            </template>

        </DataTable>
        <!--/table-->
            <div class="table_bottom">
                <InputNumber v-model="store" value="40000" inputId="integeronly" />
            </div>
            <div class="table_bottom">
                <Button label="Check Out"
                        @click="store.checkOut({id:1})"
                />
            </div>
        </div>

        <!--paginator-->
        <Paginator v-if="store.query.rows"
                   v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="((store.query.page??1)-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>

<style scoped>
.cart_detail{
    background: #fff;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 8px;
}

.table_bottom {
    display: flex;
    justify-content: flex-end;
}
</style>

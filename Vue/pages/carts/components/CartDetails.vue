<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useCartStore} from '../../../stores/store-carts'
import {computed, onMounted, ref, watch, watchEffect} from "vue";
import {useRoute} from "vue-router";
const route = useRoute();
const store = useCartStore();
const useVaah = vaah();
const cartProducts = ref([]);

onMounted(async () => {
    document.title = 'Carts - Items';
    if (route.params && route.params.id) {
        await store.getItem(route.params.id);
    }
    await store.onLoad(route);
    await store.getList();
    cartProducts.value = store.cart_products;
});
watch(cartProducts, (newValue, oldValue) => {
    totalAmount.value = store.calculateTotalAmount(newValue);
}, { deep: true });

const totalAmount = computed(() => {
    return store.calculateTotalAmount(cartProducts.value);
});
</script>

<template>

    <div v-if="store.list" class="bg-white">
        <div class="cart_detail">
            <div class="flex flex-row">
                <div>
<!--                    <b class="mr-1">UUID-Carts 1-Rahul</b>-->
                    <Button
                        @click="this.$router.push({name: 'carts.index'})"
                        label="Back"/><b class="mr-1 ml-3" v-if="store.item && store.item.user">{{ store.item.uuid }} -Carts {{store.cart_products.length}} - {{ store.item.user.first_name }}</b>


                </div>



            </div>


            <!--table-->
            <div v-if="store.item && store.item.user">
            <DataTable :value="store.cart_products"
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

                <Column field="product_name" header="Product Name" class="overflow-wrap-anywhere" :sortable="true">
                    <template #body="prop">
                        {{ prop.data.pivot.cart_product_variation !== null ? prop.data.name + ' - ' + prop.data.pivot.cart_product_variation : prop.data.name }}
                    </template>
                </Column>



                <Column field="product_quantity" header="Product Quantity" class="overflow-wrap-anywhere">
                    <template #body="prop">
                        <div class="p-inputgroup w-8rem max-w-full">
                            <InputNumber   v-model="prop.data.pivot.quantity" buttonLayout="horizontal" showButtons :min="1" :max="99" @input="store.updateQuantity(prop.data.pivot,$event)">
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



                <Column field="product_price" header="Product Price"
                        class="overflow-wrap-anywhere"
                        :sortable="true">

                    <template #body="prop">
                        <div class="flex align-items-center justify-content-between w-full">
                            <p>{{ prop.data.pivot.price }}</p>
<!--                            <p>{{ calculatePrice(prop.data) }}</p>-->
                        </div>
                    </template>

                </Column>


                <Column field="actions"
                        :style="{width: store.getActionWidth() }"
                        header="Actions">

                    <template #body="prop">
                        <div class="p-inputgroup ">
                            <Button class="p-button-tiny p-button-danger p-button-text"
                                    data-testid="orders-table-action-trash"
                                    v-tooltip.top="'Wishlist'"
                                    icon="pi pi-heart"/>


                            <Button class="p-button-tiny p-button-danger p-button-text"
                                    data-testid="products-table-action-trash"
                                    @click="store.deleteCartItem(prop.data.pivot)"
                                    v-tooltip.top="'Remove'"
                                    icon="pi pi-trash" />
                        </div>

                    </template>


                </Column>

                <template #empty>
                    <div class="text-center py-3">
                        No records found.
                    </div>
                </template>

            </DataTable>
            </div>
            <!--/table-->
            <div class="table_bottom mr-4">
                <p><b>Total Amount: </b>{{ totalAmount }}</p>
            </div>
            <div class="table_bottom">
                <Button label="Check Out"
                        @click="store.checkOut(store.item.id)"
                />
            </div>
        </div>

    </div>

</template>

<style scoped>
.cart_detail {
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

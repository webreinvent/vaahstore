<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useCartStore} from '../../../stores/store-carts'
import {computed, onMounted, ref, watch, watchEffect} from "vue";
import {useRoute} from "vue-router";
const route = useRoute();
const store = useCartStore();
const useVaah = vaah();
const cart_products_stock_available = ref([]);

onMounted(async () => {
    document.title = 'Carts - Items';
    if (route.params && route.params.id) {
        await store.getItem(route.params.id);
    }
    await store.onLoad(route);
    await store.getList();
    cart_products_stock_available.value = store.cart_products;
});

const allProductsOutOfStock = computed(() => {
    return cart_products_stock_available.value.every(product => product.pivot.is_stock_available === 0);
});
</script>

<template>

    <div   v-if="store.list" class="bg-white">
        <div class="cart_detail">
            <div class="flex flex-row">
                <div >
                    <Button
                        @click="store.redirectToCart"
                        label="Back"/><b class="mr-1 ml-3" v-if="store.item && store.item.user">{{ store.item.uuid }}   - {{ store.item.user.display_name }} ({{store.cart_products.length}})</b>
                </div>



            </div>


            <!--table-->
            <div v-if="store.item && store.item.user">
            <DataTable :value="store.cart_products"
                       dataKey="id"
                       :rowClass="store.setRowClass"
                       :rows="10"
                       :paginator="true"
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
                        <Badge v-if="prop.data.pivot.is_stock_available === 0"  value="Out of Stock"
                               severity="danger"></Badge>
                    </template>
                </Column>



                <Column field="product_quantity" header="Product Quantity" class="overflow-wrap-anywhere">
                    <template #body="prop">
                        <div class="p-inputgroup w-8rem max-w-full" >
                            <InputNumber  v-model="prop.data.pivot.quantity" buttonLayout="horizontal" showButtons :min="1" :max="1000000" @input="store.updateQuantity(prop.data.pivot,$event)">
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
                                    @click="store.addToWishList(prop.data.pivot,store.item.user)"
                                    :icon="prop.data.pivot.is_wishlisted === 1 ? 'pi pi-heart-fill' : 'pi pi-heart'"/>


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
            <div class="table_bottom mr-4">
                <p><b>Total Amount: </b>{{ store.total_amount_at_detail_page }}</p>
            </div>
            <div class="table_bottom">
                <Button label="Check Out"
                        :disabled="allProductsOutOfStock"
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
.filled-heart .pi pi-heart {
    background-color: red; /* Change this to your desired background color */
}
</style>

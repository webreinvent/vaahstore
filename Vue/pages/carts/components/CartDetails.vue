<script setup>
import { vaah } from "../../../vaahvue/pinia/vaah";
import { useCartStore } from "../../../stores/store-carts";
import { computed, onMounted, ref, watch, watchEffect } from "vue";
import { useRoute } from "vue-router";
import { useProductStore } from "../../../stores/store-products";
import Actions from "./Actions.vue";
import {useRootStore} from "../../../stores/root";
const route = useRoute();
const store = useCartStore();
const useVaah = vaah();
const root = useRootStore();
const cart_products_stock_available = ref([]);
const product_store = useProductStore();
onMounted(async () => {
    document.title = 'Carts - Items';
    await store.loadAssets();
    if (route.params && route.params.id) {
        await store.getItem(route.params.id,store.query.selected_store);
    }
    await store.onLoad(route);
    await store.getList();
    cart_products_stock_available.value = store.cart_products;
});
watch(() => store.query.selected_store, async (newStoreId) => {
    if (newStoreId && route.params?.id) {
        await store.getItem(route.params.id, newStoreId);
        cart_products_stock_available.value = store.cart_products;
    }
});

const allProductsOutOfStock = computed(() => {
  return cart_products_stock_available.value.every(
    (product) => product.pivot.is_stock_available === 0
  );
});
</script>

<template>
  <div v-if="store.list" class="">
    <div class="flex flex-col">
      <div class="flex flex-row justify-content-between bg-gray-100 pb-3">
        <div class="flex items-center gap-2">
          <Button
            @click="store.redirectToCart"
            class="text-gray-500 font-semibold !gap-[2px]"
            size="small"
          >
            <Icon icon="si:arrow-left-duotone" width="24" height="24" />Back
          </Button>
          <div class="mr-1 ml-2" v-if="store.item">
            <span class="text-gray-950 text-base leading-5 font-semibold"
              >{{ store.item.uuid }}-<span class="text-gray-400">{{
                store.item.user?.email
              }}</span></span
            >
          </div>
        </div>

        <div class="">
          <Button
            data-testid="carts-user-modal"
            class="p-button-lg"
            v-if="!store.item?.user"
            v-tooltip.top="'Assign User'"
            @click="store.openUserDialog(store.item)"
          >
            <i class="pi pi-user-plus mr-1"></i>
          </Button>
        </div>
      </div>

      <div class="h-[1px] bg-gray-200 w-full mb-2"></div>

      <!--table-->
      <Card>
        <template #content>
          <div v-if="store.item">
            <Actions />

            <DataTable
              :value="store.cart_products"
              dataKey="id"
              :rows="10"
              :paginator="true"
              :nullSortOrder="-1"
              v-model:selection="store.action.items"
              responsiveLayout="scroll"
            >
              <Column
                selectionMode="multiple"
                v-if="store.isListView()"
                headerStyle="width: 3em"
              >
              </Column>

              <Column
                field="id"
                header="ID"
                class="text-xs text-gray-400 leading-5 font-bold"
                :style="{ width: '80px' }"
                :sortable="true"
              >
              </Column>

              <Column
                field="product_name"
                header="Product Name"
                class="overflow-wrap-anywhere text-xs text-gray-400 leading-5 font-bold"
                :style="{ width: '400px' }"
              >
                <template #body="prop">
                  {{
                    prop.data.pivot.cart_product_variation
                      ? prop.data.pivot.cart_product_variation
                      : prop.data.name
                  }}
                </template>
              </Column>

              <Column
                header=""
                class="overflow-wrap-anywhere text-xs text-gray-400 leading-5 font-bold"
              >
                <template #body="prop">
                  <div
                    class="w-full"
                    v-if="prop.data.pivot.is_stock_available === 0"
                  >
                    <Badge value="Out of Stock" severity="danger"></Badge>
                    <Badge class="ml-2" severity="danger"
                      >Available Qty:{{
                        prop.data.available_stock_quantity
                      }}</Badge
                    >
                  </div>
                </template>
              </Column>

              <Column
                field="product_quantity"
                header="Product Quantity"
                class="overflow-wrap-anywhere text-xs text-gray-400 leading-5 font-bold"
              >
                <template #body="prop">
                  <div
                    class="p-inputgroup w-8rem max-w-full border p-0 border-gray-200 text-center rounded-lg"
                  >
                    <InputNumber
                      v-model="prop.data.pivot.quantity"
                      buttonLayout="horizontal"
                      showButtons
                      :min="1"
                      :max="1000000"
                      style="
                        padding: 0px !important;
                        text-align: center !important;
                        border: none;
                        background: none;
                      "
                      :pt="{
                        pcInputText: {
                          class: '!bg-none !p-0 !border-none !border-0',
                        },
                        incrementButton: {
                          class: '!bg-none !p-0 !border-none',
                        },
                        decrementButton: {
                          class: '!bg-none !p-0 !border-none',
                        },
                      }"
                      @input="store.updateQuantity(prop.data.pivot, $event)"
                    >
                      <template #incrementbuttonicon>
                        <Button
                          :pt="{ icon: { class: '!text-[8px]' } }"
                          icon="pi pi-plus"
                          class="quantity-button !rounded"
                          severity="info"
                        />
                      </template>
                      <template #decrementbuttonicon>
                        <Button
                          :pt="{ icon: { class: '!text-[8px]' } }"
                          icon="pi pi-minus"
                          class="quantity-button !rounded"
                          severity="info"
                        />
                      </template>
                    </InputNumber>
                  </div>
                </template>
              </Column>

              <Column
                field="product_price"
                header="Product Price"
                class="overflow-wrap-anywhere text-xs text-gray-400 leading-5 font-bold"
              >
                <template #body="prop">
                  <div
                    class="flex align-items-center justify-content-between w-full"
                  >
                    <p>
                        <span v-html="prop.data.price.currency?.symbol"></span>
                        {{ prop.data.pivot.price }}</p>
                  </div>
                </template>
              </Column>
              <Column
                field="product_price"
                header="Amount"
                class="overflow-wrap-anywhere text-xs text-gray-400 leading-5 font-bold"
              >
                <template #body="prop">
                  <div
                    class="flex align-items-center justify-content-between w-full"
                  >
                    <p>
                        <span v-html="prop.data.price.currency?.symbol"></span>{{
                        prop.data.pivot.price * prop.data.pivot.quantity
                      }}
                    </p>
                  </div>
                </template>
              </Column>

              <Column
                field="actions"
                :style="{ width: store.getActionWidth() }"
                header="Actions"
              >
                <template #body="prop">
                  <div class="p-inputgroup gap-2 items-center">
                    <Button
                      class="p-button-tiny p-button-danger p-button-text"
                      data-testid="orders-table-action-trash"
                      v-tooltip.top="'Wishlist'"
                      :pt="{ root: { class: '!bg-none !p-0' } }"
                      @click="
                        store.addToWishList(prop.data.pivot, store.item.user)
                      "
                    >
                      <Icon
                        :icon="
                          prop.data.pivot.is_wishlisted === 1
                            ? 'mingcute:heart-fill'
                            : 'solar:heart-outline'
                        "
                        width="18"
                        height="18"
                        :class="
                          prop.data.pivot.is_wishlisted === 1
                            ? 'text-[#F05252]'
                            : ' text-gray-500'
                        "
                      />
                    </Button>

                    <Button
                      class="p-button-tiny p-button-danger p-button-text"
                      data-testid="products-table-action-trash"
                      @click="store.deleteCartItem(prop.data.pivot, 'delete')"
                      v-tooltip.top="'Remove'"
                      :pt="{ root: { class: '!bg-none !p-0' } }"
                      icon="pi pi-trash"
                    />
                  </div>
                </template>
              </Column>

              <template #empty>
                <div class="text-center py-3">No records found.</div>
              </template>
            </DataTable>
          </div>

          <div class="flex justify-end items-center gap-3 py-3">
            <div>
              <p class="text-gray-400 text-xs font-bold">Total Amount</p>
              <p class="text-gray-950 text-base leading-5 font-bold">
                  <span v-html="root.selected_store_at_sidebar?.default_currency.symbol"></span>{{ store.total_amount_at_detail_page }}
              </p>
            </div>
            <Button
              label="Check Out"
              size="small"
              :disabled="allProductsOutOfStock"
              :pt="{ root: { class: '!bg-none' } }"
              @click="store.checkOut(store.item.id)"
              class="font-bold text-sm text-gray-50 py-2 px-[10px] rounded-lg"
            >
              Proceed To Checkout
              <Icon
                icon="lets-icons:arrow-right-light"
                width="18px"
                height="18px"
                class="text-gray-50"
              />
            </Button>
          </div>
        </template>
      </Card>
    </div>
  </div>
  <Dialog
    v-model:visible="store.open_user_dialog"
    modal
    position="top"
    header="Add User To Cart"
    :style="{ width: '30rem' }"
    @hide="store.onHideUserDialog"
  >
    <div class="flex items-center gap-4 mb-4">
      <label for="username" class="font-semibold w-24 mt-2">User</label>
      <AutoComplete
        v-model="store.item.user_object"
        class="w-full"
        :suggestions="product_store.user_suggestions"
        @complete="product_store.searchUser($event)"
        placeholder="Search By Email or Phone"
        data-testid="carts-attach_user"
        name="carts-attach_user"
        optionLabel="email"
        :pt="{
          token: {
            class: 'max-w-full',
          },
          removeTokenIcon: {
            class: 'min-w-max',
          },
          item: {
            style: {
              textWrap: 'wrap',
            },
          },
          panel: { class: 'w-16rem ' },
        }"
      >
      </AutoComplete>
    </div>

    <div class="flex justify-content-end gap-2">
      <Button
        type="button"
        label="Cancel"
        severity="secondary"
        @click="store.onHideUserDialog"
      ></Button>
      <Button
        type="button"
        label="Add"
        @click="store.addUserToGuestCart(store.item.user_object)"
      ></Button>
    </div>
  </Dialog>
</template>

<style scoped>
.cart_detail {
  display: flex;
  flex-direction: column;
}

.p-inputnumber-input {
  border: none !important;
  background: none !important;
  padding: 4px !important;
}

.filled-heart .pi pi-heart {
  background-color: red; /* Change this to your desired background color */
}
</style>

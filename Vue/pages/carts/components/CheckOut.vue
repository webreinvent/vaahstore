<script setup>
import { vaah } from "../../../vaahvue/pinia/vaah";
import { useCartStore } from "../../../stores/store-carts";
import VhField from "../../../vaahvue/vue-three/primeflex/VhField.vue";
import { onMounted, ref, computed, watchEffect, watch } from "vue";
import { useRoute } from "vue-router";
import { useRootStore } from "../../../stores/root";
import CardItem from "./CardItem.vue";
import Payment from "./payment.vue";
const route = useRoute();
const store = useCartStore();
const useVaah = vaah();
const root = useRootStore();

let route_params_id = null;
const base_url = ref("");

onMounted(async () => {
    document.title = 'Carts - Check-out';
    await store.loadAssets();
    base_url.value = root.ajax_url.replace('backend/store', '/');

  if (route.params && route.params.id) {
    route_params_id = route.params;
    await store.getItem(route.params.id);
    await store.onLoad(route);
    await store.getCartItemDetailsAtCheckout(route.params.id);
  }

  // await store.getList();
});

const orderParams = ref([]);
watch(
  () => store.is_same_as_shipping,
  (newValue) => {
    if (Array.isArray(newValue) && newValue.length === 0) {
      store.item_billing_address = null;
    }
  }
);

watchEffect(() => {
  const total_amount = store.total_mrp - store.discount_on_order;
  orderParams.value = {
    shipping_address: store.selected_shipping_address,
    total_amount: total_amount,
    payable: total_amount,
    discounts: store.discount_on_order,
    taxes: 0,
    delivery_fee: 0,
    cart_id: route.params.id,
    billing_address: store.item_billing_address
      ? store.item_billing_address
      : store.selected_billing_address,
    payment_method: store.cash_on_delivery,
    order_items: store.cart_item_at_checkout,
  };
});
</script>

<template>
  <div class="p-3 bg-gray-50">
    <div class="flex items-center gap-2">
      <Button
        @click="store.redirectToCart"
        size="small"
        label="Back"
        class="!px-1 !gap-1"
      >
        <Icon icon="si:arrow-left-duotone" width="24" height="24" />Back
      </Button>
      <div class="mr-1 ml-3" v-if="store.item">
        <span class="text-gray-950 text-base leading-5 font-semibold"
          >{{ store.item.user?.username }}-<span class="text-gray-400">{{
            store.item.user?.email
          }}</span></span
        >
      </div>
    </div>

    <div class="h-[1px] w-full bg-gray-100 mt-3" />

    <div class="flex gap-3 my-3">
      <div class="w-full">
        <Accordion :multiple="true" :activeIndex="[0, 1]" class="w-full">
          <AccordionTab
            v-if="
              store.cart_item_at_checkout && store.cart_item_at_checkout.length
            "
            :header="`Products (${store.cart_item_at_checkout.length})`"
            class="w-full"
          >
            <div class="flex flex-col gap-3">
              <div
                v-for="product in store.cart_item_at_checkout"
                :key="product.product_id"
                class="!"
              >
                <CardItem
                  :product="product"
                  :baseUrl="base_url + '/'"
                  :showVendor="true"
                  :showRating="true"
                />
              </div>
            </div>
            <!-- <div >
                        <DataTable :value="store.cart_item_at_checkout"
                                   dataKey="id"

                                   class="p-datatable-sm p-datatable-hoverable-rows"
                                   :nullSortOrder="-1"
                                   v-model:selection="store.action.items"
                                   stripedRows
                                   responsiveLayout="scroll">
                            <Column field="" header="" class="overflow-wrap-anywhere bg-gray-100"
                                    style="width:120px;">
                                <template #body="prop">
                                    <div class="flex">
                                        <div class="product_img">
                                            <div v-if="Array.isArray(prop.data.image_urls) && prop.data.image_urls.length > 0" class="bg-white rounded-lg p-2">
                                                <div v-for="(imageUrl, imgIndex) in prop.data.image_urls" :key="imgIndex">
                                                    <Image preview
                                                           :src="base_url + '/' + imageUrl"
                                                           alt="Error" width="64" />
                                                </div>
                                            </div>
                                            <div v-else>
                                                <Image  preview src="https://m.media-amazon.com/images/I/81hyHSHK7FL._AC_AA180_.jpg"
                                                     alt="Error" width="64"/>
                                            </div>
                                        </div>
                                        <div class="product_desc ml-3">
                                            <h4>{{
                                                    prop.data.pivot.cart_product_variation ? prop.data.name + '-' + prop.data.pivot.cart_product_variation : prop.data.name
                                                }}</h4>
                                            <p v-if="prop.data.pivot.quantity"><b>Qty:</b> {{ prop.data.pivot.quantity }}</p>
                                            <p v-if="prop.data.pivot.price !== null && prop.data.pivot.price !== undefined">
                                                <span v-html="store.assets?.store_default_currency"></span>{{ prop.data.pivot.price }}</p>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <template #empty>
                                <div class="text-center py-3">
                                    No records found.
                                </div>
                            </template>

                        </DataTable>

                        </div> -->
          </AccordionTab>

          <AccordionTab
            :header="store.accordionHeader"
            v-if="
              (store &&
                store.item &&
                store.item_user &&
                store.item_user_address &&
                store.shipping_addresses &&
                store.shipping_addresses.length === 0) ||
              store.show_new_address_tab
            "
          >
            <div>
              <VhField label="Country/Region">
                <AutoComplete
                  v-model="store.item_user_address.country"
                  value="id"
                  data-testid="warehouses-country"
                  :suggestions="store.country_suggestions"
                  @complete="store.searchCountry($event)"
                  :dropdown="true"
                  placeholder="Select Country"
                  forceSelection
                />
              </VhField>

              <VhField label="Full Name ">
                <InputText
                  class="w-full"
                  name="products-name"
                  data-testid="products-name"
                  placeholder="Enter Full Name "
                  v-model="store.item_user_address.name"
                />
              </VhField>

              <VhField label="Phone No.">
                <InputText
                  class="w-full"
                  name="products-phone"
                  data-testid="products-phone"
                  placeholder="Enter Phone No."
                  v-model="store.item_user_address.phone"
                />
              </VhField>
              <VhField label="Address">
                <InputText
                  class="w-full"
                  name="cart-email"
                  data-testid="cart-email"
                  placeholder="Enter Address (House No, Building, Street, Area)*"
                  v-model="store.item_user_address.address_line_1"
                />
              </VhField>

              <VhField label="PIN Code">
                <InputText
                  class="w-full"
                  name="cart-pin_code"
                  data-testid="cart-pin_code"
                  placeholder="Enter Pin Code"
                  v-model="store.item_user_address.pin_code"
                />
              </VhField>

              <VhField label="City">
                <InputText
                  class="w-full"
                  name="cart-city"
                  data-testid="cart-city"
                  placeholder="Enter City"
                  v-model="store.item_user_address.city"
                />
              </VhField>
              <VhField label="State">
                <InputText
                  class="w-full"
                  name="cart-address"
                  data-testid="cart-address"
                  placeholder="Enter State / Province / Region"
                  v-model="store.item_user_address.state"
                />
              </VhField>
            </div>
            <div class="flex justify-content-end gap-2">
              <Button
                v-if="store.shipping_addresses.length >= 1"
                type="button"
                label="Close"
                severity="secondary"
                @click="store.removeTab(index)"
              ></Button>
              <Button
                v-if="store.is_editing"
                type="button"
                label="Update"
                @click="
                  store.updateAddress(store.item_user_address, store.item_user)
                "
              ></Button>
              <Button
                v-if="!store.is_editing"
                type="button"
                label="Save"
                @click="
                  store.saveShippingAddress(
                    store.item_user_address,
                    store.item_user,
                    store.show_tab_for_billing ? 'billing' : null
                  )
                "
              ></Button>
            </div>
          </AccordionTab>

          <AccordionTab
            header="Shipping Details "
            v-if="
              store &&
              store.item &&
              store.item_user &&
              store.shipping_addresses &&
              store.shipping_addresses.length >= 1
            "
            class="!bg-black"
            :pt="{
              root: { class: '!bg-black' },
            }"
          >
            <!-- <div>
              <template
                v-for="(address, index) in store.displayedAddresses"
                :key="index"
              >
                <Card
                  :class="{
                    'selected-card': store.isSelectedShippingAddress(address),
                  }"
                  @click="store.setSelectedShippingAddress(address)"
                  :pt="{ content: { class: 'py-0' } }"
                >
                  <template #content>
                    <div class="flex align-items-center">
                      <RadioButton
                        v-model="store.selected_shipping_address"
                        :inputId="'address' + index"
                        :name="'address'"
                        :value="address"
                      />
                      <label :for="'address' + index" class="ml-2"
                        ><b>{{ address.name }}</b></label
                      >
                    </div>
                    <div class="p-2">
                      <p>{{ address.address_line_1 }}, {{ address.city }}</p>
                      <span>{{ address.country }}</span>
                    </div>
                    <div class="p-2">
                      <span>Mobile: </span><b>{{ address.phone }}</b>
                    </div>
                    <li
                      class="p-2"
                      v-if="store.isSelectedShippingAddress(address)"
                    >
                      Cash On Delivery Available
                    </li>
                    <div class="flex justify-content gap-2 mt-5">
                      <Button
                        type="button"
                        size="small"
                        label="Remove"
                        severity="secondary"
                        @click="store.removeAddress(address)"
                      ></Button>
                      <Button
                        type="button"
                        size="small"
                        label="Edit"
                        @click="store.editAddress(address, store.item_user)"
                      ></Button>
                    </div>
                  </template>
                </Card>
              </template>
            </div> -->

            <template
              v-for="(address, index) in store.displayedAddresses"
              :key="index"
            >
              <Card class="px-2 pb-3 !shadow-none rounded-xl bg-gray-100">
                <template #content>
                  <div class="flex justify-between gap-20 items-start">
                    <div>
                      <h3
                        class="font-bold text-base leading-5 text-gray-950 mb-1"
                      >
                        Address :
                      </h3>
                      <p class="font-bold text-xs leading-6">
                        <span class="text-gray-400">Street Address:</span>
                        <b class="text-gray-950">{{
                          address.address_line_1
                        }}</b>
                      </p>
                      <p class="font-bold text-xs leading-6">
                        <span class="text-gray-400">State:</span>
                        <b class="text-gray-950">{{ address.state }}</b>
                      </p>
                      <p class="font-bold text-xs leading-6">
                        <span class="text-gray-400">ZIP Code:</span>
                        <b class="text-gray-950">{{ address.zip }}</b>
                      </p>
                      <p class="font-bold text-xs leading-6">
                        <span class="text-gray-400">Country:</span>
                        <b class="text-gray-950">{{ address.country }}</b>
                      </p>
                      <p class="font-bold text-xs leading-6">
                        <span class="text-gray-400">Phone Number:</span>
                        <b class="text-gray-950">{{ address.phone }}</b>
                      </p>
                    </div>
                    <div class="">
                      <h3
                        class="font-bold text-base leading-5 text-gray-950 mb-1"
                      >
                        Expected Date :
                      </h3>
                      <p class="text-gray-400 font-bold text-xs">
                        {{ address.expected_date || "12 Feb 2025" }}
                      </p>
                      <p
                        class="text-[#0E9F6E] font-bold text-xs mt-1 whitespace-nowrap"
                      >
                        Cash On Delivery Available
                      </p>
                    </div>

                    <div class="flex gap-1">
                      <button
                        @click="store.editAddress(address)"
                        class="p-2 rounded hover:bg-gray-200"
                      >
                        <Icon
                          icon="mdi:pencil-outline"
                          width="18"
                          height="18"
                          class="text-gray-400"
                        />
                      </button>
                      <button
                        @click="store.removeAddress(address)"
                        class="p-2 rounded hover:bg-red-300"
                      >
                        <Icon
                          icon="mdi:trash-can-outline"
                          width="18"
                          height="18"
                          class="text-[#E02424]"
                        />
                      </button>
                    </div>
                  </div>

                  <!-- <div class="flex justify-between items-center mt-4">
        <a href="#" class="text-blue-600 font-medium">Add a new shipping address +</a>
        
      </div> -->
                </template>
              </Card>
            </template>

            <div class="flex justify-content-between mt-3">
              <Button
                class="!bg-none !text-[#3F83F8] font-bold text-sm"
                @click="store.toggleNewAddressTab"
                link
                :pt="{
                  root: { class: '!bg-none !p-0' },
                }"
              >
                Add a new shipping address +
              </Button>
              <Button
                v-if="store.showViewMoreButton"
                @click="store.showAllAddresses()"
                :label="`(${store.remainingAddressCount}) More Address`"
                :link="true"
              />
              <Button
                v-if="
                  !store.showViewMoreButton &&
                  store.shipping_addresses.length > 2
                "
                @click="store.hideAddressTab"
                :label="` Hide Address`"
                :link="true"
              />
            </div>
          </AccordionTab>

          <AccordionTab header="Billing Address ">
            <div
              v-if="store.selected_shipping_address"
              class="flex align-items-center mb-2"
            >
              <Checkbox
                v-model="store.is_same_as_shipping"
                inputId="sameAsShipping"
                name="sameAsShipping"
                value="1"
                size="large"
                @change="store.handleSameAsShippingChange()"
              />

              <label
                for="ingredient1"
                class="ml-2 font-bold text-base leading-5 text-gray-950"
                >Same as shipping address</label
              >
            </div>
            <div
              v-if="
                store &&
                store.item &&
                store.item_user &&
                store.user_saved_billing_addresses &&
                store.user_saved_billing_addresses.length >= 1 &&
                (!store.is_same_as_shipping ||
                  store.is_same_as_shipping.length === 0)
              "
            >
              <template
                v-for="(address, index) in store.displayedBillingAddresses"
                :key="index"
              >
                <Card
                  :class="{
                    'selected-card': store.isSelectedBillingAddress(address),
                  }"
                  @click="store.setSelectedBillingAddress(address)"
                  class="mt-2"
                  :pt="{ content: { class: 'py-0' } }"
                >
                  <template #content>
                    <div class="flex align-items-center">
                      <RadioButton
                        v-model="store.selected_billing_address"
                        :inputId="'address' + index"
                        :name="'address'"
                        :value="address"
                      />
                      <label :for="'address' + index" class="ml-2"
                        ><b>{{ address.name }}</b></label
                      >
                    </div>
                    <div class="p-2">
                      <p>{{ address.address_line_1 }}, {{ address.city }}</p>
                      <span>{{ address.country }}</span>
                    </div>
                    <div class="p-2">
                      <span>Mobile: </span><b>{{ address.phone }}</b>
                    </div>
                    <div class="flex justify-content gap-2 mt-5">
                      <Button
                        type="button"
                        label="Remove"
                        severity="secondary"
                        @click="store.removeAddress(address)"
                      ></Button>
                      <Button
                        type="button"
                        label="Edit"
                        @click="store.editAddress(address, store.item_user)"
                      ></Button>
                    </div>
                  </template>
                </Card>
              </template>
            </div>
            <div class="flex justify-content-between mt-3">
              <Button
                @click="store.toggleNewAddressTabForBilling('billing')"
                link
                class="text-gray-300 font-bold text-sm"
                :pt="{
                  root: { class: '!bg-none !p-0' },
                }"
              >
                Add a new billing address +
              </Button>
              <Button
                v-if="store.showViewMoreBillingAddressButton"
                @click="store.showAllBillingAddresses()"
                :label="`(${store.remainingAddressCountBilling}) More Address`"
                :link="true"
              />
              <Button
                v-if="
                  !store.showViewMoreBillingAddressButton &&
                  store.user_saved_billing_addresses &&
                  store.user_saved_billing_addresses.length > 2
                "
                @click="store.hideBillingAddressTab"
                :label="` Hide Address`"
                :link="true"
              />
            </div>
          </AccordionTab>
          <AccordionTab header="Payment">
            <!-- <div class="flex flex-column px-4 gap-2 max-w-14rem">
              <label
                for="payment_type"
                class="cursor-pointer flex align-items-center bg-gray-100 p-2 border-round"
              >
                <RadioButton
                  v-model="store.cash_on_delivery"
                  inputId="COD"
                  name="COD"
                  value="COD"
                />
                <span class="ml-2">Cash On Delivery</span>
              </label>
            </div> -->

            <Payment />
          </AccordionTab>
        </Accordion>
      </div>

      <!-- <div>
        <Card
          class="border-1 border-gray-200 w-25rem"
          :pt="{ content: { class: 'pb-0' } }"
        >
          <template #title>Check Summary</template>
          <template #content>
            <div class="flex justify-content-between">
              <p class="m-0">
                <b>Total MRP :</b>
              </p>
              <p class="m-0">₹{{ store.total_mrp }}</p>
            </div>
            <div class="flex justify-content-between">
              <p class="m-0">
                <b>Delivery :</b>
              </p>
              <p class="text-teal-500">Free</p>
            </div>
            <div class="flex justify-content-between">
              <p class="m-0">
                <b>Tax :</b>
              </p>
              <p>₹0</p>
            </div>
            <div class="flex justify-content-between">
              <p class="m-0">
                <b>Discount On MRP:</b>
              </p>
              <p class="text-teal-500">-₹{{ store.discount_on_order }}</p>
            </div>
            <div class="flex justify-content-between">
              <p class="m-0">
                <b>Coupon Discount :</b>
              </p>
              <p><Button label="Apply Coupon" link /></p>
            </div>
            <hr />
            <div class="flex justify-content-between">
              <p class="m-0">
                <b>Total Amount :</b>
              </p>
              <p>
                <b>₹{{ store.total_mrp - 0 }}</b>
              </p>
            </div>

            <div class="text-center">
              <Button
                label="Place an order"
                @click="store.placeOrder(orderParams)"
                class="bg-blue-700 text-white p-2 mt-3 border-round w-full"
              />
            </div>
          </template>
        </Card>

        <div class="table_bottom mt-4 border-1 border-gray-200">
          <InputText
            v-model="coupon_code"
            placeholder="Enter Coupon code"
            class="w-full"
          />
        </div>
      </div> -->

      <div>
        <Card
          class="border border-gray-200 rounded-2xl shadow-sm max-w-md mx-auto w-25rem pb-4 pt-1 px-2"
          :pt="{
            title: {
              class: 'font-bold text-2xl leading-7 text-gray-950 p-0 pb-4',
            },
          }"
        >
          <template #title>Order Summary</template>
          <template #content>
            <div class="space-y-4 mt-3">
              <div
                class="flex justify-between items-center font-bold text-base"
              >
                <span class="text-gray-500">Total MRP :</span>
                <span class="font-bold text-base">₹{{ store.total_mrp }}</span>
              </div>

              <div
                class="flex justify-between items-center font-bold text-base"
              >
                <span class="text-gray-500">Delivery :</span>
                <span class="text-[#0E9F6E] font-bold text-base">FREE</span>
              </div>

              <div
                class="flex justify-between items-center font-bold text-base"
              >
                <span class="text-gray-500">Tax :</span>
                <span class="font-bold text-base">$0</span>
              </div>

              <div
                class="flex justify-between items-center font-bold text-base"
              >
                <span class="text-gray-500">Discount on MRP :</span>
                <span class="text-[#0E9F6E] font-bold text-base"
                  >-${{ store.discount_on_order }}</span
                >
              </div>

              <div
                class="flex justify-between items-center font-bold text-base"
              >
                <span class="text-gray-500">Coupon Discount :</span>
                <Button
                  label="Apply Coupon"
                  link
                  class="p-0 !text-[#1958F7] font-bold text-base !bg-none !appearance-none"
                  :pt="{
                    root: { class: '!bg-none' },
                  }"
                  @click="showCouponInput = !showCouponInput"
                />
              </div>
            </div>
            <div>
                <Card class="border-1 border-gray-200 w-25rem" :pt="{content: {class: 'pb-0'} }">
                    <template #title>Check Summary</template>
                    <template #content>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Total MRP :</b>
                            </p>
                            <p class="m-0">
                                <span v-html="store.assets?.store_default_currency"></span>{{ store.total_mrp }}
                            </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Delivery :</b>
                            </p>
                            <p class="text-teal-500">
                                Free
                            </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                               <b>Tax :</b>
                            </p>
                            <p>
                                <span v-html="store.assets?.store_default_currency"></span>0
                             </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Discount On MRP:</b>
                            </p>
                            <p class="text-teal-500">
                                -<span v-html="store.assets?.store_default_currency"></span>{{store.discount_on_order}}
                            </p>
                        </div>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Coupon Discount :</b>
                            </p>
                            <p><Button  label="Apply Coupon" link /></p>
                        </div>
                        <hr>
                        <div class="flex justify-content-between">
                            <p class="m-0">
                                <b>Total Amount :</b>
                            </p>
                            <p>
                                <b><span v-html="store.assets?.store_default_currency"></span>{{ store.total_mrp - 0 }}</b>
                            </p>
                        </div>

            <div class="border-t border-gray-200 my-4 pt-4">
              <div
                class="flex justify-between items-center font-bold text-base"
              >
                <span>Total Amount :</span>
                <span>₹{{ store.total_mrp - store.discount_on_order }}</span>
              </div>
            </div>

            <div v-if="showCouponInput" class="mb-4">
              <InputText
                v-model="coupon_code"
                placeholder="Enter Coupon code"
                class="w-full p-2 border border-gray-200 rounded"
              />
            </div>

            <Button
              label="Place Order"
              @click="store.placeOrder(orderParams)"
              class="w-full bg-[#0E9F6E] hover:bg-green-600 text-white py-2 rounded-lg transition-colors"
              :pt="{ root: { class: 'border-none' } }"
            />
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<style scoped>
.selected-card {
  height: 250px;
  /* Add any additional styles as needed */
}
</style>

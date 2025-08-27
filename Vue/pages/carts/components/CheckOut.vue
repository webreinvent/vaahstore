<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useCartStore} from '../../../stores/store-carts'
import VhField from '../../../vaahvue/vue-three/primeflex/VhField.vue'
import {onMounted, ref, computed, watchEffect, watch} from "vue";
import {useRoute} from "vue-router";
import {useRootStore} from "../../../stores/root";
import CardItem from './CardItem.vue';
import Payment from './Payment.vue';
import PayPalButton from './PayPalButton.vue';
import StripeCheckout from './StripeCheckout.vue';
import axios from 'axios'
import {usePaymentMethodStore} from "../../../stores/store-paymentmethods";
const route = useRoute();
const store = useCartStore();
const useVaah = vaah();
const root = useRootStore();

let route_params_id = null;
const base_url = ref('');
const base2_url = document.getElementsByTagName('base')[0]?.getAttribute('href') || ''
const ajax_url = `${base2_url}/store/carts`
onMounted(async () => {
    document.title = 'Carts - Check-out';
    await store.loadAssets();
    base_url.value = root.ajax_url.replace('backend/store', '/');

    if (route.params && route.params.id) {
        route_params_id = route.params;
        await store.getItem(route.params.id);await store.onLoad(route);
        await store.getCartItemDetailsAtCheckout(route.params.id);
    }
     usePaymentMethodStore().getList();
    // await store.getList();

});

const orderParams = ref([]);
watch(() => store.is_same_as_shipping, (newValue) => {
    if (Array.isArray(newValue) && newValue.length === 0) {
        store.item_billing_address = null;
    }
});

const selectedPaymentMethod = ref(''); // default to cod or whatever you want

function onPaymentMethodChange(method) {
    selectedPaymentMethod.value = method;
}

watchEffect(() => {
    const total_amount = store.total_mrp - store.discount_on_order;
    orderParams.value = {
        shipping_address: store.selected_shipping_address,
        total_amount: total_amount,
        payable:  total_amount,
        vh_st_store_id:  root.selected_store_at_sidebar?.id,
        discounts: store.discount_on_order,
        taxes: 0,
        delivery_fee: 0,
        cart_id:route.params.id ,
        billing_address: store.item_billing_address ? store.item_billing_address : store.selected_billing_address,
        payment_method: selectedPaymentMethod.value,
        products:store.cart_item_at_checkout,
        vh_user_id:store.item_user?.id,
        currency: {
            vh_st_store_id: 51,
            name: "Indian Rupee",
            code: "USD",
            symbol: "&#x20B9;",
            is_default: 0,
            rate: 1
        }
    };
});
</script>

<template>
    <div class="bg-gray-100">

      <div class="flex items-center gap-2">
      <Button
        @click="store.redirectToCart"
        class="text-gray-500 font-semibold !gap-[2px]"
        size="small"
      >
        <Icon icon="si:arrow-left-duotone" width="24" height="24" />Back
      </Button>
      <div v-if="store.item">
        <span class="text-gray-950 text-base leading-5 font-semibold"
          >{{ store.item.user?.username }}-<span class="text-gray-400">{{
            store.item.user?.email
          }}</span></span
        >
      </div>
    </div>

    <div class="h-[1px] w-full bg-gray-200 mt-3 mb-2" />
    <div class="flex gap-3">
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
              >
                <CardItem
                  :product="product"
                  :baseUrl="base_url + '/'"
                  :showVendor="true"
                  :showRating="true"
                />
              </div>
            </div>
          </AccordionTab>



                    <AccordionTab :header="store.accordionHeader" v-if="(store && store.item && store.item_user && store.item_user_address && store.shipping_addresses && store.shipping_addresses.length===0) || store.show_new_address_tab">
                        <div >

                            <VhField label="Country/Region">
                                <AutoComplete v-model="store.item_user_address.country"
                                              value="id"

                                              data-testid="warehouses-country"
                                              :suggestions="store.country_suggestions"
                                              @complete="store.searchCountry($event)"
                                              :dropdown="true"
                                              placeholder="Select Country"
                                              forceSelection />

                            </VhField>

                            <VhField label="Full Name ">
                                <InputText class="w-full"
                                           name="products-name"
                                           data-testid="products-name"
                                           placeholder="Enter Full Name "
                                           v-model="store.item_user_address.name "/>
                            </VhField>

                            <VhField label="Phone No.">
                                <InputText class="w-full"
                                           name="products-phone"
                                           data-testid="products-phone"
                                           placeholder="Enter Phone No."
                                           v-model="store.item_user_address.phone"/>
                            </VhField>
                            <VhField label="Address">
                                <InputText class="w-full"
                                           name="cart-email"
                                           data-testid="cart-email"
                                           placeholder="Enter Address (House No, Building, Street, Area)*"
                                           v-model="store.item_user_address.address_line_1"/>
                            </VhField>

                            <VhField label="PIN Code">
                                <InputText class="w-full"
                                           name="cart-pin_code"
                                           data-testid="cart-pin_code"
                                           placeholder="Enter Pin Code"
                                           v-model="store.item_user_address.pin_code"/>
                            </VhField>

                            <VhField label="City">
                                <InputText class="w-full"
                                           name="cart-city"
                                           data-testid="cart-city"
                                           placeholder="Enter City"
                                           v-model="store.item_user_address.city"/>
                            </VhField>
                            <VhField label="State">
                                <InputText class="w-full"
                                           name="cart-address"
                                           data-testid="cart-address"
                                           placeholder="Enter State / Province / Region"
                                           v-model="store.item_user_address.state"/>
                            </VhField>
                        </div>
                        <div class="flex justify-content-end gap-2">
                            <Button v-if="store.shipping_addresses.length >= 1" type="button" label="Close" severity="secondary" @click="store.removeTab(index)"></Button>
                            <Button v-if="store.is_editing" type="button" label="Update" @click="store.updateAddress(store.item_user_address,store.item_user)"></Button>
                            <Button v-if="!store.is_editing" type="button" label="Save" @click="store.saveShippingAddress(store.item_user_address, store.item_user, store.show_tab_for_billing ? 'billing' : null)"></Button>
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

            <!-- Two cards per row on md+, one per row on mobile -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <template
                v-for="(address, index) in store.displayedAddresses"
                :key="index"
              >
                <Card
                  :class="[
                    'px-2 pb-3 !shadow-none rounded-xl bg-gray-100 h-full flex flex-col justify-between cursor-pointer transition-all',
                    store.selected_shipping_address === address ? 'ring-2 ring-[#1958F7] border-[#1958F7] bg-blue-50' : 'border border-transparent'
                  ]"
                  :pt="{ content: { class: 'py-0' } }"
                  @click="store.setSelectedShippingAddress(address)"
                >
                  <template #content>
                    <div>
                      <div class="flex items-start gap-3">
                        <div>
                          <h3 class="font-bold text-base leading-5 text-gray-950 mb-1">
                            Name : {{ address.name }}
                          </h3>
                          <p class="font-bold text-xs leading-6">
                            <span class="text-gray-400">Street Address:</span>
                            <b class="text-gray-950">{{ address.address_line_1 }}</b>
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
                      </div>

                    </div>
                    <!-- Edit/Delete Buttons -->
                    <div class="flex gap-2 mt-3 justify-end">
                      <button
                        @click.stop="store.editAddress(address)"
                        class="p-2 rounded hover:bg-gray-200"
                        title="Edit"
                      >
                        <Icon
                          icon="mdi:pencil-outline"
                          width="18"
                          height="18"
                          class="text-gray-400"
                        />
                      </button>
                      <button
                        @click.stop="store.removeAddress(address)"
                        class="p-2 rounded hover:bg-red-300"
                        title="Delete"
                      >
                        <Icon
                          icon="mdi:trash-can-outline"
                          width="18"
                          height="18"
                          class="text-[#E02424]"
                        />
                      </button>
                    </div>
                  </template>
                </Card>
              </template>
            </div>

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




                        <AccordionTab header="Billing Details ">
  <div v-if="store.selected_shipping_address" class="flex align-items-center mb-2">
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
    v-if="store && store.item && store.item_user && store.user_saved_billing_addresses && store.user_saved_billing_addresses.length >= 1 && (!store.is_same_as_shipping || store.is_same_as_shipping.length === 0)"
    class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2"
  >
    <template v-for="(address, index) in store.displayedBillingAddresses" :key="index">
      <Card
        :class="[
          'px-2 pb-3 !shadow-none rounded-xl bg-gray-100 h-full flex flex-col justify-between cursor-pointer transition-all',
          store.isSelectedBillingAddress(address) ? 'ring-2 ring-[#1958F7] border-[#1958F7] bg-blue-50' : 'border border-transparent'
        ]"
        :pt="{ content: { class: 'py-0' } }"
        @click="store.setSelectedBillingAddress(address)"
      >
        <template #content>
          <div>
            <div class="flex items-start gap-3">
              <div>
                <h3 class="font-bold text-base leading-5 text-gray-950 mb-1">
                  Name : {{ address.name }}
                </h3>
                <p class="font-bold text-xs leading-6">
                  <span class="text-gray-400">Street Address:</span>
                  <b class="text-gray-950">{{ address.address_line_1 }}</b>
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
            </div>
          </div>
          <!-- Edit/Delete Buttons -->
          <div class="flex gap-2 mt-3 justify-end">
            <button
              type="button"
              @click.stop="store.editAddress(address, store.item_user)"
              class="p-2 rounded hover:bg-gray-200"
              title="Edit"
            >
              <Icon icon="mdi:pencil-outline" width="18" height="18" class="text-gray-400" />
            </button>
            <button
              type="button"
              @click.stop="store.removeAddress(address)"
              class="p-2 rounded hover:bg-red-300"
              title="Delete"
            >
              <Icon icon="mdi:trash-can-outline" width="18" height="18" class="text-[#E02424]" />
            </button>
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
      v-if="!store.showViewMoreBillingAddressButton && store.user_saved_billing_addresses && store.user_saved_billing_addresses.length > 2"
      @click="store.hideBillingAddressTab"
      :label="` Hide Address`"
      :link="true"
    />
  </div>
</AccordionTab>
                    <AccordionTab header="Payment">
<!--                        <Payment :paymentMethods="paymentMethodStore.payment_methods @update:paymentMethod="onPaymentMethodChange"/>-->
                        <Payment
                            :paymentMethods="usePaymentMethodStore().list?.data"
                            @update:paymentMethod="onPaymentMethodChange"
                        />
                        <div v-if="selectedPaymentMethod === 'paypal'" class="w-full flex justify-start">
                            <div class="w-64"> <!-- adjust width here, e.g., w-64 for ~16rem -->
                                <PayPalButton :orderParams />
                            </div>
                        </div>
                        <div v-if="selectedPaymentMethod === 'stripe'"  >

                            <StripeCheckout :orderParams/>
                        </div>
                    </AccordionTab>
                </Accordion>
            </div>
            <div>
                <Card class="border border-gray-200 rounded-2xl shadow-sm max-w-md mx-auto w-25rem pb-4 pt-1 px-2"
                :pt="{
                    title: {
                    class: 'font-bold text-2xl leading-7 text-gray-950 p-0 pb-4',
                    },
                }">
                    <template #title>Check Summary</template>
                    <template #content>
                        <div class="space-y-4 mt-3">
              <div
                class="flex justify-between items-center font-bold text-base"
              >
                <span class="text-gray-500">Total MRP :</span>
                <span class="font-bold text-base"><span v-html="root.selected_store_at_sidebar?.default_currency.symbol"></span>{{ store.total_mrp }}</span>
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
                <span class="font-bold text-base"><span v-html="root.selected_store_at_sidebar?.default_currency.symbol"></span>0</span>
              </div>

              <div
                class="flex justify-between items-center font-bold text-base"
              >
                <span class="text-gray-500">Discount on MRP :</span>
                <span class="text-[#0E9F6E] font-bold text-base"
                  >-<span v-html="root.selected_store_at_sidebar?.default_currency.symbol"></span>{{ store.discount_on_order }}</span
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

            <div class="text-center">
                <Button label="Place an order" @click="store.placeOrder(orderParams)" :pt="{
                    root: { class: '!bg-[#0E9F6E] !py-2 !rounded-lg' },
                  }" class="bg-blue-700 text-white p-2 mt-3 border-round w-full rounde"/>
                        </div>
                    </template>
                </Card>

                <div class="table_bottom mt-4 border-1 border-gray-200">
                    <InputText v-model="coupon_code" placeholder="Enter Coupon code" class="w-full"/>
                </div>
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

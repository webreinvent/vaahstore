<script setup>
import {vaah} from '../../../vaahvue/pinia/vaah'
import {useProductStore} from '../../../stores/store-products'
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
    <Drawer v-model:visible="store.show_vendor_panel"  header="Product Price With Vendors" position="right" style="width:800px;">
        <template #header>
            <h2 style="font-weight: bold;" v-if="store.item" >{{store.product_name}}</h2>
        </template>




        <div class="flex align-items-center pt-1">
            <Checkbox v-model="show_preferred" :binary="true" />
            <label for="preferred-filter" class="ml-2"> Only Preferred Vendor </label>
        </div>

        <DataTable v-if="store.item " :value="filtered_vendors" style="border: 1px solid #ccc;margin-top:20px;"
                   :rows="20"
                   :paginator="true"
                   class="p-datatable-sm p-datatable-hoverable-rows">
            <Column header="ID" style="border: 1px solid #ccc;">
                <template #body="props">
                    {{props.data.id}}
                </template>
            </Column>
            <Column field="name" header="Vendor Name" style="border: 1px solid #ccc;">
                <template #body="props">
                    <div  >
                        {{props.data.name}}
                        <span v-if="props.data.is_default === 1">
                         <Badge severity="info">&nbsp;(Default)</Badge>
                     </span>
                    </div>
                </template>
            </Column>
            <Column header="Product Quantity" style="border: 1px solid #ccc;">
                <template #body="props">
                    <Badge :severity="props.data.quantity === 0 ? 'danger' : 'info'">
                        {{ props.data.quantity }}
                    </Badge>
                </template>
            </Column>

            <Column field="product_price_range" header="Price Range" style="border: 1px solid #ccc;">
                <template #body="props">
                    <template v-if="props.data.product_price_range.length">
                        <Badge severity="info">
                            {{ store.getPriceRangeOfProduct(props.data.product_price_range) }}
                        </Badge>
                    </template>
                    <template v-else>
                        <span >0</span>
                    </template>
                </template>
            </Column>

            <column field="Action" header="Is Preferred" style="border:1px solid #ccc;">
                <template #body="props">
                    <ToggleSwitch v-model.bool="props.data.is_preferred "
                                 :disabled=" (props.data.quantity === 0 || props.data.product_price_range.length===0 )"
                                 data-testid="products-vendors_list-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsPreferred(props.data)">
                    </ToggleSwitch>
                </template>
            </column>
            <template #empty="prop">

                <div  style="text-align: center;font-size: 12px; color: #888;">No records found.</div>

            </template>
        </DataTable>
    </Drawer>
</template>



<style scoped>

</style>

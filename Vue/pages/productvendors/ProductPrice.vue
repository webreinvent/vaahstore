<script setup>
import {onMounted, ref, watch} from "vue";
import { useProductVendorStore } from '../../stores/store-productvendors'
import { useRootStore } from '@/stores/root'
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';
import {vaah} from "../../vaahvue/pinia/vaah";


const store = useProductVendorStore();
const route = useRoute();
const root = useRootStore()
onMounted(async () => {
    if (route.params?.id && store.product_variation_list.length <= 0) {
        await store.getItem(route.params.id);
        await store.searchVariationOfProduct();
    }

});

//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};
//--------/form_menu
watch(() => store.query.selected_store, async (val) => {
    if (val) {
        store.toList();
    }
});

</script>
<template>

    <Panel :pt="root.panel_pt">

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <span>
                            Add Price
                        </span>
                    </div>

                </div>


            </template>
            <template #icons>


                <div class="p-inputgroup">
                    <Button label="Save"
                            v-if="store.item && store.item.id"
                            data-testid="productvendors-save"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            class="p-button-sm"
                            @click="store.itemAction('save-productprice')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            class="p-button-sm"
                            @click="store.itemAction('create-and-new')"
                            data-testid="productvendors-create-and-new"
                            icon="pi pi-save"/>

                    <Button data-testid="productvendors-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            class="p-button-sm"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="productvendors-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>

            <div v-if="store.product_variation_list.length > 0 " class="grid align-items-center ">
                <div class="col-5">
                    <div class="flex w-full mb-1">
                        <InputNumber v-model="store.item.all_price" inputId="integeronly" class="p-inputtext-sm p-0 w-full"
                                     placeholder="Enter Price"
                        />
                        <Button class="min-w-max" @click="store.fillAllPrices">Fill All </Button>
                    </div>

                </div>



            </div>
                <div v-if="store.item"
                     class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm overflow-auto">

                    <div v-if="store.product_variation_list && store.product_variation_list.length > 0">
                    <DataTable :value="store.product_variation_list"
                               dataKey="id"

                               class=""
                               scrollable scrollHeight="500px"
                               stripedRows
                               responsiveLayout="scroll">

                        <Column field="variations_name" header="Variations Name">
                            <template #body="props">
                                {{props.data.name}}
                            </template>
                        </Column>

                        <Column field="price" header="">
                            <template #header>
                                <span v-html="`Price (${root.selected_store_at_sidebar?.default_currency?.symbol || ''})`"></span>
                            </template>
                            <template #body="props">
                                <InputNumber
                                    :placeholder="'Enter Price '"
                                    :inputId="'minmax-buttons-' + props.index"
                                    :name="'productprices-amount-' + props.index"
                                    v-model="props.data.amount"
                                    mode="decimal"
                                    class=" h-2rem"
                                    :data-testid="'productprices-amount-' + props.index"
                                />
                            </template>
                        </Column>

                    </DataTable>
                    </div>
                    <div v-else  style="text-align: center;font-size: 15px; color: #888;">
                        Click to Create New Product Variation.
                            <Button label="Create Variation" severity="info" raised
                                    v-tooltip.top="'Create Variation'"
                                    style="border-width : 0; background: #4f46e5;cursor: pointer;"
                                    @click="store.toProductVariationCreate(store.item.product)"
                            />
                    </div>
                </div>


        </Panel>



</template>

<template>
    <card :pt="{
        content: {
            class: 'pb-0 py-0'
        }
    }">
        <template #content>
            <div class="flex align-items-center mb-3">
                <div class="flex align-items-center">
                    <InputGroup>
                        <Button
                            :disabled="store.selected_records.length <= 0"
                            type="button"
                            style="height:30px;"
                            label="Trash"
                            @click="store.deleteSelected()"
                            :badge="store.selected_records.length" />
                        <Button

                            type="button"
                            @click="toggleBulkMenuState"
                            data-testid="preview-import-bulk-menu"
                            aria-haspopup="true"
                            aria-controls="bulk_menu_state"
                            style="height:30px;"
                            class="p-button-sm">
                            <i class="pi pi-ellipsis-h"></i>
                        </Button>
                        <Menu ref="bulk_menu_state"
                              :model="store.list_bulk_menu"
                              :popup="true" />
                    </InputGroup>
                </div>
                <div class="align-items-center flex">
                    <label class="ml-2  text-sm align-items-center"><b>Override Data</b></label>
                    <ToggleSwitch
                        v-model="store.is_override"
                        class="p-inputswitch-sm ml-1"
                         />
                </div>

            </div>
            <DataTable
                         v-model:selection="store.selected_records"
                        :value="store.list.records"
                        paginator
                        :rows="20"
                       :rowsPerPageOptions="[10, 20, 50, 100]"
                       tableStyle="min-width: 50rem">

                <template v-if="store.list.records.length > 0 ">
                    <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                    <Column v-for="(col) of store.list.headers"
                            :key="col.field"
                            :field="col.field"
                            :header="store.updateColumnName(col.field)">


                        <template v-if="col.field==='is_active'" #body="{data}">
                            <div class="flex align-items-center">
                            <ToggleSwitch v-bind:false-value="0"
                                         v-bind:true-value="1"
                                         class="p-inputswitch-sm"
                                         name="import-active"
                                         data-testid="import-active"
                                         v-model="data.is_active"/>
                            <i v-if="getRowIndex(data) === 0"
                               class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                               @click="fillActiveAll(data)"
                               v-tooltip.top="'Click here to add same input for all the records'"
                               style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='is_featured_on_home_page'" #body="{data}">
                            <div class="flex align-items-center">
                            <ToggleSwitch v-bind:false-value="0"
                                         v-bind:true-value="1"
                                         class="p-inputswitch-sm"
                                         name="import-active"
                                         data-testid="import-active"
                                         v-model="data.is_featured_on_home_page"/>
                            <i v-if="getRowIndex(data) === 0"
                               class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                               @click="fillHomepageFeatured(data)"
                               v-tooltip.top="'Click here to add same input for all the records'"
                               style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='is_featured_on_category_page'" #body="{data}">

                            <div class="flex align-items-center">
                            <ToggleSwitch v-bind:false-value="0"
                                         v-bind:true-value="1"
                                         class="p-inputswitch-sm"
                                         name="import-active"
                                         data-testid="import-active"
                                         v-model="data.is_featured_on_category_page"/>
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillCategoryFeatured(data)"
                                   v-tooltip.top="'Click here to add same input for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>



                            <template v-if="col.field === 'description'"  #body="{ data }">
                            <span
                                v-if="data.description && data.description.length >30"
                                v-tooltip.top="store.showDescription(data.description)">
                                {{data.description.slice(0,30)}}...
                            </span>
                                <span v-else>{{data.description}}</span>
                            </template>



                        <template v-if="col.field==='vh_st_store_id'" #body="{ data }">
                            <div class="flex align-items-center">
                                <!-- Dropdown component -->
                                <Select
                                    class="w-full"
                                    v-model="data.vh_st_store_id"
                                    :options="store.assets.stores"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Select stores"
                                    :showClear="true"
                                    filter
                                    style="min-width:2rem; max-width: 150px;"
                                    :pt="{
                                        panel: {
                                            class: 'w-2rem'
                                        }
                                    }"
                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillStore(data)"
                                   v-tooltip.top="'Click here to add same store for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='vh_st_brand_id'" #body="{data}">
                            <div class="flex align-items-center">
                                <Select class="w-full"
                                          v-model="data.vh_st_brand_id"
                                          :options="store.assets.brands"
                                          optionLabel="name"
                                          optionValue="id"
                                          placeholder="Select brand"
                                          :showClear="true"
                                          filter
                                          style="min-width:150px; max-width: 150px"
                                          :pt="{
                                        panel: {
                                            class: 'w-2rem'
                                        }
                                    }"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillBrand(data)"
                                   v-tooltip.top="'Click here to add same brand for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_product_status'" #body="{data}">
                            <div class="flex align-items-center">
                                <Select class="w-full"
                                          v-model="data.taxonomy_id_product_status"
                                          :options="store.assets.taxonomy.status"
                                          optionLabel="name"
                                          optionValue="id"
                                          placeholder="Select status"
                                          :showClear="true"
                                          filter
                                          style="min-width:150px; max-width: 150px"
                                          :pt="{
                                                panel: {
                                                           class: 'w-2rem'
                                                        }
                                          }"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillStatus(data)"
                                   v-tooltip.top="'Click here to add same status for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_product_type'" #body="{data}">
                            <div class="flex align-items-center">
                                <Select class="w-full"
                                          v-model="data.taxonomy_id_product_type"
                                          :options="store.assets.taxonomy.types"
                                          optionLabel="name"
                                          optionValue="id"
                                          placeholder="Select types"
                                          :showClear="true"
                                          filter
                                          style="min-width:150px; max-width: 150px"
                                          :pt="{
                                                panel: {
                                                           class: 'w-2rem'
                                                        }
                                          }"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillType(data)"
                                   v-tooltip.top="'Click here to add same type for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='available_at'" #body="{data}">
                            <div class="flex align-items-center" style="width: 200px">


                                <DatePicker tabindex="0"
                                          :showIcon="true"
                                          class="w-full"
                                          name="preview-available_at"
                                          id="registered_at"
                                          value="registered_at"
                                          data-testid="preview-available_at"
                                          dateFormat="yy-mm-dd"
                                          placeholder="Select date"
                                          v-model="data.available_at"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillAvailableDate(data)"
                                   v-tooltip.top="'Click here to add same body style for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='launch_at'" #body="{data}">
                            <div class="flex align-items-center" style="width: 200px">
                                <DatePicker tabindex="0"
                                          :showIcon="true"
                                          class="w-full"
                                          name="preview-launch_at"
                                          id="registered_at"
                                          value="registered_at"
                                          data-testid="preview-launch_at"
                                          dateFormat="yy-mm-dd"
                                          placeholder="Select date"
                                          v-model="data.launch_at"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillLaunchDate(data)"
                                   v-tooltip.top="'Click here to add same launch date for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>


                    </Column>
                    <Column :field="'action'" :header="'Action'">
                        <template #body="{ data }">
                            <Button icon="pi pi-trash"
                                    class="p-button-tiny p-button-danger p-button-text"
                                    @click="store.removeRecord(data)" />
                        </template>
                    </Column>

                </template>

                <template v-else>
                    <div class="text-center">No records found</div>
                </template>


            </DataTable>

        </template>

        <template #footer
                  :pt="{
        content: {
            class: 'py-0 pb-0'
        }
    }"
        >
            <div class="flex justify-content-between">
                <Button label="Back" @click="store.prevPage()" icon="pi pi-angle-left" />
                <Button
                        :disabled="!store.list.records.length"
                        :loading="store.is_importing" label="Next" @click="store.importData()"
                        icon="pi pi-angle-right" icon-pos="right" />
            </div>
        </template>
    </card>

</template>

<script setup>
import {onMounted, ref, watch,computed,reactive} from "vue";
import {useImportStore} from "../../../stores/store-import";
import {vaah} from '../../../vaahvue/pinia/vaah.js';
const store = useImportStore();
const selectedRecordsCount = computed(() => {
    if (!store.list.records.some(record => record.selected)) {
        return '0';
    } else {
        return store.list.records.filter(record => record.selected).length;
    }
});

store.list.records.forEach(record => {
    watch(() => record.selected, () => {
        store.selectAllRecords();
    });
});

const totalRecords = computed(() => store.list.records.length);


const bulk_menu_state = ref();
const toggleBulkMenuState = (event) => {
    bulk_menu_state.value.toggle(event);
};

const getRowIndex = (data) => {
    return store.list.records.findIndex(record => record === data);
};


const fillStore = (store) => {
    const first_store = store.vh_st_store_id;
    store.list.records.forEach(record => {
        record.vh_st_store_id = first_store;
    });
    vaah().toastSuccess(['Action was successfull']);
};
const fillLaunchDate = (year) => {
    const launch_at_datetime = year.launch_at;
    store.list.records.forEach(record => {
        record.launch_at = launch_at_datetime;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillAvailableDate = (year) => {
    const available_at_datetime = year.available_at;
    store.list.records.forEach(record => {
        record.available_at = available_at_datetime;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillBrand = (brand) => {
    const first_brand = brand.vh_st_brand_id;
    store.list.records.forEach(record => {
        record.vh_st_brand_id = first_brand;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillStatus = (status) => {
    const first_status = status.taxonomy_id_product_status;
    store.list.records.forEach(record => {
        record.taxonomy_id_product_status = first_status;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillType = (year) => {
    const first_year = year.taxonomy_id_product_type;
    store.list.records.forEach(record => {
        record.taxonomy_id_product_type = first_year;
    });
    vaah().toastSuccess(['Action was successfull']);
};
const fillCategoryFeatured = (featured) => {
    const is_category_featured = featured.is_featured_on_category_page;
    store.list.records.forEach(record => {
        record.is_featured_on_category_page = is_category_featured;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillHomepageFeatured = (featured) => {
    const is_home_featured = featured.is_featured_on_home_page;
    store.list.records.forEach(record => {
        record.is_featured_on_home_page = is_home_featured;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillActiveAll = (active) => {
    const is_active = active.is_active;
    store.list.records.forEach(record => {
        record.is_active = is_active;
    });
    vaah().toastSuccess(['Action was successfull']);
};






onMounted(async () => {
    store.redirectIfInvalid();
    store.getListBulkMenu();

})
</script>

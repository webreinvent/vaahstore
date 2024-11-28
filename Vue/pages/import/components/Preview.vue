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
                            data-testid="inventories-import-bulk-menu"
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
                    <InputSwitch
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
                    {{store.list.headers}}
                    <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                    <Column v-for="(col) of store.list.headers"
                            :key="col.field"
                            :field="col.field"
                            :header="store.updateColumnName(col.field)">


                        <template v-if="col.field==='is_active'" #body="{data}">
                            <InputSwitch v-bind:false-value="0"
                                         v-bind:true-value="1"
                                         class="p-inputswitch-sm"
                                         name="import-active"
                                         data-testid="import-active"
                                         v-model="data.is_active"/>
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
                                <Dropdown
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
                                   @click="fillSupplier(data)"
                                   v-tooltip.top="'Click here to add same supplier for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='vh_st_brand_id'" #body="{data}">
                            <div class="flex align-items-center">
                                <Dropdown class="w-full"
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
                                   @click="fillMake(data)"
                                   v-tooltip.top="'Click here to add same make for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_product_status'" #body="{data}">
                            <div class="flex align-items-center">
                                <Dropdown class="w-full"
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
                                   @click="fillModel(data)"
                                   v-tooltip.top="'Click here to add same model for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_product_type'" #body="{data}">
                            <div class="flex align-items-center">
                                <Dropdown class="w-full"
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
                                   @click="fillYear(data)"
                                   v-tooltip.top="'Click here to add same year for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='available_at'" #body="{data}">
                            <div class="flex align-items-center" style="width: 200px">


                                <Calendar tabindex="0"
                                          :showIcon="true"
                                          class="w-full"
                                          name="products-registered_at"
                                          id="registered_at"
                                          value="registered_at"
                                          data-testid="products-registered_at"
                                          dateFormat="yy-mm-dd"
                                          placeholder="Select date"
                                          v-model="data.available_at"

                                ></Calendar>
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillBodyStyle(data)"
                                   v-tooltip.top="'Click here to add same body style for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='launch_at'" #body="{data}">
                            <div class="flex align-items-center" style="width: 200px">
                                <Calendar tabindex="0"
                                          :showIcon="true"
                                          class="w-full"
                                          name="products-registered_at"
                                          id="registered_at"
                                          value="registered_at"
                                          data-testid="products-registered_at"
                                          dateFormat="yy-mm-dd"
                                          placeholder="Select date"
                                          v-model="data.launch_at"

                                ></Calendar>
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillFirstYear(data)"
                                   v-tooltip.top="'Click here to add same first year for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_last_year'" #body="{data}">
                            <div class="flex align-items-center">
                                <Dropdown class="w-full"
                                          v-model="data.taxonomy_id_last_year"
                                          :options="store.assets.taxonomy.last_year"
                                          optionLabel="name"
                                          optionValue="id"
                                          placeholder="Select last year"
                                          :showClear="true"
                                          style="min-width:150px; max-width: 150px"
                                          filter
                                          :pt="{
                                                panel: {
                                                           class: 'w-2rem'
                                                        }
                                          }"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillLastYear(data)"
                                   v-tooltip.top="'Click here to add same last year for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_section'" #body="{data}">
                            <div class="flex align-items-center">
                                <Dropdown class="w-full"
                                          v-model="data.taxonomy_id_section"
                                          :options="store.assets.taxonomy.section"
                                          optionLabel="name"
                                          optionValue="id"
                                          placeholder="Select section"
                                          :showClear="true"
                                          style="min-width:150px; max-width: 150px"
                                          filter
                                          :pt="{
                                                panel: {
                                                           class: 'w-2rem'
                                                        }
                                          }"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillSection(data)"
                                   v-tooltip.top="'Click here to add same section for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_part_type'" #body="{data}">
                            <div class="flex align-items-center">
                                <Dropdown class="w-full"
                                          v-model="data.taxonomy_id_part_type"
                                          :options="store.assets.taxonomy.part_type"
                                          optionLabel="name"
                                          optionValue="id"
                                          placeholder="Select part type"
                                          :showClear="true"
                                          style="min-width:150px; max-width: 150px"
                                          filter
                                          :pt="{
                                                panel: {
                                                           class: 'w-2rem'
                                                        }
                                          }"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillPartType(data)"
                                   v-tooltip.top="'Click here to add same part type for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_class'" #body="{data}">
                            <div class="flex align-items-center">
                                <Dropdown class="w-full"
                                          v-model="data.taxonomy_id_class"
                                          :options="store.assets.taxonomy.class"
                                          optionLabel="name"
                                          optionValue="id"
                                          placeholder="Select class"
                                          :showClear="true"
                                          style="min-width:150px; max-width: 150px"
                                          filter
                                          :pt="{
                                                panel: {
                                                           class: 'w-2rem'
                                                        }
                                          }"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillClass(data)"
                                   v-tooltip.top="'Click here to add same class for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_location'" #body="{data}">
                            <div class="flex align-items-center">
                                <Dropdown class="w-full"
                                          v-model="data.taxonomy_id_location"
                                          :options="store.assets.taxonomy.location"
                                          optionLabel="name"
                                          optionValue="id"
                                          placeholder="Select location"
                                          :showClear="true"
                                          style="min-width:150px; max-width: 150px"
                                          filter
                                          :pt="{
                                                panel: {
                                                           class: 'w-2rem'
                                                        }
                                          }"

                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillLocation(data)"
                                   v-tooltip.top="'Click here to add same location for all the records'"
                                   style="font-size: 18px;"></i>
                            </div>
                        </template>

                        <template v-if="col.field==='taxonomy_id_status'" #body="{data}">
                            <div class="flex align-items-center">
                                <Dropdown class="w-full"
                                          v-model="data.taxonomy_id_status"
                                          :options="store.assets.taxonomy.status"
                                          optionLabel="name"
                                          optionValue="id"
                                          placeholder="Select status"
                                          :showClear="true"
                                          style="min-width:150px; max-width: 150px"
                                          filter
                                          :pt="{
                                                panel: {
                                                           class: 'w-2rem'
                                                        }
                                          }"
                                />
                                <i v-if="getRowIndex(data) === 0"
                                   class="pi pi-arrow-circle-down ml-2 cursor-pointer"
                                   @click="fillStatus(data)"
                                   v-tooltip.top="'Click here to add same location for all the records'"
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
            <div class="grid grid-nogutter justify-content-between">
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


const fillSupplier = (supplier) => {
    const first_supplier = supplier.sm_supplier_id;
    store.list.records.forEach(record => {
        record.sm_supplier_id = first_supplier;
    });
    vaah().toastSuccess(['Action was successfull']);
};


const fillMake = (make) => {
    const first_make = make.taxonomy_id_make;
    store.list.records.forEach(record => {
        record.taxonomy_id_make = first_make;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillModel = (model) => {
    const first_model = model.taxonomy_id_model;
    store.list.records.forEach(record => {
        record.taxonomy_id_model = first_model;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillYear = (year) => {
    const first_year = year.taxonomy_id_year;
    store.list.records.forEach(record => {
        record.taxonomy_id_year = first_year;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillBodyStyle = (body_style) => {
    const first_body_style = body_style.taxonomy_id_body_style;
    store.list.records.forEach(record => {
        record.taxonomy_id_body_style = first_body_style;
    });

    vaah().toastSuccess(['Action was successfull']);
};

const fillFirstYear = (year) => {
    const first_year = year.taxonomy_id_first_year;
    store.list.records.forEach(record => {
        record.taxonomy_id_first_year = first_year;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillLastYear = (year) => {
    const last_year_id = year.taxonomy_id_last_year;
    store.list.records.forEach(record => {
        record.taxonomy_id_last_year = last_year_id;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillSection = (section) => {
    const section_id = section.taxonomy_id_section;
    store.list.records.forEach(record => {
        record.taxonomy_id_section = section_id;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillPartType = (part_type) => {
    const part_type_id = part_type.taxonomy_id_part_type;
    store.list.records.forEach(record => {
        record.taxonomy_id_part_type = part_type_id;
    });
    vaah().toastSuccess(['Action was successfull']);
};


const fillLocation = (location) => {
    const location_id = location.taxonomy_id_location;
    store.list.records.forEach(record => {
        record.taxonomy_id_location = location_id;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillClass = (c) => {
    const class_id = c.taxonomy_id_class;
    store.list.records.forEach(record => {
        record.taxonomy_id_class = class_id;
    });
    vaah().toastSuccess(['Action was successfull']);
};

const fillStatus = (status) => {
    const status_id = status.taxonomy_id_status;
    store.list.records.forEach(record => {
        record.taxonomy_id_status = status_id;
    });
    vaah().toastSuccess(['Action was successfull']);
};


onMounted(async () => {
    store.redirectIfInvalid();
    store.getListBulkMenu();

})
</script>

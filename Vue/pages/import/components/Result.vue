<template>
    <Card>
        <template #title>Result</template>
        {{store.result}}
        <template #content>
            <DataTable :value="store.result" tableStyle="min-width: 50rem">
                <Column field="total_records" header="Total Records"></Column>
                <Column field="invalid_records_count" header="Invalid Records">
                    <template #body="prop">
                        <div>
                            <span class="text-red-600">{{prop.data.invalid_records_count}}</span>
                            <Button v-if="prop.data.invalid_records_count"
                                    class="p-button-text"
                                    icon="pi pi-download"
                                    :icon-class="{ 'text-red-600': prop.data.invalid_records_count > 0 }"
                                    @click="store.downloadInvalidRecords(prop.data.csv_content)"
                            />
                        </div>
                    </template>
                </Column>
                <Column field="imported_records" header="Imported Records">
                    <template #body="prop">
                        <div>
                            <span
                                  :class="{ 'text-green-600': prop.data.imported_records > 0 }"
                            >
                                {{prop.data.imported_records}}
                            </span>
                        </div>
                    </template>

                </Column>

                <Column field="override_records_count" header="Override Records">
                    <template #body="prop">
                        <div>
                            <span
                                :class="{ 'text-green-600': prop.data.override_records_count > 0 }"
                            >
                                {{prop.data.override_records_count}}
                            </span>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </template>
        <template #footer>
            <div class="grid grid-nogutter justify-content-between mt-2">
                <div class="flex">
                    <Button
                        label="Restart"
                        class="p-button-success"
                        @click="store.restart()"
                        icon="pi pi-refresh"
                        icon-pos="left"
                    />
                    <Button
                        class="ml-2"
                        label="Back to Inventory"
                        @click="store.toProducts()"
                        icon="pi pi-angle-left" />
                </div>

            </div>
        </template>

    </Card>

</template>

<script setup>
import {useImportStore} from "../../../stores/store-import";
import {onMounted} from "vue";

const store = useImportStore();

onMounted(() => {
    store.redirectIfInvalid();
});
</script>

<style>
.p-card-title{
    padding : 1rem;
    border-bottom: 1px solid #D3D3D3;
}
</style>

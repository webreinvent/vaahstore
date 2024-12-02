<template>
    <Card :pt="{
        content: {
            class: 'pb-0'
        }
    }">
        <template #content >
<!--            {{store.mapped_fields}}-->
            <DataTable :value="store.mapped_fields" tableStyle="min-width: 50rem">
                <Column field="label" header="Data Columns" class="max-w-min">saas</Column>
                <Column header="CSV Headers">
                    <template #body="slotProps">
                        <Dropdown
                            v-model="slotProps.data.csv_field_index"
                            :options="store.csv_fields"
                            :optionLabel="(option) => store.normaliseLabel(option.label)"
                            option-value="index"
                            placeholder="Select a Header" class="w-full md:w-14rem" />
                    </template>
                </Column>
            </DataTable>
        </template>

        <template #footer>
            <div class="grid grid-nogutter justify-content-between">
                <Button label="Back" @click="store.prevPage()" icon="pi pi-angle-left" />
                <Button label="Next" @click="nextPage()" icon="pi pi-angle-right" icon-pos="right" />
            </div>
        </template>
    </Card>
</template>

<script setup>
import {onMounted, ref} from "vue";
import {useImportStore} from "../../../stores/store-import";
const store = useImportStore();
//-----------------onMounted
onMounted(() => {
    store.redirectIfInvalid();
})

//-----------------functions
async function nextPage(){


    store.select_all_record = false;
    await store.preview();
    await store.selectTaxonomy();
     store.nextPage();

}

</script>

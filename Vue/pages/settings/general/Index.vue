<script setup>
import { onMounted } from "vue";
import { useRoute } from 'vue-router';
import {useSettingStore} from '../../../stores/store-settings'
import { vaah } from '../../../vaahvue/pinia/vaah';
import SampleSeed from './components/SampleSeed.vue'


const store = useSettingStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();
onMounted(async () => {

   document.title = 'Settings - Store';

});
</script>

<template>

    <div style="margin-top: 1rem">
        <Card>
            <template #header>
                <div class="flex justify-content-between align-items-center w-full">
                    <h5 class="font-semibold text-lg">Create Bulk Records For VaahStore Cruds</h5>
                </div>
            </template>

            <template #content>
                <Accordion :multiple="true" :activeIndex="store.active_index" id="accordionTabContainer">
                    <AccordionTab>
                        <template #header>
                            <div class="w-full">
                                <div>
                                    <h5 class="font-semibold text-sm">VaahStore Cruds</h5>
                                    <p class="text-color-secondary text-xs">
                                        Generate Bulk Records For All VaahStores Crud
                                    </p>
                                </div>
                            </div>
                        </template>

                        <SampleSeed />
                    </AccordionTab>
                </Accordion>
            </template>
        </Card>
    </div>
</template>

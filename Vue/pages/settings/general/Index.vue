<script setup>
import { onMounted } from "vue";
import { useRoute } from 'vue-router';
import {useSettingStore} from '../../../stores/store-settings'
import { vaah } from '../../../vaahvue/pinia/vaah';
import SampleSeed from './components/SampleSeed.vue'
import ChartsDateFilter from './components/ChartsDateFilter.vue'


const store = useSettingStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();
onMounted(async () => {

   document.title = 'Settings - Store';
});
</script>

<template>

    <div>
        <Card>
            <template #header>
                <div class="flex justify-content-between align-items-center w-full">
                    <h5 class="font-semibold text-lg">General Setting</h5>
                </div>
            </template>

            <template #content>
                <Accordion :multiple="true" :activeIndex="store.active_index" id="accordionTabContainer">
                    <AccordionPanel>
                        <AccordionHeader>
                            <div class="w-full">
                                <div>
                                    <h5 class="font-semibold text-sm">Sample Data</h5>
                                    <p class="text-color-secondary text-xs">
                                        Generate Sample Records For All VaahStore Crud
                                    </p>
                                </div>
                            </div>
                        </AccordionHeader>
                        <AccordionContent>
                            <SampleSeed />
                        </AccordionContent>
                    </AccordionPanel>
                </Accordion>
                <Accordion :multiple="true" :activeIndex="store.active_index" id="accordionTabContainer">
                    <AccordionPanel>
                        <AccordionHeader>
                            <div class="w-full">
                                <div>
                                    <h5 class="font-semibold text-sm">Charts Date Filters</h5>
                                    <p class="text-color-secondary text-xs">
                                        Set Date Filter For All VaahStore Charts
                                    </p>
                                </div>
                            </div>
                        </AccordionHeader>
                        <AccordionContent>
                            <ChartsDateFilter />
                        </AccordionContent>
                    </AccordionPanel>
                </Accordion>
            </template>
        </Card>
    </div>
</template>

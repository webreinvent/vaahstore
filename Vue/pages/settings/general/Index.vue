<script setup>
import { onMounted } from "vue";
import { useRoute } from 'vue-router';
import {useSettingStore} from '../../../stores/store-settings'
import { vaah } from '../../../vaahvue/pinia/vaah';
import SampleSeed from './components/SampleSeed.vue'
import DashboardDateFilter from './components/DashboardDateFilter.vue'


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
                    <AccordionTab>
                        <template #header>
                            <div class="w-full">
                                <div>
                                    <h5 class="font-semibold text-sm">Sample Data</h5>
                                    <p class="text-color-secondary text-xs">
                                        Generate Sample Records For All VaahStore Crud
                                    </p>
                                </div>
                            </div>
                        </template>

                        <SampleSeed />
                    </AccordionTab>
                </Accordion>
                <Accordion :multiple="true" :activeIndex="store.active_index" id="accordionTabContainer">
                    <AccordionTab>
                        <template #header>
                            <div class="w-full">
                                <div>
                                    <h5 class="font-semibold text-sm">Date & Time</h5>
                                    <p class="text-color-secondary text-xs">
                                        Global Date and time filter for Charts
                                    </p>
                                </div>
                            </div>
                        </template>

                        <DashboardDateFilter/>
                    </AccordionTab>
                </Accordion>
            </template>
        </Card>
    </div>
</template>

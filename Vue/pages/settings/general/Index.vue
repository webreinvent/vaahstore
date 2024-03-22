<script setup>
import { onMounted } from "vue";
import { useRoute } from 'vue-router';
import {useSettingStore} from '../../../stores/store-settings'
import { vaah } from '../../../vaahvue/pinia/vaah';
import ModuleSettings from './components/ModuleSettings.vue';
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
<!--    <div>
        <Card>
            <template #header>
                <div class="flex justify-content-between align-items-center w-full">
                    <h5 class="font-semibold text-lg">General Settings</h5>
                    <div>
                        <Button label="Expand all" class="p-button-sm mr-2" @click="store.expandAll"></Button>
                        <Button label="Collapse all" class="p-button-sm" @click="store.collapseAll"></Button>
                    </div>
                </div>
            </template>

            <template #content>
                <Accordion :multiple="true" :activeIndex="store.active_index" id="accordionTabContainer">
                    <AccordionTab>
                        <template #header>
                            <div class="w-full">
                                <div>
                                    <h5 class="font-semibold text-sm">Module Settings</h5>
                                    <p class="text-color-secondary text-xs">
                                       Basic settings of Module
                                    </p>
                                </div>
                            </div>
                        </template>

                        <ModuleSettings />
                    </AccordionTab>
                </Accordion>
            </template>
        </Card>
    </div>-->

    <div style="margin-top: 1rem">
        <Card>
            <template #header>
                <div class="flex justify-content-between align-items-center w-full">
                    <h5 class="font-semibold text-lg">Create Bulk Records For Vaah Store Cruds</h5>
                </div>
            </template>

            <template #content>
                <Accordion :multiple="true" :activeIndex="store.active_index" id="accordionTabContainer">
                    <AccordionTab>
                        <template #header>
                            <div class="w-full">
                                <div>
                                    <h5 class="font-semibold text-sm">Stores Crud</h5>
                                    <p class="text-color-secondary text-xs">
                                        Generate Sample Records For Stores Crud
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

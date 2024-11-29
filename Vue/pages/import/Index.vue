
<template>
    <div>
<!--{{store.steps}}-->
<!--        {{store.pageIndex}}-->
<!--        <Steps :model="store.steps"-->
<!--               aria-label="Form Steps"-->
<!--               class="steps-custom mb-5"-->
<!--               v-model:activeStep="store.pageIndex"-->
<!--        />-->
        <Steps :model="filteredSteps"
               aria-label="Form Steps"
               class="steps-custom mb-5"
               v-model:activeStep="store.pageIndex"
        />
        <router-view v-slot="{Component}">
            <keep-alive>
                <component :is="Component" />
            </keep-alive>
        </router-view>
    </div>
</template>

<script setup>
import {onMounted, ref,computed, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import { useToast } from "primevue/usetoast";
import {useImportStore} from "../../stores/store-import";
const store = useImportStore();
const route = useRoute();

onMounted(async () => {
    await store.getAssets();
    await store.onLoad(route);
});

watch(() => route.path,  () => {
    store.setActivePageIndex(route.path);
});
// const filteredSteps = computed(() => {
//     if (route.path === "/import/export") {
//         return store.steps.filter(step => step.label === 'Export');
//     } else {
//         return store.steps; // Show all steps if not on the export page
//     }
// });

const filteredSteps = computed(() => {
    // Only show the "Export" step if the current route is '/import/export'
    if (route.path === "/import/export") {
        return store.steps.filter(step => step.label === 'Export');
    }
    // Otherwise, return all steps (excluding "Export")
    return store.steps.filter(step => step.label !== 'Export');
});

</script>

<style scoped>
::v-deep(.p-steps .p-steps-item .p-menuitem-link ) {
    background: transparent;
}

::v-deep(.p-steps .p-steps-item:before  ) {
    content: " ";
    border-top: 3px solid #dee2e6;
    width: 100%;
    top: 50%;
    left: 0;
    display: block;
    position: absolute;
    margin-top: -1rem;
}
</style>

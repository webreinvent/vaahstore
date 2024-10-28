<template>
    <div>
        <apexchart
            v-bind="$attrs"
            :options="chartOptions"
            :series="chartSeries"

        />
    </div>
</template>

<script setup>
import { ref, defineProps, watch } from 'vue';

defineOptions({
    inheritAttrs: false,
})

// Define props to make the component dynamic
const props = defineProps({
    type: {
        type: String,
        default: 'bar'
    },
    stacked: {
        type: Boolean,
        default: false
    },
    stackType: {
        type: String,
    },
    title: {
        type: String,
        default: 'Bar Chart'
    },
    // height: {
    //     type: [Number, String],
    //     default: 'auto'
    // },
    // width: {
    //     type: [Number, String],
    //     default: '100%'
    // },
    chartOptions: {
        type: Object,
        default: () => ({}),
    },
    chartSeries: {
        type: Array,
        required: true,
    },
    // colors: {
    //     type: Array,
    //     default: () => [] // Ensure a default empty array
    // },
    labels: {
        type: Array,
        default: () => [] // Ensure a default empty array
    },
    // titleAlign: {
    //     type: String,
    //     default: 'center'
    // },
    // horizontal: {
    //     type: Boolean,
    //     default: false,
    // },
    // dataLabelsPosition: {
    //     type: String,
    //     default: 'top',
    // },
});

// Chart type prop
const chartType = ref(props.type);

// Chart options and series using refs to make them reactive
const chartOptions = ref({
    chart: {

        stacked: props.stacked,
        stackType: props.stackType,
    },
    // plotOptions: {
    //     bar: {
    //         horizontal: props.horizontal,
    //         // stacked: props.stacked,
    //         dataLabels: {
    //             position: props.dataLabelsPosition,
    //         },
    //     },
    // },
    yaxis: {
        title: {
            text: props.chartOptions.yaxisTitle || 'Count', // Provide default Y-axis title
        },
    },
    xaxis: {
        categories: props.chartOptions.categories || []
    },
    // labels: props.labels,
    title: {
        text: props.title,
        align: props.titleAlign
    },
    // dataLabels: {
    //     enabled: true
    // },
    // colors: props.colors,
});

// Define chartSeries as a reactive ref
const chartSeries = ref(props.chartSeries);

// Watch for prop changes to update chart options and series dynamically
watch(() => props.chartOptions, (newOptions) => {
    chartOptions.value = { ...chartOptions.value, ...newOptions };
}, { immediate: true });

watch(() => props.chartSeries, (newSeries) => {
    chartSeries.value = newSeries; // Update the chart series reactively
}, { immediate: true });


</script>

<style scoped>
/* Add any component-specific styles here */
</style>

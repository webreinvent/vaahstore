<template>

    <div>

        <!-- <pre>{{ chartOptions }}</pre> -->

        <apexchart


            :options="chartOptions"

            :series="chartSeries"

            v-bind="$attrs"



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



    chartOptions: {

        type: Object,

        default: () => ({}),

    },

    chartSeries: {

        type: Array,

        required: true,

    },



    titleAlign: {

        type: String,

        default: 'center'

    },



});



// Chart options and series using refs to make them reactive

const chartOptions = ref({

    chart: {

        background: 'transparent',

        stacked: props.stacked,

        stackType: props.stackType,

    },

    plotOptions: {

        bar: { },

    },

    xaxis: {

        categories: props.chartOptions.categories || []

    },

    yaxis: {

        title: {

            text: props.chartOptions.yaxisTitle ,

        },

    },

    title: {

        text: props.title,

        align: props.titleAlign

    },



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

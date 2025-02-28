<template>

        <h5 class="text-xs mb-2">Get Charts Data By:</h5>

    <div class="flex justify-content-start align-items-center gap-4">
        <InputGroup>
            <!--            {{store.chart_date_filter}}-->
            <SelectButton v-model="store.chart_date_filter"
                          optionLabel="name"
                          optionValue="value"
                          :options="store.chart_by_date_filter"
                          size="small"
                          data-testid="general-charts_filters"
                          aria-labelledby="single"
            />


            <Button   size="small"
                    icon="pi pi-copy"
                    data-testid="general-charts_filters_copy"
                    @click="store.getCopy('is_sidebar_collapsed')"
            />
        </InputGroup>

        <div class=" flex gap-2 ml-2">
            <DatePicker
                size="small"
                placeholder="Select Start Date"
                date-format="yy-mm-dd"
                @date-select="handleDateChangeRound($event,'filter_start_date')"
                :maxDate="today"
                :disabled="store.chart_date_filter !== 'custom'"
                v-model="store.filter_start_date"
                showIcon/>
            <DatePicker
                placeholder="Select End Date"
                date-format="yy-mm-dd"
                :maxDate="today"
                size="small"
                @date-select="handleDateChangeRound($event,'filter_end_date')"

                :disabled="store.chart_date_filter !== 'custom'"
                v-model="store.filter_end_date"
                showIcon/>

        </div>

    </div>
    <Button label="Submit"
            class="ml-auto mt-2"
            size="small"
            @click="store.storeChartFilterSettings()"
            :disabled="store.is_button_disabled"/>

</template>

<script setup>
import {useSettingStore} from '../../../../stores/store-settings'
import {useRoute} from "vue-router";
import {onMounted, ref, watch} from "vue";

const store = useSettingStore();

const route = useRoute();

const quick_filter_menu_state = ref();
const toggleQuickFilterState = (event) => {
    quick_filter_menu_state.value.toggle(event);
};

const handleDateChangeRound = (newDate, date_type) => {
    if (newDate && date_type) {
        store[date_type] = new Date(newDate.getTime() - newDate.getTimezoneOffset() * 60000);
    }
}
const today = ref(new Date());


</script>

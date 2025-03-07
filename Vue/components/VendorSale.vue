<template>
    <!-- Vendor Grid -->
    <div
        v-for="(item, index) in processedData"
        :key="item.id"
        class="!grid place-items-center relative border-round-xl size-full bg-white"
    >
        <!-- Logo/Image -->
        <div class="mb-3 p-2 w-full">
            <!-- Case 1: When we have image_urls array -->
            <div v-if="Array.isArray(item.image_urls) && item.image_urls.length > 0">
                <div v-for="(imageUrl, imgIndex) in item.image_urls" :key="imgIndex">
                    <Image preview :src="imageUrl" alt="Image" class="shadow-4 mx-auto" width="60" />
                </div>
            </div>
            <!-- Case 2: When we have a logo property -->
            <img v-else-if="item.logo" :src="item.logo" :alt="item.name" class="mx-auto" width="60" />
            <!-- Case 3: Fallback to default icon -->
            <i v-else class="pi pi-building text-4xl text-primary mx-auto block"></i>
        </div>

        <div class="p-1 rounded-md bg-gray-100 text-center absolute -bottom-6 w-[75%] left-1/2 -translate-x-1/2 shadow-2">
            <!-- Ranking -->
            <div class="font-bold text-[10px] leading-[14px]">
                {{ getRankText(index) }}
            </div>

            <!-- Name -->
            <div class="text-center text-[8px] text-700 leading-[12px] line-clamp-1 font-bold text-gray-400">
                {{ item.name }}
            </div>
        </div>
    </div>

    <!-- Empty state -->
    <div v-if="!processedData || processedData.length === 0" class="col-12 text-center py-5">
        <i class="pi pi-info-circle text-xl mb-3 text-blue-300"></i>
        <p>No items found.</p>
    </div>
</template>

<!--<script setup>
import { computed, onMounted, ref } from "vue";
import { useRootStore } from "@/stores/root";

const root = useRootStore();

// Props
const props = defineProps({
    data: {
        type: Array,
        default: () => [],
    },
    baseUrl: {
        type: String,
        default: "",
    },
    type: {
        type: String,
        default: "vendor", // Accepts "vendor" or "category"
    },
});

const sampleLogos = ref([]);

onMounted(async () => {
    await root.getAssets();
    console.log("Assets:", root.assets);

    // Set sample images based on type
    sampleLogos.value = props.type === "vendor" ? root.assets?.vendor_images : root.assets?.category_images;
});

// Process items dynamically based on type
const processedData = computed(() => {
    return (props.data || []).map((item, index) => ({
        ...item,
        logo: item.logo || (!Array.isArray(item.image_urls) || item.image_urls.length === 0)
            ? sampleLogos.value[index % sampleLogos.value.length]
            : item.logo,
    }));
});

// Helper function to get rank text
const getRankText = (index) => {
    const ranks = ["1st", "2nd", "3rd", "4th"];
    return ranks[index] || `${index + 1}th`;
};
</script>-->
<script setup>
import { computed, ref } from "vue";

// Props
const props = defineProps({
    data: {
        type: Array,
        default: () => [],
    },
    sampleLogos: {
        type: Array,
        default: () => [],
    },
    type: {
        type: String,
        default: "vendor", // Can be "vendor" or "category"
    },
});

// Process items dynamically based on type and sample images
const processedData = computed(() => {
    return (props.data || []).map((item, index) => ({
        ...item,
        logo: item.logo || (Array.isArray(item.image_urls) && item.image_urls.length > 0)
            ? item.logo
            : props.sampleLogos[index % props.sampleLogos.length], // Use fallback logo
    }));
});

// Helper function to get rank text
const getRankText = (index) => {
    const ranks = ["1st", "2nd", "3rd", "4th"];
    return ranks[index] || `${index + 1}th`;
};
</script>

<style scoped>
/* Add any additional custom styles here */
</style>

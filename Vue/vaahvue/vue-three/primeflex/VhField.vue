<script setup>
const props = defineProps(['label'])

</script>
<template>
    <div class="field grid">
        <label class="col-12 mb-3 md:col-3 md:mb-0">
            {{props.label}} <slot name="label"></slot>
        </label>
        <div class="col-12 md:col-9">
            <slot></slot>
        </div>
    </div>
</template>

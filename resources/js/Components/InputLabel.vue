<script setup>
defineProps({
    value: {
        type: String,
    },
    required: {
        type: Boolean,
        default: false
    },
    hint: {
        type: String,
    }
});
</script>

<template>
    <label class="block text-sm font-medium text-gray-700">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
        <span v-if="hint" class=" ml-2 text-xs text-gray-500">{{ hint }}</span>
        <span v-if="required" class="text-red-500"> *</span>
    </label>
</template>
